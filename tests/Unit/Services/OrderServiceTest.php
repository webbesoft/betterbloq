<?php

namespace Tests\Unit\Services;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\PurchaseCycle;
use App\Models\PurchasePool;
use App\Models\User;
use App\Models\Vendor;
use App\Payments\StripeGateway;
use App\Services\OrderService;
use Illuminate\Validation\ValidationException;

it('rejects an empty checkout without creating orders or contacting Stripe', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_test']);
    $this->mock(StripeGateway::class)->shouldNotReceive('createSession');

    expect(fn () => OrderService::createOrder($user, ['items' => []]))->toThrow(ValidationException::class);
    expect(Order::count())->toBe(0);
});

it('requires a configured active purchase pool rather than creating one during checkout', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_test']);
    $vendor = Vendor::create(['user_id' => $user->id, 'name' => 'Vendor', 'phone' => '123']);
    $category = Category::create(['name' => 'Materials']);
    $product = Product::withoutEvents(fn () => Product::create([
        'vendor_id' => $vendor->id, 'category_id' => $category->id, 'name' => 'Bricks',
        'description' => 'Bricks', 'price' => 100, 'storage_conditions_required' => [],
    ]));
    $cycle = PurchaseCycle::create([
        'name' => 'Active cycle', 'start_date' => today()->subDay(), 'end_date' => today()->addDay(), 'status' => 'active',
    ]);
    $this->mock(StripeGateway::class)->shouldNotReceive('createSession');
    $data = ['items' => [[
        'product_id' => $product->id, 'quantity' => 10, 'purchase_cycle_id' => $cycle->id,
        'expected_delivery_date' => today()->addDay()->toDateString(),
    ]]];

    expect(fn () => OrderService::createOrder($user, $data))->toThrow(ValidationException::class);
    expect(PurchasePool::count())->toBe(0)->and(Order::count())->toBe(0);
});
