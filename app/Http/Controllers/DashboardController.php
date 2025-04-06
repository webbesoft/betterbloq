<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Project;
use App\Models\PurchasePool;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    //
    public function index(Request $request)
    {
        $ongoingProjectsCount = Project::where('status', 'ongoing')->count();

        //        $totalExpenses = Order::where('user_id', $request->user()->id)->sum('total_amount'); // Adjust 'total_amount' as needed
        $totalExpenses = 10;
        // Example: Fetch project budget spent (adjust based on how expenses are linked to projects)
        $projectBudgetSpent = Project::withSum('orders', 'id')->get()->map(function ($project) {
            return [
                'id' => $project->id,
                'name' => $project->name,
                'budget' => $project->budget,
                'total_expenses' => $project->orders_sum_total_amount ?? 0, // Adjust relation name
            ];
        });

        // Example: Fetch purchase pool completion (adjust based on your PurchasePool model)
        $purchasePoolCompletion = PurchasePool::all()->map(function ($pool) {
            return [
                'id' => $pool->id,
                'target_amount' => $pool->target_amount ?? 0,
                'current_amount' => $pool->current_amount ?? 0,
            ];
        });

        // Example: Fetch watched purchase pools (assuming a relationship)
        $watchedPurchasePools = $request->user()->watchedPurchasePools()->get(); // Assuming a many-to-many relationship defined as 'purchasePools' on the User model

        $frequentProducts = Order::where('user_id', $request->user()->id)
            ->with('product')
            ->select('product_id')
            ->groupBy('product_id')
            ->orderByDesc(\DB::raw('count(*)'))
            ->limit(5) // Example limit
            ->get()
            ->map(function ($order) {
                return [
                    'product_id' => $order->product->id,
                    'name' => $order->product->name,
//                    'frequency' => $order->getAttribute('count'),
                ];
            });

        $regularVendors = Order::where('user_id', $request->user()->id)
            ->with('vendor') // Assuming a 'vendor' relationship on the Order model
            ->select('vendor_id')
            ->groupBy('vendor_id')
            ->orderByDesc(\DB::raw('count(*)'))
            ->limit(5) // Example limit
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
