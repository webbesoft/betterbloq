<?php

namespace App\Http\Controllers;

use App\Http\Resources\PurchasePoolResource;
use App\Models\PurchasePool;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\QueryBuilder;

class PurchasePoolController extends Controller
{
    //
    public function index(Request $request): Response
    {
        $purchasePoolQuery = QueryBuilder::for(PurchasePool::class)
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('shop/purchase-pools/index', [
            'purchasePools' => PurchasePoolResource::collection($purchasePoolQuery),
        ]);
    }
}
