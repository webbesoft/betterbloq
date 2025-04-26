<?php

namespace App\Filament\Resources\PlanLimitResource\Pages;

use App\Filament\Resources\PlanLimitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlanLimit extends EditRecord
{
    protected static string $resource = PlanLimitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
