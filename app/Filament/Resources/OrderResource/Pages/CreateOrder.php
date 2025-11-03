<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $totalOrderAmount = 0;
        if (isset($data['line_items']) && is_array($data['line_items'])) {
            foreach ($data['line_items'] as $item) {
                $totalOrderAmount += $item['final_line_price'] ?? 0;
            }
        }
        $data['total_amount'] = $totalOrderAmount;
        $data['final_amount'] = $totalOrderAmount; // Adjust if there are order-level discounts/charges

        return $data;
    }
}
