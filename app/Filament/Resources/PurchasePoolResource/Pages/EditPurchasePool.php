<?php

namespace App\Filament\Resources\PurchasePoolResource\Pages;

use App\Filament\Resources\PurchasePoolResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurchasePool extends EditRecord
{
    protected static string $resource = PurchasePoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
