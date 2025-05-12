<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorageOrderLineItem extends Model
{
    /** @use HasFactory<\Database\Factories\StorageOrderLineItemFactory> */
    use HasFactory;

    protected $fillable = [
        'storage_order_id',
        'product_id',
        'quantity_stored',
        'manual_dimensions_length',
        'manual_dimensions_width',
        'manual_dimensions_height',
        'calculated_space_for_item',
        'entry_date',
        'retrieval_date',
    ];

    public function storageOrder(): BelongsTo
    {
        return $this->belongsTo(StorageOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
