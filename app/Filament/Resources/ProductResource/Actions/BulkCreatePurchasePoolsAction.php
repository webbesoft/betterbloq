<?php

namespace App\Filament\Resources\ProductResource\Actions;

use App\Models\Product;
use App\Models\PurchaseCycle;
use App\Models\PurchasePool;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
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
                Select::make('purchase_cycle_id')
                    ->label('Purchase Cycle')
                    ->options(
                        PurchaseCycle::whereIn('status', ['upcoming', 'active'])
                            ->get()
                            ->pluck('name', 'id')
                            ->all()
                    )
                    ->searchable(),
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
        $purchaseCycle = PurchaseCycle::whereId(data_get($data, 'purchase_cycle_id'))->first();

        if (! $purchaseCycle) {
            throw new Exception('Purchase cycle is required');
        }

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
                $purchaseCycle->end_date,
                $product
            );

            if (! $targetDeliveryDate) {
                $skippedCalculationFailureCount++;
                $skippedProductNames[] = ($product->name ?? "ID: {$product->id}").' (delivery date calculation failed)';

                continue;
            }

            $poolName = 'Pool - '.($product->name ?? 'Unnamed Product').
                        ' - '.Carbon::parse($purchaseCycle->start_date)->format('M d').
                        ' to '.Carbon::parse($purchaseCycle->end_date)->format('M d, Y');

            PurchasePool::create([
                'name' => $poolName,
                'product_id' => $product->id,
                'vendor_id' => $product->vendor_id,
                'purchase_cycle_id' => $purchaseCycle->id,
                'target_delivery_date' => $targetDeliveryDate->toDateString(),
                'min_orders_for_discount' => 0,
                'max_orders' => 0,
                'target_volume' => 0,
                'current_volume' => 0,
                'cycle_status' => PurchasePool::STATUS_ACCUMULATING,
            ]);
            $createdCount++;
        }

        if ($createdCount > 0) {
            Notification::make()
                ->success()
                ->title('Purchase Pools Creation Complete')
                ->body(
                    "Successfully created {$createdCount} purchase pool(s). ".
                    "Target delivery dates were automatically calculated. Status set to 'accumulating'. ".
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
