<?php

namespace App\Filament\Resources\PurchasePoolResource\Pages;

use App\Filament\Resources\PurchasePoolResource;
use App\Models\Product;
use App\Models\Vendor;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurchasePool extends EditRecord
{
    protected static string $resource = PurchasePoolResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {

        if (isset($data['product_id'])) {
            $product = Product::find($data['product_id']);
            if ($product) {
                $data['vendor_id'] = $product->vendor_id;
            }
        }
        
        if (isset($data['end_date'], $data['vendor_id'], $data['product_id'])) {
            $vendor = Vendor::find($data['vendor_id']);
            $product = Product::find($data['product_id']);

            if ($vendor && $product) {
                $endDate = Carbon::parse($data['end_date']);
                $prepTime = $vendor->prep_time ?? 0;
                $deliveryTime = $product->delivery_time ?? 0;
                $targetDeliveryDate = $endDate->addDays($prepTime + $deliveryTime);

                $data['target_delivery_date'] = $targetDeliveryDate->toDateString();
            } else {  $data['target_delivery_date'] = null;
            }
        } else {
            $data['target_delivery_date'] = null; 
        }

        if (! isset($data['current_volume'])) {
            $data['current_volume'] = 0;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
