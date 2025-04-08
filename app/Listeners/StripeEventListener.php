<?php

namespace App\Listeners;

use App\Mail\NewOrderConfirmation;
use App\Models\Order;
use App\Models\Product;
use App\Models\PurchasePool;
use App\Models\User;
use App\Services\LogService;
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
            $session = $event->payload;

            $stripeCustomerId = data_get($session, 'data.object.customer');

            $user = User::whereStripeCustomerId($stripeCustomerId)->first();

            if ($user) {
                try {
                    $stripe = new StripeClient(config('services.stripe.secret'));
                    $lineItems = $stripe->checkout->sessions->allLineItems(data_get($session, 'data.object.id'), ['limit' => 1]);

                    if (! empty($lineItems->data)) {
                        $lineItem = $lineItems->data[0];
                        $stripePriceId = $lineItem->price->id;
                        $quantity = $lineItem->quantity;
                        $price = $lineItem->price;

                        $product = Product::where('stripe_price_id', $stripePriceId)->first();

                        if ($product) {
                            $expectedDeliveryDate = data_get($session, 'data.object.metadata.expected_delivery_date');

                            if ($expectedDeliveryDate) {
                                $openPool = PurchasePool::where('product_id', $product->id)
                                    ->where('status', 'active')
                                    ->where('end_date', '>', $expectedDeliveryDate)
                                    ->where('start_date', '<', $expectedDeliveryDate)
                                    ->first();

                                if ($openPool) {
                                    $order = Order::create([
                                        'user_id' => $user->id,
                                        'email' => data_get($session, 'data.object.customer_details.email') ?? 'tawanda@betterbloq.com',
                                        'phone' => data_get($session, 'data.object.customer_details.phone') ?? '+1 (456) 7890',
                                        'address' => json_encode(data_get($session, 'data.object.customer_details.address')),
                                        'status' => 'completed',
                                        'purchase_pool_id' => $openPool->id,
                                        'product_id' => $product->id,
                                        'quantity' => $quantity,
                                        'stripe_session_id' => data_get($session, 'data.object.id'),
                                        'vendor_id' => $product->vendor_id,
                                        'total_amount' => ($price->unit_amount / 100) * $quantity,
                                    ]);

                                    // #! Stripe provides the unit amount in cents
                                    $openPool->update([
                                        'current_amount' => $openPool->current_amount + (($price->unit_amount / 100) * $quantity),
                                    ]);

                                    Mail::to($order->user->email)->send(new NewOrderConfirmation($order, $user));
                                } else {
                                    info('No active purchase pool found for product after successful payment', [
                                        'product_id' => $product->id,
                                        'expected_delivery_date' => $expectedDeliveryDate,
                                        'stripe_session_id' => data_get($session, 'data.object.id'),
                                    ]);
                                }
                            } else {
                                // Handle the case where expected_delivery_date is not found in metadata
                                info('Expected delivery date not found in Stripe session metadata', [
                                    'stripe_session_id' => data_get($session, 'data.object.id'),
                                ]);
                            }
                        }
                    }
                } catch (ApiErrorException $e) {
                    (new LogService)->logException($e, __CLASS__, __METHOD__, [
                        $event->payload,
                    ]);
                }

            }
        }
    }
}
