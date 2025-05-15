<?php

namespace App\Listeners;

use App\Mail\NewOrderConfirmation;
use App\Models\Log;
use App\Models\Order;
use App\Models\OrderLineItem;
use App\Models\Product;
use App\Models\PurchasePool;
use App\Models\StorageOrder;
use App\Models\User;
use App\Services\LogService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Events\WebhookReceived;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class StripeEventListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @throws ApiErrorException
     */
    public function handle(WebhookReceived $event): void
    {
        //
        if ($event->payload['type'] === 'checkout.session.completed') {
            $session = $event->payload['data']['object'];
            $metadata = $session['metadata'];

            $stripeCustomerId = data_get($session, 'customer');

            $user = User::whereStripeCustomerId($stripeCustomerId)->first();

            $isSubscription = data_get($session, 'mode') === 'subscription';

            if ($isSubscription) {
                return;
            }

            if ($user) {
                try {
                    $stripe = new StripeClient(config('services.stripe.secret'));
                    $lineItemsFromStripe = $stripe->checkout->sessions->allLineItems(data_get($session, 'id'), ['limit' => 100]);

                    if (empty($lineItemsFromStripe->data)) {
                        Log::error('Webhook: No line items found in completed Stripe session.', ['session_id' => $session['id']]);

                        return;
                    }

                    $mainOrder = Order::create([
                        'user_id' => $user->id,
                        'email' => $session['customer_details']['email'] ?? $user->email,
                        'phone' => $session['customer_details']['phone'] ?? null,
                        'address' => json_encode($session['customer_details']['address'] ?? null),
                        'status' => 'pending_fulfillment', // Or your default status
                        'stripe_session_id' => $session['id'],
                        'payment_intent_id' => $session['payment_intent'],
                        'total_amount' => $session['amount_total'] / 100, // amount_total is in cents
                        'expected_delivery_date' => Carbon::parse($metadata['expected_delivery_date'])->toDateString(),
                        'vendor_id' => $metadata['vendor_id'],
                    ]);

                    $firstVendorId = null;

                    foreach ($lineItemsFromStripe->data as $lineItem) {
                        $stripePriceId = $lineItem->price->id;
                        $product = Product::where('stripe_price_id', $stripePriceId)->first();

                        if ($product) {
                            if (! $firstVendorId) {
                                $firstVendorId = $product->vendor_id;
                            }

                            OrderLineItem::create([
                                'order_id' => $mainOrder->id,
                                'product_id' => $product->id,
                                'quantity' => $lineItem->quantity,
                                'price_per_unit' => ($lineItem->price->unit_amount / 100),
                                'total_price' => ($lineItem->amount_total / 100),
                                'purchase_pool_id' => $lineItemsFromStripe['metadata']['purchase_pool_id'],
                            ]);

                            // Update Purchase Pool Volume for this specific product
                            // This requires knowing which pool this product order was associated with.
                            // If not directly available, you might need to find the active pool for the product again.
                            $activePool = PurchasePool::where('purchase_pool_id', $metadata['purchase_pool_id'])
                                ->first();
                            if ($activePool) {
                                $activePool->increment('current_volume', $lineItem->quantity);
                                // $orderLineItem->update(['purchase_pool_id' => $activePool->id]);
                            }
                        }
                    }

                    $storageOrderId = $metadata['storage_order_id'] ?? null;
                    $storageNeededFlag = filter_var($metadata['storage_needed_flag'] ?? false, FILTER_VALIDATE_BOOLEAN);

                    if ($storageOrderId && $storageNeededFlag) {
                        $storageOrder = StorageOrder::find($storageOrderId);
                        if ($storageOrder) {
                            $storageOrder->update([
                                'order_id' => $mainOrder->id,
                                'status' => 'pending_arrival',
                                'payment_intent_id' => $session['payment_intent'],
                            ]);
                            Log::info('StorageOrder linked and updated successfully.', ['storage_order_id' => $storageOrderId, 'main_order_id' => $mainOrder->id]);

                            // TODO: Notify admin: New Storage Order Requires Setup for items in Order #{$mainOrder->id}
                        } else {
                            Log::warning('StorageOrder ID found in Stripe metadata, but record not found in DB.', [
                                'storage_order_id' => $storageOrderId, 'session_id' => $session['id'],
                            ]);
                        }
                    } elseif ($storageNeededFlag && ! $storageOrderId) {
                        Log::error('Storage was flagged as needed in metadata, but no storage_order_id was provided.', [
                            'session_id' => $session['id'], 'metadata' => $metadata,
                        ]);
                    }

                    Mail::to($mainOrder->user->email)->send(new NewOrderConfirmation($mainOrder, $user));
                } catch (ApiErrorException $e) {
                    (new LogService)->logException($e, __CLASS__, __METHOD__, [
                        $event->payload,
                    ]);
                }
            }
        }
    }
}
