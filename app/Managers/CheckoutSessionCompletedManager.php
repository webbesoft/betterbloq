<?php

namespace App\Managers;

use App\Models\Log;
use App\Models\User;
use Illuminate\Support\Facades\Log as FacadesLog;
use Stripe\Collection;
use Stripe\StripeClient;

class CheckoutSessionCompletedManager
{
    public static function getValidatedSessionData(array $payload): ?object
    {
        $session = data_get($payload, 'data.object');
        $stripeCustomerId = data_get($session, 'customer');
        $user = User::whereStripeCustomerId($stripeCustomerId)->first();

        if (! $user) {
            Log::warning('Webhook: User not found for Stripe customer ID.', ['stripe_customer_id' => $stripeCustomerId, 'session_id' => $session['id']]);

            return null;
        }

        if (data_get($session, 'mode') === 'subscription') {
            info('Webhook: Ignoring subscription event for checkout.session.completed.', ['session_id' => $session['id']]);

            return null;
        }

        // Guard against asynchronous payment methods (e.g. ACH, SEPA, bank transfers).
        // For these methods, checkout.session.completed fires when the customer submits
        // the form, but with payment_status: 'unpaid'. The actual debit can fail days
        // later. We must only provision orders once payment is confirmed.
        //
        // The initial charge is processed immediately at checkout, so we expect 'paid'.
        $paymentStatus = data_get($session, 'payment_status');
        if (! in_array($paymentStatus, ['paid', 'no_payment_required'])) {
            FacadesLog::warning('Webhook: Skipping order creation — payment not confirmed.', [
                'session_id' => $session['id'],
                'payment_status' => $paymentStatus,
            ]);

            return null;
        }

        return (object) [
            'session' => $session,
            'user' => $user,
            'metadata' => $session['metadata'],
        ];
    }

    /**
     * @throws ApiErrorException
     */
    public static function fetchStripeLineItems(string $sessionId): ?Collection
    {
        $stripe = new StripeClient(config('services.stripe.secret'));
        $lineItems = $stripe->checkout->sessions->allLineItems($sessionId, ['limit' => 100]);

        if (empty($lineItems->data)) {
            Log::error('Webhook: No line items found in completed Stripe session.', ['session_id' => $sessionId]);

            return null;
        }

        return $lineItems;
    }

    public static function parseItemDetailsMetadata(array $metadata, string $sessionId): array
    {
        $itemDetailsJson = data_get($metadata, 'item_details');
        $productSubtotalMeta = data_get($metadata, 'product_subtotal');
        $storageCostAppliedMeta = data_get($metadata, 'storage_cost_applied');
        $decodedItemDetails = [];

        if ($itemDetailsJson) {
            $decodedItemDetails = json_decode($itemDetailsJson, true);
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decodedItemDetails)) {
                info('Webhook Error: Failed to decode item_details metadata JSON.', [
                    'json_error' => json_last_error_msg(),
                    'itemDetailsJson' => $itemDetailsJson,
                    'session_id' => $sessionId,
                ]);

                return []; // Return empty on error
            }
        } else {
            info('Webhook Warning: item_details metadata was missing or empty.', ['session_id' => $sessionId]);
        }

        $itemDetailsLookup = [];
        if (! empty($decodedItemDetails)) {
            foreach ($decodedItemDetails as $itemDetail) {
                if (isset($itemDetail['product_id'])) {
                    $itemDetailsLookup[$itemDetail['product_id']] = $itemDetail;
                } else {
                    info("Webhook Warning: item_details metadata entry missing 'product_id'.", ['itemDetail' => $itemDetail, 'session_id' => $sessionId]);
                }
            }
        }

        return $itemDetailsLookup;
    }
}
