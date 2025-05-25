<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CycleProductVolume extends Model
{
    /** @use HasFactory<\Database\Factories\CycleProductVolumeFactory> */
    use HasFactory;

    protected $fillable = [
        'purchase_cycle_id',
        'product_id',
        'total_aggregated_quantity',
        'achieved_discount_percentage'
    ];

    public function purchaseCycle(): BelongsTo
    {
        return $this->belongsTo(PurchaseCycle::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
