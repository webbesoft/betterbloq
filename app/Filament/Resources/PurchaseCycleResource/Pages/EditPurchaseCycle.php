<?php

namespace App\Filament\Resources\PurchaseCycleResource\Pages;

use App\Filament\Resources\PurchaseCycleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseCycle extends EditRecord
{
    protected static string $resource = PurchaseCycleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
