<?php

namespace App\Console\Commands;

use App\Services\CycleFinalizationService;
use Illuminate\Console\Command;

class FinalizeCycle extends Command
{
    protected $signature = 'payments:finalize-cycle {cycle : Purchase cycle ID}';

    protected $description = 'Finalize or resume a purchase cycle using its persisted payment operations';

    public function handle(CycleFinalizationService $service): int
    {
        $results = $service->finalize((int) $this->argument('cycle'));
        $this->info("Completed: {$results['successful']}; failed: {$results['failed']}");

        return $results['failed'] ? self::FAILURE : self::SUCCESS;
    }
}
