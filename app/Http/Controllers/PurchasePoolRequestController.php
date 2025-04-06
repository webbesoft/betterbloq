<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchasePoolRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PurchasePoolRequestController extends Controller
{
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
