<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlanResource;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $cacheKey = 'shop.plans.all';
        $cacheDuration = 60 * 60 * 60;

        $allPlans = Cache::remember($cacheKey, $cacheDuration, function () {
            return Plan::with([
                'planFeatures',
                'planLimits',
                'planLimits.plan',
                'planFeatures.plan',
            ])
                ->orderBy('order')
                ->get();
        });

        $groupedPlans = $allPlans->groupBy('interval');

        $monthlyPlans = PlanResource::collection($groupedPlans->get('monthly', collect()));
        $yearlyPlans = PlanResource::collection($groupedPlans->get('yearly', collect()));

        return Inertia::render('shop/plans', [
            'monthlyPlans' => $monthlyPlans,
            'yearlyPlans' => $yearlyPlans,
        ]);
    }
}
