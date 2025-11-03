<?php

namespace App\Filament\Resources\ProductRatingResource\Pages;

use App\Filament\Resources\ProductRatingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductRatings extends ListRecords
{
    protected static string $resource = ProductRatingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
