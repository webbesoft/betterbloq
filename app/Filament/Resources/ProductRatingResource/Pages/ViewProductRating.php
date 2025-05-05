<?php

namespace App\Filament\Resources\ProductRatingResource\Pages;

use App\Filament\Resources\ProductRatingResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProductRating extends ViewRecord
{
    protected static string $resource = ProductRatingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
