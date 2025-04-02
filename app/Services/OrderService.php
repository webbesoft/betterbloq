<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PurchasePool;
use App\Models\User;

class OrderService
{
    public static function createOrder(User $user, array $data)
    {
        $open_pool = PurchasePool::where('product_id', data_get($data, 'product_id'))->where('status', 'open')->where('end_date', '>', data_get($data, 'expected_delivery_date'))->where('start_date', '<', data_get($data, 'expected_delivery_date'))->first();

        if (! $open_pool) {
            return [
                'error' => [
                    'message' => 'No open purchase pools found',
                ],
            ];
        }

        $order = Order::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'phone' => '+1 (456) 78901234',
            'address' => '1 Main St, Anytown, USA',
            'status' => 'pending',
            'purchase_pool_id' => $open_pool->id,
            'product_id' => data_get($data, 'product_id'),
            'expected_delivery_date' => data_get($data, 'expected_delivery_date'),
            'quantity' => data_get($data, 'quantity'),
        ]);

        return $order;
    }
}
