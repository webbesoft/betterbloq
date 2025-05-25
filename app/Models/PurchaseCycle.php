<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseCycle extends Model
{
    /** @use HasFactory<\Database\Factories\PurchaseCycleFactory> */
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

    public function purchasePools(): HasMany
    {
        return $this->hasMany(PurchasePool::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
