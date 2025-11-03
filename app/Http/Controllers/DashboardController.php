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
        $cacheDuration = 300;

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

            $activePoolsProgress = [];

            $frequentProducts = DB::table('order_line_items')
                ->join('orders', 'order_line_items.order_id', '=', 'orders.id')
                ->join('products', 'order_line_items.product_id', '=', 'products.id')
                ->where('orders.user_id', $request->user()->id)
                ->select('order_line_items.product_id', 'products.name', DB::raw('count(*) as frequency'))
                ->groupBy('order_line_items.product_id', 'products.name')
                ->orderByDesc('frequency')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'name' => $item->name,
                        'frequency' => $item->frequency,
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
                'activeOrdersCount' => Order::where('user_id', $request->user()->id)->whereIn('status', Order::ACTIVE_STATUSES)->count(),
                'completedOrdersCount' => Order::where('user_id', $request->user()->id)->where('status', 'completed')->count(),
                'activePoolsProgress' => $activePoolsProgress,
                'ongoingProjectsCount' => $ongoingProjectsCount,
                'totalExpenses' => $totalExpenses,
                'projectBudgetSpent' => $projectBudgetSpent,
                'purchasePoolCompletion' => $purchasePoolCompletion,
                'watchedPurchasePools' => $watchedPurchasePools,
                'frequentProducts' => $frequentProducts,
                'regularVendors' => $regularVendors,
                'hasCompletedGuide' => $request->user()->has_completed_guide,
            ];
        });

        return Inertia::render('shop/dashboard', $dashboardData);
    }
}
