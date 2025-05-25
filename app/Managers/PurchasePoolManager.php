<?php

namespace App\Managers;

use App\Models\Product;
use App\Models\PurchaseCycle;
use App\Models\PurchasePool;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PurchasePoolManager extends Manager
{
    public function __construct(public PurchasePool $purchasePool) {}

    public function onCreated(Model $model): void {}

    public function onPurchasePoolUpdated() {}

    public function onSaving(mixed $data): mixed
    {
        $product = Product::whereId($data['product_id'] ?? null)->select(['vendor_id', 'delivery_time'])->first();
        if ($product) {
            $data['vendor_id'] = $product->vendor_id;
        } else {
            throw new \Exception('Product not found');
        }

        if (isset($data['vendor_id'], $data['product_id'])) {
            $vendor = Vendor::whereId($data['vendor_id'])->select('prep_time')->first();
            $cycle = PurchaseCycle::find($data['purchase_cycle_id']);

            if ($vendor && $product && $cycle) {
                $endDate = Carbon::parse($cycle->end_date);
                $prepTime = $vendor->prep_time ?? 0;
                $deliveryTime = $product->delivery_time ?? 0;
                $targetDeliveryDate = $endDate->addDays($prepTime + $deliveryTime);

                $data['target_delivery_date'] = $targetDeliveryDate->toDateString();
            } else {
                $data['target_delivery_date'] = null;
            }
        } else {
            $data['target_delivery_date'] = null;
        }

        if (! isset($data['current_volume'])) {
            $data['current_volume'] = 0;
        }

        return $data;
    }

    public function onPurchasePoolDeleted() {}
}
