<?php

namespace App\Filament\Resources\AppResource\Pages;

use App\Filament\Resources\AppResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageApps extends ManageRecords
{
    protected static string $resource = AppResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
