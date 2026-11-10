<?php

namespace App\Models;

use Database\Factories\PurchaseCycleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property Carbon|null $finalization_lease_until
 */
class PurchaseCycle extends Model
{
    /** @use HasFactory<PurchaseCycleFactory> */
    use HasFactory;

    const STATUS_UPCOMING = 'upcoming';

    const STATUS_ACTIVE = 'active';

    const STATUS_CALCULATING = 'calculating_discounts';

    const STATUS_PROCESSING = 'processing_payments';

    const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'status',
    ];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date', 'finalization_lease_until' => 'datetime'];
    }

    /** @return HasMany<PurchasePool, $this> */
    public function purchasePools(): HasMany
    {
        return $this->hasMany(PurchasePool::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
