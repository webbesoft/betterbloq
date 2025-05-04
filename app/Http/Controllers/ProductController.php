<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\PurchasePool;
use App\Models\PurchasePoolRequest;
use App\Models\PurchasePoolTier;
use App\Models\Vendor;
use App\Services\LogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $filterCacheKey = 'shop_available_filters';
        $cacheDuration = 3600;

        $availableFiltersData = Cache::remember($filterCacheKey, $cacheDuration, function () {
            return [
                'categories' => Category::select('id', 'name')->get(),
                'vendors' => Vendor::select('id', 'name')->get(),
            ];
        });

        $products = QueryBuilder::for(Product::class)
            ->allowedFilters([
                AllowedFilter::exact('vendor', 'vendor_id'),
                AllowedFilter::exact('category', 'category_id'),

                AllowedFilter::partial('name'),
                // AllowedFilter::partial('vendor.name'),

                // Price Range Filter using the scope defined in the Product model
                // Expects URL parameters like ?filter[price_between]=0,500
                AllowedFilter::scope('price_between'),
                AllowedFilter::scope('has_open_purchase_pool'),

                // Add other filters as needed (e.g., AllowedFilter::partial('name'))
            ])
            ->defaultSort('-created_at')
            ->allowedSorts([
                'created_at',
                'price',
                'name',
            ])
            ->with(['category', 'vendor', 'images'])
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('shop/market', [
            'filters' => $request->all(),
            'products' => ProductResource::collection($products),
            'availableFilters' => $availableFiltersData,
        ]);
    }

    public function show(Request $request, Product $product)
    {
        $product->load([
            'vendor',
            'images',
            'category'
        ])
            ->loadAvg('ratings', 'rating')
            ->loadCount('ratings');

        $userRating = null;
        if ($request->user()) {
            $userRating = $product->ratings()
                                ->where('user_id', $request->user()->id)
                                ->first();
        }

        $canRate = $request->user()
               && $request->user()->hasVerifiedEmail()
               && is_null($userRating);

        $now = Carbon::now();
        $activePool = PurchasePool::with(['purchasePoolTiers' => function ($query) {
            $query->orderBy('min_volume', 'asc');
        }])
            ->where('product_id', $product->id)
            ->where('status', PurchasePool::STATUS_ACTIVE)
            ->where(function ($query) use ($now) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $now);
            })
            ->first();

        $poolData = null;
        if ($activePool) {
            $currentTier = $this->determineCurrentTier($activePool, $activePool->current_volume);

            $poolData = [
                'id' => $activePool->id,
                'status' => $activePool->status,
                'end_date' => $activePool->end_date?->toIso8601String(),
                'target_delivery_date' => $activePool->target_delivery_date?->toIso8601String(),
                'min_orders_for_discount' => $activePool->min_orders_for_discount,
                'max_orders' => $activePool->max_orders,
                'current_volume' => $activePool->current_volume,
                'target_volume' => $activePool->target_volume ?? 1000,
                'tiers' => $activePool->purchasePoolTiers->map(fn ($tier) => [
                    'id' => $tier->id,
                    'name' => $tier->name,
                    'description' => $tier->description,
                    'discount_percentage' => $tier->discount_percentage,
                    'min_volume' => $tier->min_volume,
                    'max_volume' => $tier->max_volume,
                ])->all(),
                'current_tier' => $currentTier ? [
                    'id' => $currentTier->id,
                    'name' => $currentTier->name,
                    'discount_percentage' => $currentTier->discount_percentage,
                    'min_volume' => $currentTier->min_volume,
                ] : null,
            ];
        }

        $hasPurchasePoolRequest = false;
        // try {
        // if ($request->user()) {
        //    $hasPurchasePoolRequest = (PurchasePoolRequest::whereIsPending()
        //        ->where('product_id', $product->id)
        //        ->where('user_id', $request->user()->id)
        //        ->count() > 0);
        // }
        // } catch (\Exception $e) {
        // throw new Exception($e);
        // }

        return Inertia::render('shop/product', [
            'product' => new ProductResource($product),
            'hasPurchasePoolRequest' => $hasPurchasePoolRequest,
            'activePurchasePool' => $poolData,
            'hasOrder' => $request->user() ? Order::where('user_id', $request->user()->id)
                ->where('product_id', $product->id)
                ->where('purchase_pool_id', $poolData['id'] ?? null)
                ->exists() : false,
            'canRate' => $canRate,
            'userRating' => $userRating,
        ]);
    }

    private function determineCurrentTier(PurchasePool $pool, int $currentVolume): ?PurchasePoolTier
    {
        $applicableTier = null;
        foreach ($pool->purchasePoolTiers as $tier) {
            if ($currentVolume >= $tier->min_volume) {
                if ($tier->max_volume === null || $currentVolume <= $tier->max_volume) {
                    $applicableTier = $tier;
                } elseif ($tier->max_volume !== null && $currentVolume > $tier->max_volume) {
                    continue;
                }
            } else {
                break;
            }
        }

        return $applicableTier;
    }
}
