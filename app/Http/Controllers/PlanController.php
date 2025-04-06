<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlanResource;
use App\Models\Plan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $plans = Plan::with(['planFeatures', 'planLimits'])->get();

        return Inertia::render('shop/plans', [
            'plans' => PlanResource::collection($plans),
        ]);
    }
}
