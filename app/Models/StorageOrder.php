<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StorageOrder extends Model
{
    /** @use HasFactory<\Database\Factories\StorageOrderFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'warehouse_id',
        'order_id',
        'payment_intent_id',
        'requested_storage_start_date',
        'requested_storage_duration_estimate',
        'preliminary_storage_cost_estimate',
        'actual_storage_start_date',
        'actual_storage_end_date',
        'manually_entered_total_space_units',
        'calculated_space_unit_type',
        'applied_storage_tier_id',
        'actual_rate_per_unit_per_period',
        'billing_period_for_actuals',
        'next_billing_date',
        'total_actual_storage_cost_to_date',
        'status',
        'notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function appliedStorageTier(): BelongsTo
    {
        return $this->belongsTo(StorageTier::class);
    }

    public function storageOrderLineItems(): HasMany
    {
        return $this->hasMany(StorageOrderLineItem::class);
    }

    public static function getStatusOptions(): array
    {
        return ['pending_arrival', 'stored', 'partially_retrieved', 'retrieved', 'cancelled'];
    }
}
