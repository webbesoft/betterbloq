<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Project;
use App\Models\PurchasePool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    //
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $cacheKey = 'dashboard_data_'.$userId;
        $cacheDuration = 300; // 5 minutes (adjust as needed)

        $dashboardData = Cache::remember($cacheKey, $cacheDuration, function () use ($request, $userId) {
            $ongoingProjectsCount = Project::where('status', 'ongoing')->count();

            $totalExpenses = Order::where('user_id', $userId)
                ->where('status', 'completed')
                ->sum('total_amount');

            $projectBudgetSpent = Project::withSum('orders', 'id')->get()->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'budget' => $project->budget,
                    'total_expenses' => $project->orders_sum_total_amount ?? 0,
                ];
            });

            $purchasePoolCompletion = PurchasePool::all()->map(function ($pool) {
                return [
                    'id' => $pool->id,
                    'target_amount' => $pool->target_amount ?? 0,
                    'current_amount' => $pool->current_amount ?? 0,
                ];
            });

            $watchedPurchasePools = $request->user()->watchedPurchasePools()->get();

            $activeOrderPoolIds = Order::where('user_id', $userId)
                ->where('status', 'completed')
                ->whereNotNull('purchase_pool_id')
                ->distinct()
                ->pluck('purchase_pool_id');

            $activePoolsProgress = [];
            if ($activeOrderPoolIds->isNotEmpty()) {
                $activePoolsProgress = PurchasePool::whereIn('id', $activeOrderPoolIds)
                    ->with('product:id,name')
                    ->get()
                    ->map(function ($pool) {
                        return [
                            'id' => $pool->id,
                            'name' => $pool->name,
                            'product_id' => $pool->product->id ?? null,
                            'current_volume' => $pool->current_volume ?? 0,
                            'target_volume' => $pool->target_volume ?? 0,
                        ];
                    })->all();
            }

            $frequentProducts = Order::where('user_id', $request->user()->id)
                ->with('product')
                ->select('product_id', DB::raw('count(*) as frequency'))
                ->groupBy('product_id')
                ->orderByDesc(DB::raw('count(*)'))
                ->limit(5)
                ->get()
                ->map(function ($order) {
                    return [
                        'product_id' => $order->product->id,
                        'name' => $order->product->name,
                        'frequency' => $order->getAttribute('frequency'),
                    ];
                });

            $regularVendors = Order::where('user_id', $request->user()->id)
                ->with('vendor')
                ->select('vendor_id', DB::raw('count(*) as frequency'))
                ->groupBy('vendor_id')
                ->orderByDesc(DB::raw('count(*)'))
                ->limit(5)
                ->get()
                ->map(function ($order) {
                    return [
                        'vendor_id' => $order->vendor->id,
                        'name' => $order->vendor->name,
                        'frequency' => $order->getAttribute('frequency'),
                    ];
                });

            return [
                'activeOrdersCount' => Order::where('user_id', $request->user()->id)->where('status', 'active')->count(),
                'completedOrdersCount' => Order::where('user_id', $request->user()->id)->where('status', 'completed')->count(),
                'activePoolsProgress' => $activePoolsProgress,
                'ongoingProjectsCount' => $ongoingProjectsCount,
                'totalExpenses' => $totalExpenses,
                'projectBudgetSpent' => $projectBudgetSpent,
                'purchasePoolCompletion' => $purchasePoolCompletion,
                'watchedPurchasePools' => $watchedPurchasePools,
                'frequentProducts' => $frequentProducts,
                'regularVendors' => $regularVendors,
                'hasCompletedGuide' => $request->user()->has_completed_guide
            ];
        });

        return Inertia::render('shop/dashboard', $dashboardData);
    }
}
