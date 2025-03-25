<?php

namespace App\Apps\BulkBuy\Managers;

use App\Apps\BulkBuy\Models\Product;
use App\Apps\BulkBuy\Models\PurchasePool;

class PurchasePoolManager
{
    // #! Not sure if we want to dynamically create purchase pools - yet - given the number of variables and parameters and things to consider
    public static function createPurchasePool(string $product_id, string $expected_date): PurchasePool
    {
        $product = Product::where('id', $product_id)->with['vendor']->first();

        return PurchasePool::create([
            'product_id' => $product_id,
            'vendor_id' => $product->vendor->id,
            'target_delivery_date' => $expected_date,
            'end_date',
        ]);
    }
}
