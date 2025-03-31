<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $plans = Plan::get();

        return Inertia::render('shop/plans', [
            'plans' => $plans,
        ]);
    }
}
