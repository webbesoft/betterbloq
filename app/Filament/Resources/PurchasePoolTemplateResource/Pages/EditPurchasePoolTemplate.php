<?php

namespace App\Filament\Resources\PurchasePoolTemplateResource\Pages;

use App\Filament\Resources\PurchasePoolTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurchasePoolTemplate extends EditRecord
{
    protected static string $resource = PurchasePoolTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
