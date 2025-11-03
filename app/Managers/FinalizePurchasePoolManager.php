<?php

namespace App\Managers;

use App\Mail\OrderFinalisedNotification;
use App\Models\Order;
use App\Models\PurchaseCycle;
use App\Models\PurchasePool;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\StripeClient;

class FinalizePurchasePoolManager
{
    public static function canFinalizeCycle(PurchaseCycle $purchaseCycle): bool
    {
        if (! in_array($purchaseCycle->status, ['active', 'calculating_discounts'])) {
            Notification::make()
                ->title('Finalization Failed')
                ->body("Purchase Cycle '{$purchaseCycle->name}' is not in a finalizable state (current status: {$purchaseCycle->status}).")
                ->danger()
                ->send();

            return false;
        }

        return true;
    }

    public static function getFinalizablePurchasePools(PurchaseCycle $purchaseCycle)
    {
        return $purchaseCycle->purchasePools()
            ->whereIn('cycle_status', ['accumulating'])
            ->with(['orders' => function ($query) {
                $query->whereIn('status', ['payment_authorized', 'pending_finalization'])
                    ->whereNotNull('payment_intent_id');
            }, 'tiers'])
            ->get();
    }

    public static function processAllPoolsInCycle($purchasePools): array
    {
        $totalSuccessful = 0;
        $totalFailed = 0;
        $poolResults = [];

        foreach ($purchasePools as $pool) {
            $poolResult = self::processPoolFinalization($pool);
            $totalSuccessful += $poolResult['successful'];
            $totalFailed += $poolResult['failed'];

            // Track detailed pool results for reporting
            $poolResults[] = [
                'pool_id' => $pool->id,
                'pool_name' => $pool->name,
                'successful' => $poolResult['successful'],
                'failed' => $poolResult['failed'],
                'failed_orders' => $poolResult['failed_orders'] ?? [],
                'status' => $poolResult['pool_status'],
            ];

            // Update cycle product volumes
            self::updateCycleProductVolume($pool, $poolResult['discount_percentage']);
        }

        return [
            'successful' => $totalSuccessful,
            'failed' => $totalFailed,
            'processed_pools' => $purchasePools->count(),
            'pool_details' => $poolResults,
        ];
    }

    private function processPoolFinalization(PurchasePool $pool): array
    {
        $orders = $pool->orders;

        if ($orders->isEmpty()) {
            $pool->update(['cycle_status' => 'finalized']);

            return [
                'successful' => 0,
                'failed' => 0,
                'discount_percentage' => 0,
                'pool_status' => 'finalized',
                'failed_orders' => [],
            ];
        }

        $discountPercentage = $pool->getApplicableDiscountPercentage();
        self::notifyDiscountApplication($pool, $discountPercentage);

        $successfulCaptures = 0;
        $failedCaptures = 0;
        $failedOrderDetails = [];
        $stripe = new StripeClient(config('cashier.secret'));

        foreach ($orders as $order) {
            try {
                $result = self::processOrderCapture($order, $discountPercentage, $stripe);
                if ($result['success']) {
                    $successfulCaptures++;
                } else {
                    $failedCaptures++;
                    $failedOrderDetails[] = [
                        'order_id' => $order->id,
                        'user_email' => $order->user->email,
                        'reason' => $result['failure_reason'] ?? 'Unknown error',
                        'amount' => $order->initial_amount,
                    ];
                }
            } catch (\Exception $e) {
                self::handleOrderError($order, $e);
                $failedCaptures++;
                $failedOrderDetails[] = [
                    'order_id' => $order->id,
                    'user_email' => $order->user->email,
                    'reason' => 'Exception: '.$e->getMessage(),
                    'amount' => $order->initial_amount,
                ];
            }
        }

        // Determine pool status based on results
        $poolStatus = self::determinePoolStatus($successfulCaptures, $failedCaptures);
        self::updatePoolStatus($pool, $poolStatus);

        return [
            'successful' => $successfulCaptures,
            'failed' => $failedCaptures,
            'discount_percentage' => $discountPercentage,
            'pool_status' => $poolStatus,
            'failed_orders' => $failedOrderDetails,
        ];
    }

    private function processOrderCapture(Order $order, float $discountPercentage, StripeClient $stripe): array
    {
        $order->update(['status' => 'processing_capture']);

        $finalAmount = self::calculateFinalAmount($order->initial_amount, $discountPercentage);
        $amountInCents = (int) ($finalAmount * 100);

        if ($finalAmount <= 0) {
            return self::handleFreeOrder($order, $finalAmount);
        }

        if ($amountInCents < 50) {
            return self::handleInsufficientAmount($order, $finalAmount);
        }

        $capturedIntent = $stripe->paymentIntents->capture(
            $order->payment_intent_id,
            ['amount_to_capture' => $amountInCents]
        );

        if ($capturedIntent->status === 'succeeded') {
            $order->update([
                'status' => 'completed',
                'final_amount' => $finalAmount,
                'notes' => self::buildSuccessNotes($order, $discountPercentage),
            ]);

            Mail::to($order->user->email)->send(new OrderFinalisedNotification($order, $discountPercentage));

            return ['success' => true];
        }

        self::handleFailedCapture($order, $finalAmount, $capturedIntent->status);

        return [
            'success' => false,
            'failure_reason' => 'Stripe capture failed: '.$capturedIntent->status,
        ];
    }

    private function calculateFinalAmount(float $initialAmount, float $discountPercentage): float
    {
        if ($discountPercentage <= 0) {
            return $initialAmount;
        }

        $discountValue = ($initialAmount * $discountPercentage) / 100;

        return round($initialAmount - $discountValue, 2);
    }

    private function handleFreeOrder(Order $order, float $finalAmount): array
    {
        $order->update([
            'status' => 'completed',
            'final_amount' => $finalAmount,
            'notes' => self::buildFreeOrderNotes($order),
        ]);

        Mail::to($order->user->email)->send(new OrderFinalisedNotification($order));

        return ['success' => true];
    }

    private function handleInsufficientAmount(Order $order, float $finalAmount): array
    {
        Log::warning('Final amount too small for Stripe capture', [
            'order_id' => $order->id,
            'final_amount' => $finalAmount,
        ]);

        $order->update([
            'status' => 'capture_failed',
            'final_amount' => $finalAmount,
            'notes' => ($order->notes ?? '').' Error: Amount too small for capture.',
        ]);

        return [
            'success' => false,
            'failure_reason' => 'Amount too small for Stripe capture ('.$finalAmount.')',
        ];
    }

    private function determinePoolStatus(int $successful, int $failed): string
    {
        if ($failed === 0) {
            return 'finalized';
        } elseif ($successful > 0) {
            return 'failed'; // Partial failure - requires manual intervention
        } else {
            return 'failed'; // Complete failure
        }
    }

    private function updateCycleProductVolume(PurchasePool $pool, float $discountPercentage): void
    {
        $pool->purchaseCycle->cycleProductVolumes()
            ->updateOrCreate(
                ['product_id' => $pool->product_id],
                [
                    'total_aggregated_quantity' => $pool->current_volume,
                    'achieved_discount_percentage' => $discountPercentage,
                ]
            );
    }

    private function notifyDiscountApplication(PurchasePool $pool, float $discountPercentage): void
    {
        $notification = $discountPercentage > 0
            ? Notification::make()
                ->title('Discount Applied')
                ->body("Pool '{$pool->name}' applying {$discountPercentage}% discount based on volume of {$pool->current_volume}.")
                ->info()
            : Notification::make()
                ->title('No Discount')
                ->body("Pool '{$pool->name}' has no applicable discount for current volume.")
                ->warning();

        $notification->send();
    }

    private function buildSuccessNotes(Order $order, float $discountPercentage): string
    {
        return ($order->notes ?? '')." Payment captured successfully. Discount: {$discountPercentage}%";
    }

    private function buildFreeOrderNotes(Order $order): string
    {
        return ($order->notes ?? '').' Order completed with 100% discount. No payment captured.';
    }

    private function handleOrderError(Order $order, \Exception $e): void
    {
        Log::error('Error processing order capture', [
            'order_id' => $order->id,
            'error' => $e->getMessage(),
        ]);

        $order->update([
            'status' => 'capture_failed',
            'notes' => ($order->notes ?? '').' Error: '.$e->getMessage(),
        ]);
    }

    private function handleFailedCapture(Order $order, float $finalAmount, string $stripeStatus): void
    {
        Log::error('Stripe capture failed', [
            'order_id' => $order->id,
            'stripe_status' => $stripeStatus,
        ]);

        $order->update([
            'status' => 'capture_failed',
            'final_amount' => $finalAmount,
            'notes' => ($order->notes ?? '')." Error: Stripe status: {$stripeStatus}",
        ]);
    }

    private function updatePoolStatus(PurchasePool $pool, int $failedCaptures): void
    {
        $pool->update([
            'cycle_status' => $failedCaptures > 0 ? 'failed' : 'finalized',
        ]);
    }

    public static function finalizeCycle(PurchaseCycle $purchaseCycle, array $results): void
    {
        $status = $results['failed'] > 0 ? 'closed' : 'closed'; // Adjust statuses as needed
        $purchaseCycle->update(['status' => $status]);
    }

    public static function handleEmptyCycle(PurchaseCycle $purchaseCycle): void
    {
        $purchaseCycle->update(['status' => 'closed']);
        DB::commit();

        Notification::make()
            ->title('Cycle Finalized (No Orders)')
            ->body("Purchase Cycle '{$purchaseCycle->name}' had no orders to process.")
            ->success()
            ->send();
    }

    public static function sendFinalizationNotification(PurchaseCycle $purchaseCycle, array $results): void
    {
        $notification = Notification::make()
            ->title('Purchase Cycle Finalization Complete')
            ->body("Cycle '{$purchaseCycle->name}' processed {$results['processed_pools']} pools. Successful: {$results['successful']}, Failed: {$results['failed']}")
            ->persistent();

        $results['failed'] === 0 ? $notification->success() : $notification->warning();
        $notification->send();
    }

    public static function handleCriticalError(PurchaseCycle $purchaseCycle, \Exception $e): void
    {
        Log::emergency('Critical error during cycle finalization', [
            'cycle_id' => $purchaseCycle->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        Notification::make()
            ->title('Cycle Finalization Failed')
            ->body("Critical error occurred: {$e->getMessage()}")
            ->danger()
            ->persistent()
            ->send();
    }
}
