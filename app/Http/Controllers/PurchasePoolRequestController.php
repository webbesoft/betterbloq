<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchasePoolRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class PurchasePoolRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $query = PurchasePoolRequest::with(['product', 'vendor', 'user']);

        $query->when($request->input('search'), function (Builder $query, $search) {
            $query->where(function (Builder $query) use ($search) {
                $query->where('id', 'like', "%{$search}%")
                    ->orWhere('quantity', 'like', "%{$search}%");

                $query->orWhereHas('product', function (Builder $query) use ($search) {
                    $query->where('name', 'like', "%{$search}%");
                });

                $query->orWhereHas('vendor', function (Builder $query) use ($search) {
                    $query->where('name', 'like', "%{$search}%");
                });

                $query->orWhereHas('user', function (Builder $query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            });
        });

        $query->when($request->input('sort'), function (Builder $query, $sort) {
            $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
            $column = ltrim($sort, '-');

            $sortableColumns = ['id', 'quantity', 'target_date'];
            if (in_array($column, $sortableColumns)) {
                $query->orderBy($column, $direction);
            }

        }, function (Builder $query) {
            $query->orderBy('created_at', 'desc');
        });

        $purchasePoolRequests = $query->paginate(15)->withQueryString();

        return Inertia::render('shop/purchase-pool-requests/index', [
            'purchasePoolRequests' => $purchasePoolRequests,
            'filters' => $request->only(['search', 'sort']),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @return \Inertia\Response
     */
    public function show(PurchasePoolRequest $purchasePoolRequest)
    {
        $purchasePoolRequest->load(['product', 'vendor', 'user']);

        return Inertia::render('shop/purchase-pool-requests/show', [
            'purchasePoolRequest' => $purchasePoolRequest,
        ]);
    }

    //
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer',
            'expected_delivery_date' => 'required|date',
        ]);

        $product = Product::whereId(data_get($validated, 'product_id'))->first();

        PurchasePoolRequest::create([
            'user_id' => $request->user()->id,
            'product_id' => $validated['product_id'],
            'quantity' => $validated['quantity'],
            'target_date' => $validated['expected_delivery_date'],
        ]);

        Session::flash('message', ['success' => 'Your purchase pool request has been submitted.']);

        return redirect()->back();
    }
}
