<?php

namespace App\Filament\Resources\PurchasePoolRequestResource\Pages;

use App\Filament\Resources\PurchasePoolRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurchasePoolRequest extends EditRecord
{
    protected static string $resource = PurchasePoolRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
