<?php

use App\Models\Product;
use App\Models\PurchasePool;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Support\Collection;

class BulkCreatePurchasePoolsAction extends BulkAction
{
    public static function getDefaultName(): ?string
    {
        return 'bulkCreatePurchasePools';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Create Purchase Pools')
            ->icon('heroicon-o-shopping-bag')
            ->form([
                DatePicker::make('start_date')
                    ->label('Pool Start Date')
                    ->required()
                    ->default(now())
                    ->native(false)
                    ->displayFormat('Y-m-d'),
                DatePicker::make('end_date')
                    ->label('Pool End Date')
                    ->required()
                    ->native(false)
                    ->displayFormat('Y-m-d')
                    ->minDate(fn (Get $get) => $get('start_date') ? Carbon::parse($get('start_date'))->addDay() : now()->addDay())
                    ->helperText('Must be after the start date.'),
            ])
            ->action(function (Collection $records, array $data) {
                $this->processBulkCreation($records, $data);
            });

        $this->deselectRecordsAfterCompletion();
    }

    protected function processBulkCreation(Collection $records, array $data)
    {
        $createdCount = 0;
        $skippedMissingVendorCount = 0;
        $skippedCalculationFailureCount = 0;
        $skippedProductNames = [];

        foreach ($records as $product) {
            $product->loadMissing(['vendor']);

            if (! $product instanceof Product) {
                continue;
            }

            if (empty($product->vendor_id) || ! $product->vendor) {
                $skippedMissingVendorCount++;
                $skippedProductNames[] = ($product->name ?? "ID: {$product->id}").' (missing vendor info)';

                continue;
            }

            $targetDeliveryDate = PurchasePool::calculateTargetDeliveryDate(
                $data['end_date'],
                $product
            );

            if (! $targetDeliveryDate) {
                $skippedCalculationFailureCount++;
                $skippedProductNames[] = ($product->name ?? "ID: {$product->id}").' (delivery date calculation failed)';

                continue;
            }

            $poolName = 'Pool - '.($product->name ?? 'Unnamed Product').
                        ' - '.Carbon::parse($data['start_date'])->format('M d').
                        ' to '.Carbon::parse($data['end_date'])->format('M d, Y');

            PurchasePool::create([
                'name' => $poolName,
                'product_id' => $product->id,
                'vendor_id' => $product->vendor_id,
                'start_date' => Carbon::parse($data['start_date'])->toDateString(),
                'end_date' => Carbon::parse($data['end_date'])->toDateString(),
                'target_delivery_date' => $targetDeliveryDate->toDateString(),
                'min_orders_for_discount' => 0,
                'max_orders' => 0,
                'target_volume' => 0,
                'current_volume' => 0,
                'status' => 'pending',
            ]);
            $createdCount++;
        }

        if ($createdCount > 0) {
            Notification::make()
                ->success()
                ->title('Purchase Pools Creation Complete')
                ->body(
                    "Successfully created {$createdCount} purchase pool(s). ".
                    "Target delivery dates were automatically calculated. Status set to 'pending'. ".
                    'Please review each pool for settings like target volume and order limits before activating.'
                )
                ->persistent()
                ->send();
        }

        $errorMessages = [];
        if ($skippedMissingVendorCount > 0) {
            $errorMessages[] = "Skipped {$skippedMissingVendorCount} product(s) due to missing vendor information.";
        }
        if ($skippedCalculationFailureCount > 0) {
            $errorMessages[] = "Skipped {$skippedCalculationFailureCount} product(s) because target delivery date calculation failed.";
        }

        if (! empty($errorMessages)) {
            $body = implode(" \n", $errorMessages);
            if (count($skippedProductNames) > 0 && count($skippedProductNames) <= 5) { // List a few names if not too many
                $body .= "\nDetails: ".implode(', ', array_slice($skippedProductNames, 0, 5));
                if (count($skippedProductNames) > 5) {
                    $body .= ', and more.';
                }
            }
            Notification::make()
                ->warning()
                ->title('Issues During Pool Creation')
                ->body($body)
                ->persistent()
                ->send();
        }

        if ($createdCount === 0 && $skippedMissingVendorCount === 0 && $skippedCalculationFailureCount === 0 && $records->isNotEmpty()) {
            Notification::make()
                ->info()
                ->title('No Purchase Pools Created')
                ->body('No products were eligible for purchase pool creation from the current selection.')
                ->send();
        }
    }
}
