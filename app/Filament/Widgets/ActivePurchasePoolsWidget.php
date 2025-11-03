<?php

namespace App\Filament\Widgets;

use App\Models\PurchasePool;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ActivePurchasePoolsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $now = Carbon::now();
        $activePoolsCount = PurchasePool::where('cycle_status', '!=', 'cancelled')
            ->count();

        return [
            Stat::make('Active Purchase Pools', $activePoolsCount)
                ->description('Pools currently accepting orders')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('info'),
        ];
    }
}
