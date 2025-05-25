<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderLineItem extends Model
{
    /** @use HasFactory<\Database\Factories\OrderLineItemFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'purchase_pool_id',
        'price_per_unit',
        'total_price',
        'quantity',
    ];

    public function casts(): array
    {
        return [
            'total_price' => 'float',
            'price_per_unit' => 'float'
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function purchasePool(): BelongsTo
    {
        return $this->belongsTo(PurchasePool::class);
    }
}
