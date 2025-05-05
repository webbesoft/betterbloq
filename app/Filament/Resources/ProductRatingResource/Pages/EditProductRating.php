<?php

namespace App\Filament\Resources\ProductRatingResource\Pages;

use App\Filament\Resources\ProductRatingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductRating extends EditRecord
{
    protected static string $resource = ProductRatingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
