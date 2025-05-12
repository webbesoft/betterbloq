<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorageTier extends Model
{
    /** @use HasFactory<\Database\Factories\StorageTierFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'warehouse_id',
        'min_space_units',
        'max_space_units',
        'price_per_space_unit',
        'billing_period',
        'min_duration',
        'duration_unit',
        'conditions',
        'notes',
    ];

    public function casts(): array
    {
        return [
            'conditions' => 'array',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public static function getBillingPeriodOptions(): array
    {
        return [
            'day' => 'Day',
            'week' => 'Week',
            'month' => 'Month',
            'quarter' => 'Quarter',
            'year' => 'Year',
        ];
    }

    public static function getDurationUnitOptions(): array
    {
        return [
            'day' => 'Day',
            'week' => 'Week',
            'month' => 'Month',
        ];
    }
}
