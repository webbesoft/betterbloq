<?php

namespace App\Filament\Resources\StorageOrderLineItemResource\Pages;

use App\Filament\Resources\StorageOrderLineItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStorageOrderLineItem extends EditRecord
{
    protected static string $resource = StorageOrderLineItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
