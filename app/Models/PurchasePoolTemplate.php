<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchasePoolTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'min_orders_for_discount',
        'max_orders',
        'status',
        'target_volume',
        'vendor_id',
        'product_id',
    ];

    protected $casts = [

    ];

    public function tiers(): HasMany
    {
        return $this->hasMany(PurchasePoolTemplateTier::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    //
}
