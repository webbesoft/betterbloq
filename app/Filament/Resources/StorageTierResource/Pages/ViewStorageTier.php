<?php

namespace App\Filament\Resources\StorageTierResource\Pages;

use App\Filament\Resources\StorageTierResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewStorageTier extends ViewRecord
{
    protected static string $resource = StorageTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
