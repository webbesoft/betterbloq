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
        $activePoolsCount = PurchasePool::where('start_date', '<=', $now)
                                       ->where('end_date', '>=', $now)
                                       // Optionally add other status checks if needed
                                       // ->where('status', '!=', 'completed')
                                       // ->where('status', '!=', 'cancelled')
                                       ->count();

        return [
            Stat::make('Active Purchase Pools', $activePoolsCount)
                ->description('Pools currently accepting orders')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('info'),
        ];
    }
}
