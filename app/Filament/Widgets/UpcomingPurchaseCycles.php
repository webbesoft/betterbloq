<?php

namespace App\Filament\Widgets;

use App\Models\PurchaseCycle;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UpcomingPurchaseCycles extends BaseWidget
{
    protected function getStats(): array
    {
        $now = Carbon::now();

        $activeCycles = PurchaseCycle::where('status', '!=', 'upcoming')
            ->count();

        $nextUpcomingCycle = PurchaseCycle::where('status', 'upcoming')
            ->where('start_date', '>', $now)
            ->orderBy('start_date', 'asc')
            ->first();

        $nextUpcomingCycleDate = 'N/A';
        $nextUpcomingCycleDescription = 'No upcoming cycles found';

        if ($nextUpcomingCycle) {
            $nextUpcomingCycleDate = Carbon::parse($nextUpcomingCycle->start_date)->format('F j, Y'); // e.g., July 25, 2024
            $diffForHumans = Carbon::parse($nextUpcomingCycle->start_date)->diffForHumans($now);
            $nextUpcomingCycleDescription = "Starts $diffForHumans";
        }

        return [
            Stat::make('Active Purchase Cycles', $activeCycles)
                ->description('Currently active cycles')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('info'),
            Stat::make('Next Upcoming Cycle', $nextUpcomingCycleDate)
                ->description($nextUpcomingCycleDescription)
                ->descriptionIcon('heroicon-m-calendar')
                ->color('success'),
        ];
    }
}
