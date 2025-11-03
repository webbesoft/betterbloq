<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ActiveOrdersWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Define your active order statuses
        $activeOrderStatuses = ['pending', 'processing', 'confirmed'];

        $activeOrdersCount = Order::whereIn('status', $activeOrderStatuses)
            ->count();

        return [
            Stat::make('Active Orders', $activeOrdersCount)
                ->description('Orders currently in progress')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),
        ];
    }
}
