<?php

namespace App\Services;

use App\Models\CheckoutAttempt;
use App\Models\Order;
use App\Models\PaymentOperation;
use App\Models\PurchaseCycle;
use App\Payments\StripeGateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class CycleFinalizationService
{
    public function finalize(int $cycleId): array
    {
        $token = $this->claim($cycleId);
        $results = ['successful' => 0, 'failed' => 0];
        try {
            $orders = Order::withTrashed()->where('purchase_cycle_id', $cycleId)
                ->whereIn('status', ['paid', 'payment_authorized', 'pending_finalization', 'processing_capture', 'refund_failed', 'capture_failed'])
                ->orderBy('id')->lazyById(100);
            foreach ($orders as $order) {
                $this->heartbeat($cycleId, $token);
                try {
                    $this->finalizeOrder($order);
                    $results['successful']++;
                } catch (Throwable $e) {
                    report($e);
                    // Preserve completed financial effects and their operation record.
                    Order::whereKey($order->id)->where('status', '!=', 'completed')->update(['status' => 'refund_failed']);
                    PaymentOperation::where('order_id', $order->id)->where('status', '!=', 'completed')
                        ->update(['last_error' => $e->getMessage()]);
                    $results['failed']++;
                }
            }
            DB::transaction(function () use ($cycleId, $token, $results) {
                $cycle = PurchaseCycle::whereKey($cycleId)->lockForUpdate()->firstOrFail();
                if ($cycle->finalization_token !== $token) {
                    throw new RuntimeException('Finalization lease was lost.');
                }
                $cycle->purchasePools()->update(['cycle_status' => $results['failed'] ? 'failed' : 'finalized']);
                $cycle->forceFill([
                    'status' => $results['failed'] ? 'calculating_discounts' : 'closed',
                    'finalization_token' => null, 'finalization_lease_until' => null,
                ])->save();
            });
        } catch (Throwable $e) {
            PurchaseCycle::whereKey($cycleId)->where('finalization_token', $token)->update([
                'status' => 'calculating_discounts', 'finalization_token' => null, 'finalization_lease_until' => null,
            ]);
            throw $e;
        }

        return $results;
    }

    private function claim(int $cycleId): string
    {
        return DB::transaction(function () use ($cycleId) {
            $cycle = PurchaseCycle::whereKey($cycleId)->lockForUpdate()->firstOrFail();
            if ($cycle->status === 'closed') {
                throw new RuntimeException('This cycle has already been finalized.');
            }
            if ($cycle->finalization_lease_until && now()->lt($cycle->finalization_lease_until)) {
                throw new RuntimeException('This cycle is already being finalized.');
            }
            if (! in_array($cycle->status, ['active', 'calculating_discounts', 'processing_payments'], true)) {
                throw new RuntimeException('This cycle is not finalizable.');
            }
            // An open or asynchronous checkout can still turn into a paid order.
            // Reconcile/expire it before freezing volumes and closing the cycle.
            if (CheckoutAttempt::where('purchase_cycle_id', $cycleId)->whereIn('status', ['creating', 'open', 'processing'])->exists()) {
                throw new RuntimeException('Reconcile outstanding checkouts before finalizing this cycle.');
            }
            foreach ($cycle->purchasePools()->orderBy('id')->lockForUpdate()->get() as $pool) {
                if ($pool->final_achieved_discount_percentage === null) {
                    $discount = $pool->getApplicableDiscountPercentage();
                    if ($discount < 0 || $discount > 100) {
                        throw new RuntimeException('Invalid pool discount percentage.');
                    }
                    $pool->forceFill(['final_achieved_discount_percentage' => $discount])->save();
                }
            }
            $token = (string) Str::uuid();
            $cycle->forceFill(['status' => 'processing_payments', 'finalization_token' => $token,
                'finalization_lease_until' => now()->addMinutes(10)])->save();

            return $token;
        }, 3);
    }

    private function heartbeat(int $cycleId, string $token): void
    {
        if (! PurchaseCycle::whereKey($cycleId)->where('finalization_token', $token)->update([
            'finalization_lease_until' => now()->addMinutes(10),
        ])) {
            throw new RuntimeException('Finalization lease was lost.');
        }
    }

    private function finalizeOrder(Order $order): void
    {
        $operation = DB::transaction(function () use ($order) {
            $order = Order::withTrashed()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $existing = PaymentOperation::where('order_id', $order->id)->first();
            if ($existing) {
                return $existing;
            }
            $order->load('lineItems.purchasePool');
            if ($order->lineItems->isEmpty()) {
                throw new RuntimeException('An order without line items requires reconciliation.');
            }
            $amounts = [];
            $charged = 0;
            foreach ($order->lineItems as $line) {
                if ($line->charged_amount === null || ($line->product_id !== null && $line->original_unit_amount === null)) {
                    throw new RuntimeException('Legacy order lacks a reliable original-price snapshot; reconcile before refunding.');
                }
                $charged += (int) $line->charged_amount;
                $final = (int) $line->charged_amount;
                $discount = 0;
                if ($line->product_id !== null) {
                    $pool = $line->purchasePool;
                    if (! $pool || $pool->purchase_cycle_id !== $order->purchase_cycle_id
                        || $pool->final_achieved_discount_percentage === null) {
                        throw new RuntimeException('Order line has no frozen pool discount.');
                    }
                    $discount = (int) round($pool->final_achieved_discount_percentage * 100);
                    if ($discount < 0 || $discount > 10000) {
                        throw new RuntimeException('Invalid frozen discount.');
                    }
                    $final = min($final, intdiv((int) $line->original_unit_amount * $line->quantity * (10000 - $discount) + 5000, 10000));
                }
                $amounts[$line->id] = ['amount' => $final, 'discount_basis_points' => $discount];
            }
            if ($charged !== (int) round($order->initial_amount * 100)) {
                throw new RuntimeException('Order total does not match its charged line amounts.');
            }
            $order->update(['status' => 'processing_capture']);

            return PaymentOperation::create([
                'id' => (string) Str::uuid(), 'order_id' => $order->id,
                'target_amount' => array_sum(array_column($amounts, 'amount')), 'line_amounts' => $amounts,
            ]);
        }, 3);
        if ($operation->status === 'completed') {
            return;
        }
        $gateway = app(StripeGateway::class);
        if (! $operation->kind) {
            if (! $order->payment_intent_id) {
                if ((int) $operation->target_amount !== 0 || (float) $order->initial_amount !== 0.0) {
                    throw new RuntimeException('Order is missing its PaymentIntent.');
                }
                $operation->update(['kind' => 'none', 'request' => []]);
            } else {
                $intent = $gateway->paymentIntent($order->payment_intent_id);
                if (($intent['currency'] ?? null) !== 'usd') {
                    throw new RuntimeException('Unexpected PaymentIntent currency.');
                }
                $initial = (int) round($order->initial_amount * 100);
                if ($intent['status'] === 'succeeded') {
                    if ((int) ($intent['amount_received'] ?? 0) !== $initial) {
                        throw new RuntimeException('PaymentIntent received amount does not match the order.');
                    }
                    // Pre-existing external refunds cannot be silently treated as a
                    // volume-discount refund. Resolve them explicitly first.
                    if (data_get($intent, 'latest_charge.amount_refunded', 0) > 0) {
                        throw new RuntimeException('An existing refund requires reconciliation before creating the discount refund.');
                    }
                    $refund = $initial - (int) $operation->target_amount;
                    $operation->update(['kind' => $refund ? 'refund' : 'none', 'request' => $refund ? [
                        'payment_intent' => $order->payment_intent_id, 'amount' => $refund,
                        'reason' => 'requested_by_customer', 'metadata' => ['payment_operation_id' => $operation->id],
                    ] : []]);
                } elseif ($intent['status'] === 'requires_capture') {
                    $operation->update(['kind' => $operation->target_amount ? 'capture' : 'cancel',
                        'request' => ['payment_intent' => $order->payment_intent_id, 'amount' => (int) $operation->target_amount]]);
                } else {
                    throw new RuntimeException('PaymentIntent is not ready for finalization: '.$intent['status']);
                }
            }
        }
        // Save the exact request BEFORE contacting Stripe. Every retry replays it
        // with the same key, even if Stripe succeeded before the process crashed.
        if ($operation->status !== 'applied') {
            $key = 'finalize_'.$operation->id;
            $response = null;
            if ($operation->kind === 'refund' && isset($operation->response['id'])) {
                $response = $gateway->refundById($operation->response['id']);
            } elseif ($operation->created_at->lt(now()->subHours(23)) && $operation->kind !== 'none') {
                // Stripe may prune idempotency keys after 24 hours. Find the original
                // operation instead of risking a second refund after that window.
                if ($operation->kind === 'refund') {
                    $response = $gateway->findRefund($order->payment_intent_id, $operation->id);
                } else {
                    $intent = $gateway->paymentIntent($order->payment_intent_id);
                    if (($operation->kind === 'capture' && $intent['status'] === 'succeeded'
                            && (int) $intent['amount_received'] === (int) $operation->target_amount)
                        || ($operation->kind === 'cancel' && $intent['status'] === 'canceled')) {
                        $response = $intent;
                    }
                }
                if (! $response) {
                    throw new RuntimeException('Payment operation exceeded the safe retry window and requires Stripe reconciliation.');
                }
            }
            $response ??= match ($operation->kind) {
                'refund' => $gateway->refund($operation->request, $key),
                'capture' => $gateway->capture($operation->request['payment_intent'], $operation->request['amount'], $key),
                'cancel' => $gateway->cancel($operation->request['payment_intent'], $key),
                'none' => ['status' => 'succeeded'],
                default => throw new RuntimeException('Unknown payment operation.'),
            };
            $operation->update(['response' => $response]);
            $expectedStatus = $operation->kind === 'cancel' ? 'canceled' : 'succeeded';
            if (($response['status'] ?? '') !== $expectedStatus) {
                throw new RuntimeException('Payment operation is pending or failed: '.($response['status'] ?? 'unknown'));
            }
            $operation->update(['response' => $response, 'status' => 'applied']);
        }
        DB::transaction(function () use ($order, $operation) {
            $locked = PaymentOperation::whereKey($operation->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === 'completed') {
                return;
            }
            foreach ($operation->line_amounts as $lineId => $amount) {
                $order->lineItems()->whereKey($lineId)->update([
                    'final_line_price' => $amount['amount'] / 100,
                    'applied_discount_percentage' => $amount['discount_basis_points'] / 100,
                ]);
            }
            $order->update(['status' => 'completed', 'final_amount' => $operation->target_amount / 100]);
            $locked->update(['status' => 'completed', 'last_error' => null]);
            DB::table('payment_notifications')->insertOrIgnore([
                'order_id' => $order->id, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }, 3);
    }
}
