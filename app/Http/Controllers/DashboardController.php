<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Project;
use App\Models\PurchasePool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    //
    public function index(Request $request)
    {
        $ongoingProjectsCount = Project::where('status', 'ongoing')->count();

        $totalExpenses = 10;

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

        $frequentProducts = Order::where('user_id', $request->user()->id)
            ->with('product')
            ->select('product_id')
            ->groupBy('product_id')
            ->orderByDesc(DB::raw('count(*)'))
            ->limit(5)
            ->get()
            ->map(function ($order) {
                return [
                    'product_id' => $order->product->id,
                    'name' => $order->product->name,
                    //                    'frequency' => $order->getAttribute('count'),
                ];
            });

        $regularVendors = Order::where('user_id', $request->user()->id)
            ->with('vendor')
            ->select('vendor_id')
            ->groupBy('vendor_id')
            ->orderByDesc(DB::raw('count(*)'))
            ->limit(5)
            ->get()
            ->map(function ($order) {
                return [
                    'vendor_id' => $order->vendor->id,
                    'name' => $order->vendor->name,
                    //                    'frequency' => $order->getAttribute('count'),
                ];
            });

        return Inertia::render('shop/dashboard', [
            'ongoingProjectsCount' => $ongoingProjectsCount,
            'totalExpenses' => $totalExpenses,
            'projectBudgetSpent' => $projectBudgetSpent,
            'purchasePoolCompletion' => $purchasePoolCompletion,
            'watchedPurchasePools' => $watchedPurchasePools,
            'frequentProducts' => $frequentProducts,
            'regularVendors' => $regularVendors,
        ]);
    }
}
