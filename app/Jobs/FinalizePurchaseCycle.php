<?php

namespace App\Jobs;

use App\Services\CycleFinalizationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class FinalizePurchaseCycle implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(public int $cycleId) {}

    public function handle(CycleFinalizationService $service): void
    {
        $results = $service->finalize($this->cycleId);
        if ($results['failed'] > 0) {
            throw new \RuntimeException('Some payment operations require retry or reconciliation.');
        }
    }

    public function backoff(): array
    {
        return [60, 600];
    }
}
