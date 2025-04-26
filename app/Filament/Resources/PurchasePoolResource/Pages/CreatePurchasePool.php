<?php

namespace App\Filament\Resources\PurchasePoolResource\Pages;

use App\Filament\Resources\PurchasePoolResource;
use App\Models\Product;
use App\Models\Vendor;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchasePool extends CreateRecord
{
    protected static string $resource = PurchasePoolResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['product_id'])) {
            $product = Product::find($data['product_id']);
            if ($product) {
                $data['vendor_id'] = $product->vendor_id;
            } else {
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
            } else { $data['target_delivery_date'] = null;
            }
        } else {
            $data['target_delivery_date'] = null; 
        }

        if (! isset($data['current_volume'])) {
            $data['current_volume'] = 0;
        }

        return $data;
    }
}
