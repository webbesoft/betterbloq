<?php

namespace App\Listeners;

use App\Enums\OrderStatusEnum;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as FacadesLog;
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

            info('session: ', ['session' => $session]);

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

                    $itemDetails = data_get($metadata, 'item_details');

                    DB::transaction(function () use ($session, $user, $metadata, $lineItemsFromStripe, $itemDetails) {
                        $mainOrder = Order::create([
                            'user_id' => $user->id,
                            'email' => data_get($session, 'customer_details.email') ?? $user->email,
                            'phone' => data_get($session, 'customer_details.phone') ?? '+1 (456) 7890',
                            'address' => json_encode(data_get($session, 'customer_details.address') ?? null),
                            'status' => OrderStatusEnum::PAYMENT_AUTHORIZED,
                            'stripe_session_id' => data_get($session, 'id'),
                            'payment_intent_id' => data_get($session, 'payment_intent'),
                            'initial_amount' => data_get($session, 'amount_total', 1) / 100,
                            'total_amount' => data_get($session, 'amount_total', 1) / 100,
                            // 'expected_delivery_date' => Carbon::parse($metadata['expected_delivery_date'])->toDateString(),
                            'vendor_id' => data_get($metadata, 'vendor_id') ?? data_get(json_decode(data_get($metadata, 'item_details')), 'vendor_id'),
                        ]);

                        $firstVendorId = null;

                        $decodedItemDetails = [];
                        if ($itemDetails) {
                            // Decode the JSON string into an associative PHP array
                            $decodedItemDetails = json_decode($itemDetails, true);

                            // Basic check if decoding was successful and it's an array
                            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decodedItemDetails)) {
                                info('Webhook Error: Failed to decode item_details metadata JSON.', [
                                    'json_error' => json_last_error_msg(),
                                    'itemDetailsJson' => $itemDetails,
                                    'objectId' => $eventObject->id ?? 'N/A',
                                ]);
                                $decodedItemDetails = [];
                            }
                        } else {
                            info('Webhook Warning: item_details metadata was missing or empty for event.', [
                                'objectId' => $eventObject->id ?? 'N/A',
                            ]);
                        }

                        $itemDetailsLookup = [];
                        if (! empty($decodedItemDetails)) {
                            foreach ($decodedItemDetails as $itemDetail) {
                                // Ensure 'product_id' exists in each entry before using it as a key
                                if (isset($itemDetail['product_id'])) {
                                    $itemDetailsLookup[$itemDetail['product_id']] = $itemDetail;
                                } else {
                                    // Log a warning or error if an item entry in metadata is malformed
                                    info("Webhook Warning: item_details metadata entry missing 'product_id'.", ['itemDetail' => $itemDetail]);
                                }
                            }
                        }

                        foreach ($lineItemsFromStripe->data as $lineItem) {
                            $stripePriceId = $lineItem->price->id;
                            $product = Product::where('stripe_price_id', $stripePriceId)->first();

                            if ($product) {
                                if (! $firstVendorId) {
                                    $firstVendorId = $product->vendor_id;
                                }

                                info('lineItem: ', ['lineItem' => $lineItem]);

                                $purchasePoolId = null;

                                if (isset($itemDetailsLookup[$product->id])) {
                                    $matchedItemDetail = $itemDetailsLookup[$product->id];

                                    $purchasePoolId = data_get($matchedItemDetail, 'purchase_pool_id');

                                    if (is_null($purchasePoolId)) {
                                        info("Webhook Warning: Found item detail entry for product ID {$product->id} but 'purchase_pool_id' key is missing or null.", ['matchedItemDetail' => $matchedItemDetail]);
                                    }

                                } else {
                                    // Log a warning if no matching item detail was found in metadata for this product ID
                                    info("Webhook Warning: No matching item detail found in metadata for product ID {$product->id} from Stripe line item.", ['lineItem' => $lineItem, 'productId' => $product->id]);
                                }

                                OrderLineItem::create([
                                    'order_id' => $mainOrder->id,
                                    'product_id' => $product->id,
                                    'quantity' => $lineItem->quantity,
                                    'price_per_unit' => ($lineItem->price->unit_amount / 100),
                                    'total_price' => ($lineItem->amount_total / 100),
                                    'purchase_pool_id' => $purchasePoolId,
                                ]);

                                $activePool = PurchasePool::whereId($purchasePoolId)->first();
                                if ($activePool) {
                                    $activePool->increment('current_volume', $lineItem->quantity);
                                    // $orderLineItem->update(['purchase_pool_id' => $activePool->id]);
                                }
                            }
                        }

                        $storageOrderId = data_get($metadata, 'storage_order_id');
                        $storageNeededFlag = filter_var(data_get($metadata, 'storage_needed_flag', false), FILTER_VALIDATE_BOOLEAN);

                        if ($storageOrderId && $storageNeededFlag) {
                            $storageOrder = StorageOrder::find($storageOrderId);
                            if ($storageOrder) {
                                $storageOrder->update([
                                    'order_id' => $mainOrder->id,
                                    'status' => 'pending_arrival',
                                    // 'payment_intent_id' => $session['payment_intent'],
                                ]);
                                FacadesLog::info('StorageOrder linked and updated successfully.', ['storage_order_id' => $storageOrderId, 'main_order_id' => $mainOrder->id]);

                                // TODO: Notify admin: New Storage Order Requires Setup for items in Order #{$mainOrder->id}
                            } else {
                                FacadesLog::warning('StorageOrder ID found in Stripe metadata, but record not found in DB.', [
                                    'storage_order_id' => $storageOrderId, 'session_id' => $session['id'],
                                ]);
                            }
                        } elseif ($storageNeededFlag && ! $storageOrderId) {
                            FacadesLog::error('Storage was flagged as needed in metadata, but no storage_order_id was provided.', [
                                'session_id' => $session['id'], 'metadata' => $metadata,
                            ]);
                        }

                        // TODO: send internal email, too

                        // Mail::to($mainOrder->user->email)->send(new NewOrderConfirmation($mainOrder, $user));
                    });
                } catch (ApiErrorException $e) {
                    (new LogService)->logException($e, __CLASS__, __METHOD__, [
                        $event->payload,
                    ]);
                }
            }
        }
    }
}
