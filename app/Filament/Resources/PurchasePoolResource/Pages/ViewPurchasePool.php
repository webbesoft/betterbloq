<?php

namespace App\Filament\Resources\PurchasePoolResource\Pages;

use App\Filament\Resources\PurchasePoolResource;
use App\Filament\Resources\PurchasePoolResource\Actions\FinalisePurchasePoolAction;
use App\Models\PurchasePool;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPurchasePool extends ViewRecord
{
    protected static string $resource = PurchasePoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            FinalisePurchasePoolAction::make()
                ->visible(fn (PurchasePool $record) => in_array($record->status, ['active', 'pending_finalization'])),
        ];
    }
}
