<?php

namespace App\Apps\BulkBuy\Controllers;

use App\Apps\BulkBuy\Models\Product;
use App\Apps\BulkBuy\Resources\ProductResource;
use App\Http\Controllers\Controller;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::get();

        return Inertia::render('shop/market', [
            'products' => ProductResource::collection($products),
        ]);
    }

    public function show(Product $product)
    {
        return Inertia::render('shop/product', [
            'product' => new ProductResource($product),
        ]);
    }
}
