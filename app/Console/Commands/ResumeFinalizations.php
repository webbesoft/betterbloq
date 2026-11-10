<?php

namespace App\Console\Commands;

use App\Jobs\FinalizePurchaseCycle;
use App\Models\PurchaseCycle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResumeFinalizations extends Command
{
    protected $signature = 'payments:resume-finalizations';

    protected $description = 'Resume finalization workers whose leases expired before completion';

    public function handle(): int
    {
        foreach (PurchaseCycle::where(function ($query) {
            $query->where(function ($query) {
                $query->where('status', 'processing_payments')
                    ->where(fn ($query) => $query->whereNull('finalization_lease_until')->orWhere('finalization_lease_until', '<', now()));
            })->orWhere(function ($query) {
                $query->where('status', 'calculating_discounts')->where('updated_at', '<', now()->subMinutes(5))
                    ->whereIn('id', DB::table('orders')->join('payment_operations', 'payment_operations.order_id', '=', 'orders.id')
                        ->where('payment_operations.status', '!=', 'completed')->select('orders.purchase_cycle_id'));
            });
        })->lazyById(100) as $cycle) {
            FinalizePurchaseCycle::dispatch($cycle->id);
        }

        return self::SUCCESS;
    }
}
