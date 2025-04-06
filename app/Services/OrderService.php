<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PurchasePool;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class OrderService
{
    public static function createOrder(User $user, array $data)
    {
        $open_pool = PurchasePool::where('product_id', data_get($data, 'product_id'))->where('status', 'active')->where('end_date', '>', data_get($data, 'expected_delivery_date'))->where('start_date', '<', data_get($data, 'expected_delivery_date'))->first();

        if (! $open_pool) {
            return [
                'error' => 'No open purchase pools found',
            ];
        }

        $product = Product::whereId(data_get($data, 'product_id'))->first();

        if (! $product) {
            return [
                'error' => 'This product does not exist',
            ];
        }

        if ($user && $open_pool && $product) {
            try {
                $session = $user->checkout([$product->stripe_price_id => data_get($data, 'quantity')], [
                    'success_url' => route('orders.index'),
                    'cancel_url' => route('product.show', ['product' => $product]),
                    'metadata' => [
                        'product_id' => data_get($data, 'product_id'), // Optional, but can be helpful
                        'expected_delivery_date' => data_get($data, 'expected_delivery_date'),
                        'user_id' => $user->id, // Optional
                    ],
                ]);

                return [
                    'success' => true,
                    'url' => $session->url,
                ];
            } catch (\Exception $e) {
                (new LogService)->logException($e, __CLASS__, __METHOD__, [
                    'metadata' => [
                        'product_id' => data_get($data, 'product_id'),
                        'expected_delivery_date' => data_get($data, 'expected_delivery_date'),
                        'user_id' => $user->id,
                    ],
                ]);
                Session::flash('message', 'Could not initiate checkout.');

                return back();
            }
        } else {
            Log::info('missing product, user, or purchase pool');
        }

        $order = [];

        return $order;
    }
}
