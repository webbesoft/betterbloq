<?php

namespace App\Managers;

use App\Models\CheckoutAttempt;
use App\Models\User;
use App\Payments\StripeGateway;
use RuntimeException;
use Stripe\Collection;

class CheckoutSessionCompletedManager
{
    public static function getValidatedSessionData(array $payload): ?object
    {
        $session = data_get($payload, 'data.object', []);
        if (($session['mode'] ?? null) !== 'payment') {
            return null;
        }
        $metadata = $session['metadata'] ?? [];
        if (empty($metadata['checkout_attempt_id']) && empty($metadata['item_details'])) {
            return null; // A different Cashier checkout flow owns this session.
        }
        if (! in_array($session['payment_status'] ?? null, ['paid', 'no_payment_required'], true)) {
            if (! empty($metadata['checkout_attempt_id'])) {
                CheckoutAttempt::whereKey($metadata['checkout_attempt_id'])->whereIn('status', ['creating', 'open'])
                    ->update(['status' => 'processing', 'stripe_session_id' => $session['id']]);
            }

            return null;
        }
        $user = User::where('stripe_id', $session['customer'] ?? null)->first();
        if (! $user || empty($session['customer'])) {
            throw new RuntimeException('Paid checkout customer could not be resolved.');
        }
        if (isset($metadata['user_id']) && (int) $metadata['user_id'] !== $user->id) {
            throw new RuntimeException('Paid checkout customer metadata mismatch.');
        }

        return (object) compact('session', 'user', 'metadata');
    }

    public static function fetchStripeLineItems(string $sessionId): Collection
    {
        $lines = app(StripeGateway::class)->lineItems($sessionId);
        if (! $lines->data) {
            throw new RuntimeException('Paid checkout has no line items.');
        }

        return $lines;
    }

    public static function parseItemDetailsMetadata(array $metadata, string $sessionId): array
    {
        if (! empty($metadata['checkout_attempt_id'])) {
            $attempt = CheckoutAttempt::findOrFail($metadata['checkout_attempt_id']);

            return collect($attempt->snapshot)->keyBy('id')->all();
        }
        $details = json_decode($metadata['item_details'] ?? '', true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($details) || ! $details) {
            throw new RuntimeException('Missing checkout item details.');
        }

        return collect($details)->keyBy('product_id')->all();
    }
}
