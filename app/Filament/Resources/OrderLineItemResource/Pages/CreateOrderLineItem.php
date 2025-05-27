<?php

namespace App\Filament\Resources\OrderLineItemResource\Pages;

use App\Filament\Resources\OrderLineItemResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderLineItem extends CreateRecord
{
    protected static string $resource = OrderLineItemResource::class;
}
