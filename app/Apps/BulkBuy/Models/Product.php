<?php

namespace App\Apps\BulkBuy\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;

/**
 * App\Apps\BulkBuy\Models\Product
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property float $price
 * @property int $category_id
 * @property int $vendor_id
 * @property string $image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 *
 * @mixin Eloquent
 */
class Product extends Model
{
    /** @use HasFactory<\Database\Factories\Apps\BulkBuy\ProductFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'stripe_price_id',
        'stripe_product_id',
        'description',
        'price',
        'category_id',
        'vendor_id',
        'image',
        'price',
        'unit',
        'amount',
    ];

    protected $table = 'products';

    protected static function boot()
    {
        parent::boot();

        static::created(function ($product) {
            try {
                $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
                $stripeProduct = $stripe->products->create([
                    'name' => $product->name.' '.$product->vendor->name,
                    'description' => $product->description ?? null,
                ]);

                // Create Stripe Price
                $stripePrice = $stripe->prices->create([
                    'product' => $stripeProduct->id,
                    'unit_amount' => $product->price * 100,
                    'currency' => 'usd',
                ]);

                // Update the local Product record with Stripe IDs
                $product->update([
                    'stripe_product_id' => $stripeProduct->id,
                    'stripe_price_id' => $stripePrice->id,
                ]);
            } catch (\Stripe\Exception\ApiErrorException $e) {
                // Handle Stripe API errors
                Log::error('Error creating Stripe product: '.$e->getMessage());
                // Optionally, you might want to handle this differently, like setting a flag on the product
            }
        });

        static::updated(function ($product) {
            // update name and description in Stripe
        });
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
