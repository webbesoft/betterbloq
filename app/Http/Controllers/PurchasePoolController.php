<?php

namespace App\Http\Controllers;

use App\Models\PurchasePool;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PurchasePoolController extends Controller
{
    //
    public function index(Request $request): \Inertia\Response
    {
        $purchasePoolQuery = PurchasePool::query();

        return Inertia::render('shop/purchase-pools/index', [
            'purchasePools' => $purchasePoolQuery->paginate(10)->withQueryString(),
        ]);
    }
}
