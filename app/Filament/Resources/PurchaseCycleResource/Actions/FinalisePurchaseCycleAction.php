<?php

namespace App\Filament\Resources\PurchaseCycleResource\Actions;

use App\Managers\FinalizePurchasePoolManager;
use App\Models\PurchaseCycle;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;

class FinalisePurchaseCycleAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'finalisePurchaseCycle';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Finalise Cycle')
            ->color('success')
            ->icon('heroicon-o-check-circle')
            ->requiresConfirmation()
            ->modalHeading('Finalise Purchase Cycle')
            ->modalDescription('This will calculate final discounts, attempt to capture payments for all authorized orders in this cycle, mark orders as complete, and close the pools. This action is irreversible.')
            ->modalSubmitActionLabel('Yes, Finalise Cycle')
            ->action(function (PurchaseCycle $record, array $data) {
                $this->processFinalization($record);
            });
    }

    protected function processFinalization(PurchaseCycle $purchaseCycle): void
    {
        if (! FinalizePurchasePoolManager::canFinalizeCycle($purchaseCycle)) {
            return;
        }

        // Set cycle to processing state
        $purchaseCycle->update(['status' => 'processing_payments']);

        DB::beginTransaction();

        try {
            $purchasePools = FinalizePurchasePoolManager::getFinalizablePurchasePools($purchaseCycle);

            if ($purchasePools->isEmpty()) {
                FinalizePurchasePoolManager::handleEmptyCycle($purchaseCycle);

                return;
            }

            $results = FinalizePurchasePoolManager::processAllPoolsInCycle($purchasePools);
            FinalizePurchasePoolManager::finalizeCycle($purchaseCycle, $results);

            DB::commit();
            FinalizePurchasePoolManager::sendFinalizationNotification($purchaseCycle, $results);

        } catch (\Exception $e) {
            DB::rollBack();
            FinalizePurchasePoolManager::handleCriticalError($purchaseCycle, $e);
        }
    }
}
