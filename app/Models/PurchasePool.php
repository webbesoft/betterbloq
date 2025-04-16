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
 * @property float current_volume
 * @property float target_volume
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
 * @method static Builder|PurchasePool whereId($value)
 * @method static Builder|PurchasePool whereStatus($value)
 * @method static Builder|PurchasePool whereStartDate($value)
 * @method static Builder|PurchasePool whereEndDate($value)
 * @method static Builder|PurchasePool whereTargetDeliveryDate($value)
 * @method static Builder|PurchasePool whereMinOrdersForDiscount($value)
 * @method static Builder|PurchasePool whereMaxOrders($value)
 * @method static Builder|PurchasePool whereDiscountPercentage($value)
 * @method static Builder|PurchasePool whereCurrentVolume($value)
 * @method static Builder|PurchasePool whereTargetVolume($value)
 * @method static Builder|PurchasePool whereProductId($value)
 * @method static Builder|PurchasePool whereVendorId($value)
 * @method static Builder|PurchasePool whereDeletedAt($value)
 * @method static Builder|PurchasePool whereCreatedAt($value)
 * @method static Builder|PurchasePool whereUpdatedAt($value)
 * @method static Builder|PurchasePool withinDeliveryRange(string $expectedDeliveryDate)
 * @method static Builder|PurchasePool active()
 * @method static Builder|PurchasePool pending()
 * @method static Builder|PurchasePool closed()
 *
 * @mixin Eloquent
 */
class PurchasePool extends Model
{
    /** @use HasFactory<PurchasePoolFactory> */
    use HasFactory, SoftDeletes;

    const STATUS_ACTIVE = 'active';

    const STATUS_PENDING = 'pending';

    const STATUS_CLOSED = 'closed';

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
        'target_volume',
        'current_volume',
    ];

    public function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'target_delivery_date' => 'date',
            'target_volume' => 'float',
            'current_volume' => 'float',
        ];
    }

    public function purchasePoolTiers(): HasMany
    {
        return $this->hasMany(PurchasePoolTier::class);
    }

    public function scopeWithinDeliveryRange(Builder $query, string $expectedDeliveryDate): Builder
    {
        $expectedDate = Carbon::parse($expectedDeliveryDate);
        $startDate = $expectedDate->copy()->subDays(3)->toDateString();
        $endDate = $expectedDate->copy()->addDays(3)->toDateString();

        return $query->whereBetween('target_delivery_date', [$startDate, $endDate]);
    }
}
