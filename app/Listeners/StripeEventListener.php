<?php

namespace App\Listeners;

use App\Managers\CheckoutSessionCompletedManager;
use App\Models\CheckoutAttempt;
use App\Models\Order;
use App\Services\OrderService;
use Laravel\Cashier\Events\WebhookReceived;

class StripeEventListener
{
    public function handle(WebhookReceived $event): void
    {
        $type = $event->payload['type'] ?? '';
        if (in_array($type, ['checkout.session.expired', 'checkout.session.async_payment_failed'], true)) {
            $id = data_get($event->payload, 'data.object.metadata.checkout_attempt_id');
            if ($id) {
                CheckoutAttempt::whereKey($id)->whereIn('status', ['creating', 'open', 'processing'])
                    ->update(['status' => $type === 'checkout.session.expired' ? 'expired' : 'failed']);
            }

            return;
        }
        if (! in_array($type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
            return; // Allow Cashier to handle its own subscription/invoice events.
        }
        $data = CheckoutSessionCompletedManager::getValidatedSessionData($event->payload);
        if (! $data || Order::withTrashed()->where('stripe_session_id', $data->session['id'])->exists()) {
            return;
        }
        $lines = CheckoutSessionCompletedManager::fetchStripeLineItems($data->session['id']);
        $details = CheckoutSessionCompletedManager::parseItemDetailsMetadata($data->metadata, $data->session['id']);
        // A failed transaction must fail the webhook so Stripe retries delivery.
        app(OrderService::class)->createOrderFromStripeSession($data->session, $data->user, $data->metadata, $lines, $details);
    }
}
