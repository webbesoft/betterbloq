<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\PaymentOperation;
use App\Payments\StripeGateway;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReconcileOrderPrices extends Command
{
    protected $signature = 'payments:reconcile-order-prices {order} {original-unit-amounts : JSON mapping product line IDs to original unit prices in USD cents}';

    protected $description = 'Record verified historical prices before finalizing a legacy order; does not move money';

    public function handle(StripeGateway $gateway): int
    {
        $prices = json_decode($this->argument('original-unit-amounts'), true, 512, JSON_THROW_ON_ERROR);
        $order = Order::with('lineItems')->findOrFail($this->argument('order'));
        if (! is_array($prices) || ! $order->payment_intent_id || $order->status === 'completed') {
            throw new RuntimeException('An unfinalized paid order and original prices are required.');
        }
        $intent = $gateway->paymentIntent($order->payment_intent_id);
        $charged = (int) round($order->initial_amount * 100);
        if (($intent['currency'] ?? null) !== 'usd' || $intent['status'] !== 'succeeded'
            || (int) $intent['amount_received'] !== $charged || data_get($intent, 'latest_charge.amount_refunded', 0) !== 0) {
            throw new RuntimeException('Stripe payment requires manual reconciliation before recording historical prices.');
        }
        DB::transaction(function () use ($order, $prices, $charged) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if (PaymentOperation::where('order_id', $order->id)->exists()) {
                throw new RuntimeException('An existing payment operation must not be repriced.');
            }
            $order->load('lineItems');
            $productLineIds = $order->lineItems->whereNotNull('product_id')->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
            $providedIds = array_map('intval', array_keys($prices));
            sort($providedIds);
            if ($providedIds !== $productLineIds) {
                throw new RuntimeException('Supply exactly one original price for each product line.');
            }
            $total = 0;
            foreach ($order->lineItems as $line) {
                $lineCharged = (int) round($line->total_price * 100);
                $original = $line->product_id !== null ? ($prices[$line->id] ?? null) : null;
                if ($line->product_id !== null && (! is_int($original) || $original <= 0 || $original * $line->quantity < $lineCharged)) {
                    throw new RuntimeException('Original prices must be positive integer cents and cover the charged amount.');
                }
                $total += $lineCharged;
                $line->update(['original_unit_amount' => $original, 'charged_amount' => $lineCharged]);
            }
            if ($total !== $charged) {
                throw new RuntimeException('Legacy line totals do not reconcile to the Stripe payment.');
            }
        }, 3);
        $this->info('Historical price snapshot recorded. No payment or refund was issued.');

        return self::SUCCESS;
    }
}
