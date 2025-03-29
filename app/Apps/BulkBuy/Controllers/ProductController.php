<?php

namespace App\Apps\BulkBuy\Controllers;

use App\Apps\BulkBuy\Models\Product;
use App\Apps\BulkBuy\Resources\ProductResource;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProductController extends Controller
{
    public function index()
    {
        /** @var Product|QueryBuilder $productQuery */
        $productQuery = QueryBuilder::for(Product::class);

        $products = $productQuery->paginate(10);

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
