<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ActiveOrdersWidget;
use App\Filament\Widgets\ActiveUsersWidget;
use App\Filament\Widgets\UpcomingPurchaseCycles;
// use App\Filament\Widgets\PlanDistributionChart;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            ActiveUsersWidget::class,
            // PlanDistributionChart::class,
            ActiveOrdersWidget::class,
            UpcomingPurchaseCycles::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return 3;
    }
}
