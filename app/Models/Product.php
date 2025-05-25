<?php

namespace App\Models;

use App\Managers\ProductManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stripe\Stripe;

/**
 * App\Models\Product
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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Category> $category
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 * @method static Builder|Product filterByName(string $name)
 * @method static Builder|Product filterByPrice(int $price)
 * @method static Builder|Product whereId($value)
 * @method static Builder|Product whereName($value)
 * @method static Builder|Product wherePrice($value)
 * @method static Builder|Product whereVendor($value)
 * @method static Builder|Product whereCategory($value)
 *
 * @mixin Eloquent
 */
class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'delivery_time',
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
        'storable',
        'storage_unit_of_measure',
        'default_length',
        'default_width',
        'default_height',
        'is_stackable',
        'max_stack_height_units',
        'storage_conditions_required',
        'storage_handling_notes',
    ];

    public function casts(): array
    {
        return [
            'price' => 'float',
            'storage_conditions_required' => 'array', // or 'json'
            'storable' => 'boolean',
            'is_stackable' => 'boolean',
            'default_length' => 'float:2',
            'default_width' => 'float:2',
            'default_height' => 'float:2',
        ];
    }

    protected $table = 'products';

    protected static function boot()
    {
        parent::boot();

        static::created(function ($product) {
            (new ProductManager)->onCreated($product);
        });

        static::updated(function ($product) {
            // update name and description in Stripe
        });
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('order');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(ProductRating::class);
    }

    public function getAverageRatingAttribute(): ?float
    {
        return $this->ratings()->avg('rating');
    }

    public function getRatingsCountAttribute(): int
    {
        return $this->ratings()->count();
    }

    public function scopeFilterByName(Builder $query, string $name)
    {
        return $query->where('name', 'like', "%$name%");
    }

    public function scopeVendor(Builder $query, int $vendor_id)
    {
        return $query->where('vendor_id', $vendor_id);
    }

    public function scopeCategory(Builder $query, int $category_id)
    {
        return $query->where('category_id', $category_id);
    }

    public function scopePriceBetween(Builder $query, string $value): Builder
    {
        if ($value) {
            [$min, $max] = explode(':', $value);
            $query->whereBetween('price', [floatval($min), floatval($max)]);
        }

        return $query;
    }

    public function scopeHasOpenPurchasePool(Builder $query): Builder
    {
        return $query->whereHas('purchasePools', function (Builder $poolQuery) {
            $poolQuery->where('status', 'open');
        });
    }
}
