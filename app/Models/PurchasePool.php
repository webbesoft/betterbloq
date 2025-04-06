<?php

namespace App\Models;

use Database\Factories\PurchasePoolFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * App\Models\PurchasePool
 *
 * @property int $id
 * @property string status
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property Carbon|null $target_delivery_date
 * @property int $min_orders_for_discount
 * @property int $max_orders
 * @property int $discount_percentage
 * @property int $product_id
 * @property int $vendor_id
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, PurchasePoolTier> $purchasePoolTiers
 * @property-read int $purchase_pool_tiers_count
 *
 * @method static Builder|PurchasePool query()
 * @method static Builder|PurchasePool newModelQuery()
 * @method static Builder|PurchasePool newQuery()
 *
 * @mixin Eloquent
 */
class PurchasePool extends Model
{
    /** @use HasFactory<PurchasePoolFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'start_date',
        'end_date',
        'target_delivery_date',
        'status',
        'product_id',
        'vendor_id',
        'discount_percentage',
        'min_orders_for_discount',
        'max_orders',
        'target_amount',
        'current_amount',
    ];

    public function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'target_delivery_date' => 'date',
            'target_amount' => 'float',
            'current_amount' => 'float',
        ];
    }

    public function purchasePoolTiers(): HasMany
    {
        return $this->hasMany(PurchasePoolTier::class);
    }
}
