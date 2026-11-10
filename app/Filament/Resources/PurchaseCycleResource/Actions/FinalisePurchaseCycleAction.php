<?php

namespace App\Filament\Resources\PurchaseCycleResource\Actions;

use App\Jobs\FinalizePurchaseCycle;
use App\Models\PurchaseCycle;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class FinalisePurchaseCycleAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'finalisePurchaseCycle';
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->label('Finalise Cycle')->color('success')->icon('heroicon-o-check-circle')
            ->requiresConfirmation()->modalHeading('Finalise Purchase Cycle')
            ->modalDescription('Finalize paid orders, refund unlocked volume discounts, and close the cycle. Outstanding checkouts must be resolved first.')
            ->modalSubmitActionLabel('Yes, Finalise Cycle')
            ->action(function (PurchaseCycle $record) {
                FinalizePurchaseCycle::dispatch($record->id);
                Notification::make()->title('Cycle finalization queued')->success()->send();
            });
    }
}
