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
        // Check if necessary data is present
        if (isset($data['end_date'], $data['vendor_id'], $data['product_id'])) {
            $vendor = Vendor::find($data['vendor_id']);
            $product = Product::find($data['product_id']);

            if ($vendor && $product) {
                // --- Calculate Target Delivery Date ---
                $endDate = Carbon::parse($data['end_date']);
                $prepTime = $vendor->prep_time ?? 0;
                $deliveryTime = $product->delivery_time ?? 0;
                $targetDeliveryDate = $endDate->addDays($prepTime + $deliveryTime);

                $data['target_delivery_date'] = $targetDeliveryDate->toDateString();
                // --- End Calculation ---
            } else {
                // Handle case where vendor/product might not be found yet
                // or if IDs are somehow invalid during submission.
                // Setting to null might be an option, or rely on validation.
                $data['target_delivery_date'] = null;
            }
        } else {
            $data['target_delivery_date'] = null; // Ensure it's null if calculation can't happen
        }

        // Ensure current_volume defaults correctly if not provided/disabled
        if (! isset($data['current_volume'])) {
            $data['current_volume'] = 0;
        }

        return $data;
    }
}
