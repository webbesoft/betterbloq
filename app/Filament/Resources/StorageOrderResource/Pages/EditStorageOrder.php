<?php

namespace App\Filament\Resources\StorageOrderResource\Pages;

use App\Filament\Resources\StorageOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStorageOrder extends EditRecord
{
    protected static string $resource = StorageOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
