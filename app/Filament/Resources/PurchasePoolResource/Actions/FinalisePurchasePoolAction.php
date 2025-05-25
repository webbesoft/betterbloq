<?php

namespace App\Filament\Resources\PurchasePoolResource\Actions;

use App\Mail\OrderFinalisedNotification;
use App\Models\Order;
use App\Models\PurchasePool;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class FinalisePurchasePoolAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'finalisePurchasePool';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Finalise Pool')
            ->color('success')
            ->icon('heroicon-o-check-circle')
            ->requiresConfirmation()
            ->modalHeading('Finalise Purchase Pool')
            ->modalDescription('This will calculate final discounts, attempt to capture payments for all authorized orders in this pool, mark orders as complete, and close the pool. This action is irreversible.')
            ->modalSubmitActionLabel('Yes, Finalise Pool')
            ->action(function (PurchasePool $record, array $data) {
                $this->processFinalization($record);
            });
    }

    protected function processFinalization(PurchasePool $purchasePool): void
    {
        // 1. Check if pool can be finalized (e.g., status is 'active' or 'pending_finalization', end_date has passed)
        if (! in_array($purchasePool->cycle_status, ['active', 'pending_finalization'])) {
            Notification::make()
                ->title('Finalization Failed')
                ->body("Purchase Pool '{$purchasePool->name}' is not in a state that allows finalization (current status: {$purchasePool->cycle_status}).")
                ->danger()
                ->send();

            return;
        }

        // Optional: Check if end_date has passed
        // if ($purchasePool->end_date && $purchasePool->end_date->isFuture()) {
        //     Notification::make()
        //         ->title('Finalization Not Allowed')
        //         ->body("Purchase Pool '{$purchasePool->name}' cannot be finalized before its end date ({$purchasePool->end_date->toFormattedDateString()}).")
        //         ->warning()
        //         ->send();
        //     return;
        // }

        DB::beginTransaction();

        try {
            // 2. Determine the applicable discount tier
            $applicableTier = $purchasePool->getApplicableTier();
            $discountPercentage = $applicableTier ? $applicableTier->discount_percentage : 0;

            if ($applicableTier) {
                Notification::make()
                    ->title('Discount Applied')
                    ->body("Tier '{$applicableTier->name}' with {$discountPercentage}% discount is being applied based on current volume of {$purchasePool->current_volume}.")
                    ->info()
                    ->send();
            } else {
                Notification::make()
                    ->title('No Discount Tier')
                    ->body("No discount tier was applicable for the current volume of {$purchasePool->current_volume}. Orders will be processed at their initial amount.")
                    ->warning()
                    ->send();
            }

            // 3. Fetch orders that are in 'payment_authorized' or 'pending_finalization' state
            $ordersToProcess = $purchasePool->orders()
                ->whereIn('status', ['payment_authorized', 'pending_finalization'])
                ->get();

            if ($ordersToProcess->isEmpty()) {
                $purchasePool->update(['status' => 'closed_empty']); // Or another status indicating no orders to process
                DB::commit();
                Notification::make()
                    ->title('Pool Finalized (No Orders)')
                    ->body("Purchase Pool '{$purchasePool->name}' had no active orders to process. It has been closed.")
                    ->success()
                    ->send();

                return;
            }

            $stripe = new StripeClient(config('cashier.secret'));
            $successfulCaptures = 0;
            $failedCaptures = 0;

            foreach ($ordersToProcess as $order) {
                try {
                    $order->update(['status' => 'processing_capture']); // Mark as attempting capture

                    $initialAmount = $order->initial_amount;
                    $finalAmount = $initialAmount;

                    if ($discountPercentage > 0) {
                        $discountValue = ($initialAmount * $discountPercentage) / 100;
                        $finalAmount = round($initialAmount - $discountValue, 2);
                    }

                    // Ensure final amount is not negative or zero if it's a paid product
                    if ($finalAmount <= 0 && $initialAmount > 0) {
                        // This case should be rare if initial_amount > 0 and discount < 100%
                        Log::warning('Final amount for capture is zero or negative, using a nominal amount or skipping capture.', [
                            'order_id' => $order->id,
                            'initial_amount' => $initialAmount,
                            'discount_percentage' => $discountPercentage,
                            'calculated_final_amount' => $finalAmount,
                        ]);
                        // Decide how to handle: skip capture, capture $0.01, or error.
                        // For now, let's assume we capture the calculated amount, even if 0 for 100% discount.
                        // Stripe requires at least $0.50 (or equivalent) for most transactions.
                        // If finalAmount is truly 0 (e.g. 100% discount), you might not need to "capture".
                        // However, to keep flow consistent, we attempt capture. Stripe might reject < $0.50.
                    }

                    $amountToCaptureInCents = (int) ($finalAmount * 100);

                    if (empty($order->payment_intent_id)) {
                        Log::error('Order missing payment_intent_id, cannot capture.', ['order_id' => $order->id]);
                        $order->update([
                            'status' => 'capture_failed',
                            'final_amount' => $finalAmount, // Store it anyway
                            'notes' => ($order->notes ?? '').' Error: Missing Payment Intent ID.',
                        ]);
                        $failedCaptures++;

                        continue;
                    }

                    // Only capture if there's an amount to capture (Stripe min is usually $0.50)
                    if ($amountToCaptureInCents >= 50) { // Or your currency's minimum if different
                        $capturedIntent = $stripe->paymentIntents->capture(
                            $order->payment_intent_id,
                            ['amount_to_capture' => $amountToCaptureInCents]
                        );

                        if ($capturedIntent->status == 'succeeded') {
                            $order->update([
                                'status' => 'completed', // Or 'finalized', 'shipped' etc.
                                'final_amount' => $finalAmount,
                                'notes' => ($order->notes ?? '').' Payment captured successfully. Tier: '.($applicableTier->name ?? 'None'),
                            ]);
                            $successfulCaptures++;
                            // Send notification email to user
                            Mail::to($order->user->email)->send(new OrderFinalisedNotification($order, $applicableTier));
                        } else {
                            // This case might occur if capture fails for other reasons post-authorization
                            Log::error('Stripe payment capture did not return "succeeded".', [
                                'order_id' => $order->id,
                                'payment_intent_id' => $order->payment_intent_id,
                                'stripe_status' => $capturedIntent->status,
                            ]);
                            $order->update([
                                'status' => 'capture_failed',
                                'final_amount' => $finalAmount,
                                'notes' => ($order->notes ?? '').' Error: Stripe capture status: '.$capturedIntent->status,
                            ]);
                            $failedCaptures++;
                        }
                    } elseif ($finalAmount == 0 && $initialAmount > 0) { // Handle 100% discount scenario
                        $order->update([
                            'status' => 'completed', // Or 'finalized_free'
                            'final_amount' => 0.00,
                            'notes' => ($order->notes ?? '').' Order completed with 100% discount. No payment captured. Tier: '.($applicableTier->name ?? 'None'),
                        ]);
                        $successfulCaptures++; // Count as success as it's processed
                        Mail::to($order->user->email)->send(new OrderFinalisedNotification($order, $applicableTier));
                    } else {
                        // Amount is too small to capture (e.g. $0.01 to $0.49)
                        Log::warning('Final amount too small to capture via Stripe.', [
                            'order_id' => $order->id,
                            'payment_intent_id' => $order->payment_intent_id,
                            'final_amount' => $finalAmount,
                        ]);
                        $order->update([
                            'status' => 'capture_failed', // Or a specific status like 'amount_too_small'
                            'final_amount' => $finalAmount,
                            'notes' => ($order->notes ?? '').' Error: Final amount too small for Stripe capture.',
                        ]);
                        $failedCaptures++;
                    }

                } catch (ApiErrorException $e) {
                    Log::error('Stripe API Error during payment capture for order.', [
                        'order_id' => $order->id,
                        'payment_intent_id' => $order->payment_intent_id,
                        'error_message' => $e->getMessage(),
                    ]);
                    $order->update([
                        'status' => 'capture_failed',
                        // 'final_amount' might not be set if calculation failed, or set it to attempted amount
                        'notes' => ($order->notes ?? '').' Error: Stripe API Exception - '.$e->getMessage(),
                    ]);
                    $failedCaptures++;
                } catch (\Exception $e) {
                    Log::critical('Generic error during order finalization loop.', [
                        'order_id' => $order->id,
                        'error_message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    // Potentially update order status to a generic error state
                    if ($order->status !== 'completed' && $order->status !== 'capture_failed') {
                        $order->update([
                            'status' => 'processing_error',
                            'notes' => ($order->notes ?? '').' Error: System error during finalization - '.$e->getMessage(),
                        ]);
                    }
                    $failedCaptures++; // Or handle as a separate category of failure
                }
            }

            // 5. Update Purchase Pool status
            $finalPoolStatus = ($failedCaptures > 0) ? 'closed_with_errors' : 'closed';
            $purchasePool->update(['status' => $finalPoolStatus]);

            DB::commit();

            Notification::make()
                ->title('Purchase Pool Finalization Complete')
                ->body("Pool '{$purchasePool->name}' finalized. Successful captures: {$successfulCaptures}. Failed captures: {$failedCaptures}.")
                ->success($failedCaptures === 0)
                ->warning($failedCaptures > 0 && $successfulCaptures > 0)
                ->danger($failedCaptures > 0 && $successfulCaptures === 0)
                ->persistent() // Keep it visible
                ->send();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::emergency("Critical error during purchase pool finalization transaction for Pool ID: {$purchasePool->id}", [
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            Notification::make()
                ->title('Pool Finalization Failed Critically')
                ->body("An unexpected error occurred: {$e->getMessage()}. The transaction has been rolled back. Check logs for details.")
                ->danger()
                ->persistent()
                ->send();
        }
    }
}
