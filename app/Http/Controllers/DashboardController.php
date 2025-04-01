<?php

namespace App\Http\Controllers;

use App\Apps\BulkBuy\Models\Order;
use App\Apps\BulkBuy\Models\PurchasePool;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    //
    public function index(Request $request)
    {
        $ongoingProjectsCount = Project::where('status', 'ongoing')->count();

        $totalExpenses = Order::where('user_id', $request->user()->id)->sum('total_amount'); // Adjust 'total_amount' as needed

        // Example: Fetch project budget spent (adjust based on how expenses are linked to projects)
        $projectBudgetSpent = Project::withSum('orders', 'total_amount')->get()->map(function ($project) {
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
                'name' => $pool->name,
                'target_amount' => $pool->target_amount ?? 0,
                'current_amount' => $pool->current_amount ?? 0,
            ];
        });

        // Example: Fetch watched purchase pools (assuming a relationship)
        $watchedPurchasePools = $request->user()->watchedPurchasePools()->get(); // Assuming a many-to-many relationship defined as 'purchasePools' on the User model

        // Example: Fetch frequently bought products (adjust based on your order structure)
        $frequentProducts = Order::where('user_id', $request->user()->id)
            ->with('product') // Assuming an 'product' relationship on the Order model
            ->select('product_id')
            ->groupBy('product_id')
            ->orderByDesc(\DB::raw('count(*)'))
            ->limit(5) // Example limit
            ->get()
            ->map(function ($order) {
                return [
                    'product_id' => $order->product->id,
                    'name' => $order->product->name,
                    'frequency' => $order->getAttribute('count'), // Access the count from the groupBy
                ];
            });

        // Example: Fetch regular vendors (adjust based on your order structure)
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
                    'frequency' => $order->getAttribute('count'), // Access the count from the groupBy
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
