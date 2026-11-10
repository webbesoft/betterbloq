<?php

namespace App\Models;

use Database\Factories\OrderLineItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderLineItem extends Model
{
    /** @use HasFactory<OrderLineItemFactory> */
    use HasFactory;

    protected $fillable = [
        'original_unit_amount',
        'charged_amount',
        'order_id',
        'product_id',
        'purchase_pool_id',
        'price_per_unit',
        'total_price',
        'quantity',
        'final_line_price',
        'applied_discount_percentage',
        'description',
    ];

    public function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
            'price_per_unit' => 'decimal:2',
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
