<?php

namespace App\Console\Commands;

use App\Models\CheckoutAttempt;
use App\Services\CheckoutReconciliationService;
use Illuminate\Console\Command;
use Throwable;

class ReconcileCheckouts extends Command
{
    protected $signature = 'payments:reconcile-checkouts';

    protected $description = 'Recover paid, expired, or interrupted checkout attempts from Stripe';

    public function handle(CheckoutReconciliationService $service): int
    {
        $failed = false;
        foreach (CheckoutAttempt::whereIn('status', ['creating', 'open', 'processing'])
            ->where('created_at', '<', now()->subMinutes(5))->lazyById(100) as $attempt) {
            try {
                $service->reconcile($attempt);
            } catch (Throwable $e) {
                report($e);
                $this->error('Checkout '.$attempt->id.': '.$e->getMessage());
                $failed = true;
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
