<?php

namespace App\Filament\Resources\PurchaseCycleResource\Pages;

use App\Filament\Resources\PurchaseCycleResource;
use App\Filament\Resources\PurchaseCycleResource\Actions\FinalisePurchaseCycleAction;
use App\Models\PurchaseCycle;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPurchaseCycle extends ViewRecord
{
    protected static string $resource = PurchaseCycleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            FinalisePurchaseCycleAction::make()
                ->visible(fn (PurchaseCycle $record) => in_array($record->status, [PurchaseCycle::STATUS_ACTIVE]) && $record->purchasePools()->count() > 0)
                ->requiresConfirmation(),
        ];
    }
}
