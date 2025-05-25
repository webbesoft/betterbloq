<?php

namespace App\Filament\Resources\PurchaseCycleResource\Pages;

use App\Filament\Resources\PurchaseCycleResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPurchaseCycle extends ViewRecord
{
    protected static string $resource = PurchaseCycleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
