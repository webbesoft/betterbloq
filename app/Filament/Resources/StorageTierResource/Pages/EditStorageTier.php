<?php

namespace App\Filament\Resources\StorageTierResource\Pages;

use App\Filament\Resources\StorageTierResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStorageTier extends EditRecord
{
    protected static string $resource = StorageTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
