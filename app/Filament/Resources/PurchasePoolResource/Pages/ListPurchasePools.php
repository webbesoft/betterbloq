<?php

namespace App\Filament\Resources\PurchasePoolResource\Pages;

use App\Filament\Resources\PurchasePoolResource;
use App\Models\Product;
use App\Models\PurchasePool;
use App\Models\PurchasePoolTemplate;
use App\Models\Vendor;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListPurchasePools extends ListRecords
{
    protected static string $resource = PurchasePoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            // --- Add Custom Action Here ---
            Actions\Action::make('createFromTemplate')
                ->label('Create Pool from Template')
                ->icon('heroicon-o-document-duplicate')
                ->form([
                    Select::make('template_id')
                        ->label('Select Template')
                        ->options(PurchasePoolTemplate::pluck('name', 'id'))
                        ->required()
                        ->searchable()
                        ->live(),
                    DatePicker::make('start_date')
                        ->label('Pool Start Date')
                        ->required(),
                    DatePicker::make('end_date')
                        ->label('Pool End Date')
                        ->required(),
                ])
                ->action(function (array $data) {
                    try {
                        // Find the template with its tiers
                        $template = PurchasePoolTemplate::with('tiers')->findOrFail($data['template_id']);

                        $vendor = Vendor::find($template->vendor_id);
                        $product = Product::find($template->product_id);

                        if (! $vendor || ! $product) {
                            Notification::make()
                                ->title('Error creating Purchase Pool')
                                ->body('Selected Vendor or Product not found.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $endDate = Carbon::parse($data['end_date']);
                        $prepTime = $vendor->prep_time ?? 0;
                        $deliveryTime = $product->delivery_time ?? 0;
                        $targetDeliveryDate = $endDate->addDays($prepTime + $deliveryTime);

                        // Prepare data for the new PurchasePool
                        $poolData = [
                            'start_date' => $data['start_date'],
                            'end_date' => $data['end_date'],
                            // will be calcuated from vendor.prep_time + product_delivery_time
                            'target_delivery_date' => $targetDeliveryDate->toDateString(),
                            'min_orders_for_discount' => $template->min_orders_for_discount,
                            'max_orders' => $template->max_orders,
                            'status' => 'pending',
                            'target_volume' => $template->target_volume,
                            'vendor_id' => $template->vendor_id,
                            'product_id' => $template->product_id,
                            'current_volume' => 0,
                        ];

                        DB::transaction(function () use ($template, $poolData) {

                            $newPool = PurchasePool::create($poolData);

                            // Create the Tiers for the new pool based on template tiers
                            foreach ($template->tiers as $templateTier) {
                                $newPool->purchasePoolTiers()->create([ // Assumes PurchasePool has a tiers() hasMany relationship
                                    'name' => $templateTier->name,
                                    'description' => $templateTier->description,
                                    'discount_percentage' => $templateTier->discount_percentage,
                                    'min_volume' => $templateTier->min_volume,
                                    'max_volume' => $templateTier->max_volume,
                                ]);
                            }
                        });

                        Notification::make()
                            ->title('Purchase Pool created successfully from template')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error creating Purchase Pool')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            // --- End Custom Action ---

        ];
    }
}
