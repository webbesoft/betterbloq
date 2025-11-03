<?php

namespace App\Models;

use Database\Factories\PurchasePoolTierFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * App\Models\PurchasePoolTier
 *
 * @property int id
 * @property string name
 * @property string description
 * @property float discount_percentage
 * @property int min_volume
 * @property int max_volume
 * @property string stripe_coupon_id
 *
 * @method static Builder|PurchasePoolTier query()
 * @method static Builder|PurchasePoolTier queryModel()
 */
class PurchasePoolTier extends Model
{
    /** @use HasFactory<PurchasePoolTierFactory> */
    use HasFactory;

    protected $table = 'purchase_pool_tiers';

    protected $fillable = [
        'name',
        'description',
        'discount_percentage',
        'min_volume',
        'max_volume',
        'stripe_coupon_id',
    ];

    protected static function booted()
    {
        static::created(function ($tier) {
            $tier->createStripeCoupon();
        });

        static::deleted(function ($tier) {
            $tier->deleteStripeCoupon();
        });
    }

    public function createStripeCoupon(): void
    {
        if (! $this->discount_percentage || $this->discount_percentage <= 0) {
            return;
        }

        $couponId = "tier_{$this->name}_{$this->id}";

        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
            $stripe->coupons->create([
                'id' => $couponId,
                'percent_off' => $this->discount_percentage,
                'duration' => 'once',
                'name' => "Tier {$this->id} Discount ({$this->discount_percentage}%)",
            ]);

            $this->update(['stripe_coupon_id' => $couponId]);
        } catch (\Exception $e) {
            Log::error('Failed to create Stripe coupon', ['tier_id' => $this->id, 'error' => $e->getMessage()]);
        }
    }

    public function deleteStripeCoupon(): void
    {
        if (! $this->stripe_coupon_id) {
            return;
        }

        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
            $stripe->coupons->retrieve($this->stripe_coupon_id)->delete();
        } catch (\Exception $e) {
            Log::warning('Failed to delete Stripe coupon', ['coupon_id' => $this->stripe_coupon_id, 'error' => $e->getMessage()]);
        }
    }
}
