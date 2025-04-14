<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

/**
 * App\Models\Plan
 *
 * @property int id
 * @property string name
 * @property string stripe_plan
 * @property string description
 * @property float price
 * @property int order
 * @property int is_popular
 * @property int interval
 * @property-read Collection<int, PlanFeature> $planFeatures
 * @property-read int|null $plan_features_count
 * @property-read Collection<int, PlanFeature> $planLimits
 * @property-read int|null $plan_limits_count
 *
 * @method static Builder|Plan newQuery()
 * @method static Builder|Plan query()
 * @method static Builder|Plan newModelQuery()
 */
class Plan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'stripe_plan',
        'price',
        'description',
        'order',
        'is_popular',
        'interval',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::created(function ($model) {
            Cache::forget('shop.plans.all');
        });

        static::updated(function ($model) {
            Cache::forget('shop.plans.all');
        });
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function planFeatures(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    public function planLimits(): HasMany
    {
        return $this->hasMany(PlanLimit::class);
    }
}
