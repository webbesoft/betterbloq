<?php

namespace App\Models;

use Database\Factories\PurchasePoolFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
 * @property-read Product $product
 * @property-read Vendor $vendor
 * @property-read Collection<int, Order> $orders
 * @property-read int $orders_count
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

    const STATUS_ACCUMULATING = 'accumulating';

    const STATUS_PENDING = 'pending';

    const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'name',
        'description',
        'target_delivery_date',
        'status',
        'product_id',
        'vendor_id',
        'discount_percentage',
        'min_orders_for_discount',
        'max_orders',
        'target_volume',
        'current_volume',
        'purchase_cycle_id',
        'cycle_status',
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseCycle(): BelongsTo
    {
        return $this->belongsTo(PurchaseCycle::class);
    }

    public function scopeWithinDeliveryRange(Builder $query, string $expectedDeliveryDate): Builder
    {
        $expectedDate = Carbon::parse($expectedDeliveryDate);
        $startDate = $expectedDate->copy()->subDays(3)->toDateString();
        $endDate = $expectedDate->copy()->addDays(3)->toDateString();

        return $query->whereBetween('target_delivery_date', [$startDate, $endDate]);
    }

    /**
     * Helper method to get the current aggregated volume for this pool's
     * product within its specific purchase cycle from the 'cycle_product_volumes' table.
     */
    public function getCurrentVolumeInCycle(): float
    {
        $volumeRecord = CycleProductVolume::where('purchase_cycle_id', $this->purchase_cycle_id)
            ->where('product_id', $this->product_id)
            ->first();

        return $volumeRecord ? (float) $volumeRecord->total_aggregated_quantity : 0.0;
    }

    /**
     * Get the applicable discount tier based on the dynamically fetched volume
     * for this pool's product and cycle, using its directly associated tiers.
     *
     * @return PurchasePoolTier|null The applicable tier, or null if none applies.
     */
    public function getApplicableTier(): ?PurchasePoolTier
    {
        $currentVolume = $this->getCurrentVolumeInCycle();

        return $this->purchasePoolTiers()
            ->where('min_volume', '<=', $currentVolume)
            ->where(function ($query) use ($currentVolume) {
                $query->whereNull('max_volume')
                    ->orWhere('max_volume', '>=', $currentVolume);
            })
            ->orderBy('min_volume', 'desc')
            ->first();
    }

    /**
     * Convenience method to directly get the discount percentage.
     *
     * @return float The discount percentage (e.g., 10.5 for 10.5%), or 0 if no tier applies.
     */
    public function getApplicableDiscountPercentage(): float
    {
        $tier = $this->getApplicableTier();

        return $tier ? (float) $tier->discount_percentage : 0.0;
    }

    public function getStripeCouponIdAttribute(): ?string
    {
        return $this->getApplicableTier()?->stripe_coupon_id;
    }

    /**
     * Calculate the target delivery date based on end date, product, and vendor.
     *
     * @param  string|Carbon  $endDateInput  The end date of the purchase pool.
     * @param  int|Product  $productOrProductId  The Product model instance or its ID.
     * @return Carbon|null The calculated target delivery date as a Carbon instance, or null on failure.
     */
    public static function calculateTargetDeliveryDate($endDateInput, $productOrProductId): ?Carbon
    {
        try {
            $endDate = Carbon::parse($endDateInput);
        } catch (\Exception $e) {
            return null;
        }

        $product = $productOrProductId instanceof Product ? $productOrProductId : Product::find($productOrProductId);

        if (! $product) {
            return null;
        }

        $vendor = $product->vendor;

        if (! $vendor) {
            return null;
        }

        $prepTimeDays = $vendor->prep_time ?? 0;
        $deliveryTimeDays = $product->delivery_time ?? 0;

        // Calculate the target delivery date
        return $endDate->addDays((int) $prepTimeDays + (int) $deliveryTimeDays);
    }
}
