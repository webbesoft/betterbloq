<?php

namespace App\Filament\Resources\PurchasePoolResource\Pages;

use App\Filament\Resources\PurchasePoolResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPurchasePool extends ViewRecord
{
    protected static string $resource = PurchasePoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
