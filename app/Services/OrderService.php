<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PurchasePool;
use App\Models\StorageOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class OrderService
{
    public static function createOrder(User $user, array $data)
    {
        $productId = data_get($data, 'product_id');
        $userExpectedDeliveryDateString = data_get($data, 'expected_delivery_date');
        $quantity = data_get($data, 'quantity');

        $product = Product::find($productId);
        if (! $product) {
            Session::flash('message', ['error' => 'This product does not exist.']);

            return ['error' => 'This product does not exist.'];
        }

        $open_pool = PurchasePool::where('product_id', data_get($data, 'product_id'))
            ->where('status', 'active')
            ->first();

        if (! $open_pool) {
            return [
                'error' => 'Sorry, your delivery date must be within 3 days of the purchase pool delivery date. Please try again or request a new purchase pool for a different date.',
            ];
        }

        $storageNeeded = false;
        $storageOrderId = null;
        $poolTargetDeliveryDate = Carbon::parse($open_pool->target_delivery_date);

        if ($userExpectedDeliveryDateString) {
            $userExpectedDeliveryDate = Carbon::parse($userExpectedDeliveryDateString);

            if ($userExpectedDeliveryDate->isAfter($poolTargetDeliveryDate->copy()->addDays(3))) {
                $storageNeeded = true;
            }
        } else {
            Session::flash('message', ['error' => 'Please provide your preferred delivery date.']);

            return ['redirect_back' => true];
        }

        if ($storageNeeded) {
            // Find a default warehouse or implement logic to select one
            $defaultWarehouse = Warehouse::where('is_active', true)->orderBy('id')->first();
            if (! $defaultWarehouse) {
                Log::error('No active default warehouse found for creating storage order.', ['product_id' => $productId]);
                Session::flash('message', ['error' => 'Storage configuration error. Please contact support.']);

                return ['redirect_back' => true];
            }

            try {
                $storageOrder = StorageOrder::create([
                    'user_id' => $user->id,
                    'order_id' => null,
                    'warehouse_id' => $defaultWarehouse->id,
                    'requested_storage_start_date' => $poolTargetDeliveryDate->copy()->addDay(),
                    'requested_storage_duration_estimate' => $userExpectedDeliveryDate->diffForHumans($poolTargetDeliveryDate->copy()->addDay(), true).' (approx)',
                    'preliminary_storage_cost_estimate' => 0.00,
                    'status' => 'pending_payment_confirmation',
                    'notes' => 'Storage automatically initiated due to extended delivery date request.', 'manually_entered_total_space_units' => null,
                    'calculated_space_unit_type' => null,
                    'applied_storage_tier_id' => null,
                ]);
                $storageOrderId = $storageOrder->id;
                Log::info('Preliminary StorageOrder created.', ['id' => $storageOrderId, 'user_id' => $user->id]);
            } catch (\Exception $e) {
                Log::error('Failed to create preliminary StorageOrder', [
                    'user_id' => $user->id,
                    'product_id' => $productId,
                    'error' => $e->getMessage(),
                ]);
                Session::flash('message', ['error' => 'Could not set up storage details. Please try again or contact support.']);

                return ['redirect_back' => true];
            }
        }

        if ($user && $open_pool && $product) {
            try {
                if (empty($product->stripe_price_id)) {
                    Log::error('Product missing stripe_price_id', ['product_id' => $product->id]);

                    return ['error' => 'Product configuration error. Cannot proceed with checkout.'];
                }

                $checkout_session_payload = [
                    'success_url' => route('orders.index').'?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('product.show', ['product' => $product]),
                    'metadata' => [
                        'product_id' => $product->id,
                        'purchase_pool_id' => $open_pool->id,
                        'quantity' => data_get($data, 'quantity'),
                        'expected_delivery_date' => data_get($data, 'expected_delivery_date'),
                        'user_id' => $user->id,
                        'vendor_id' => $product->vendor->id,
                        'storage_order_id' => $storageOrderId,
                        'storage_needed_flag' => $storageNeeded,
                    ],
                    'payment_intent_data' => [
                        'capture_method' => 'manual',
                        'description' => "Order for {$product->name} - Pool: {$open_pool->name}",
                        'metadata' => [
                            'order_type' => 'purchase_pool_join',
                            'product_id' => $product->id,
                            'user_id' => $user->id,
                            'storage_order_id' => $storageOrderId,
                            'vendor_id' => $product->vendor->id,
                        ],
                    ],
                    'customer_email' => $user->email,
                    'mode' => 'payment',
                ];

                if ($user->stripe_id) {
                    $checkout_session_payload['customer'] = $user->stripe_id;
                }
                $line_items = [
                    [
                        'price' => $product->stripe_price_id,
                        'quantity' => $quantity,
                    ],
                ];

                $session = $user->checkout(
                    $line_items,
                    $checkout_session_payload
                );

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
            (new LogService)->createLog('error', 'Missing product, user, or purchase pool for order creation.', __CLASS__, __METHOD__, [
                'metadata' => [
                    'product_id' => data_get($data, 'product_id'),
                    'expected_delivery_date' => data_get($data, 'expected_delivery_date'),
                    'product_id' => data_get($data, 'product_id'),
                    'user_id' => $user->id ?? null,
                    'open_pool_exists' => ! is_null($open_pool),
                ],
            ]);
        }

        return ['error' => 'Could not process order. Required information missing.'];
    }
}
