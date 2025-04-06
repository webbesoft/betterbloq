<?php

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Models\User;
use App\Models\PurchasePool;
use App\Models\Vendor;
use App\Services\OrderService;

it('should create a new order and a new purchase pool when none exists', function () {
    $vendorUser = User::factory()->create();
    $vendor = Vendor::factory()->create([
        'user_id' => $vendorUser->id,
    ]);

    $account = User::factory()->create();

    $product = Product::factory()->create([
        'vendor_id' => $vendor->id,
    ]);

    $orderData = [
        'product_id' => $product->id,
        'expected_delivery_date' => now()->addDay()->startOfDay()->toDateString(),
        'quantity' => 10,
    ];

    OrderService::createOrder($account, $orderData);

    $purchase_pool = PurchasePool::where('product_id', '=', $product->id);

    expect($purchase_pool)->toBeInstanceOf(PurchasePool::class);
});

it('should add a newly created order to an existing purchase pool where applicable', function () {})->skip();
