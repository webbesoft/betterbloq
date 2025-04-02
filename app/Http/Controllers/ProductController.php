<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Vendor;
use App\Http\Resources\ProductResource;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $availableFiltersData = [
            'categories' => Category::select('id', 'name')->get(),
            'vendors' => Vendor::select('id', 'name')->get()
        ];

        $products = QueryBuilder::for(Product::class)
            ->allowedFilters([
                // AllowedFilter::scope('category'),
                // AllowedFilter::scope('vendor'),
                AllowedFilter::exact('vendor', 'vendor_id'),
                AllowedFilter::exact('category', 'category_id'),

                AllowedFilter::partial('name'),
                // AllowedFilter::partial('vendor.name'),
                
                // Price Range Filter using the scope defined in the Product model
                // Expects URL parameters like ?filter[price_between]=0,500
                AllowedFilter::scope('price_between'),
                AllowedFilter::scope('has_open_purchase_pool')

                // Add other filters as needed (e.g., AllowedFilter::partial('name'))
            ])
            ->defaultSort('-created_at')
            ->allowedSorts([
                'created_at', 
                'price',      
                'name',       
            ])
            ->with(['category', 'vendor'])
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('shop/market', [
            'filters' => $request->all(),
            'products' => ProductResource::collection($products),
            'availableFilters' => $availableFiltersData
        ]);
    }

    public function show(Product $product)
    {
        return Inertia::render('shop/product', [
            'product' => new ProductResource($product),
        ]);
    }
}
