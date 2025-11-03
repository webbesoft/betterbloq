<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ActiveUsersWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $activeUsersCount = User::all()->filter(function ($user) {
            return $user->subscriptions()->active()->exists();
        })->count();

        return [
            Stat::make('Active Users', $activeUsersCount)
                ->description('Users with an active plan')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
        ];
    }
}
