<?php

namespace App\Apps\BulkBuy\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Apps\BulkBuy\Models\PurchasePool
 *
 * @property int $id
 * @property string status
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property Carbon|null $target_delivery_date
 * @property int $min_orders_for_discount
 * @property int $max_orders
 * @property int $discount_percentage
 * @property int $product_id
 * @property int $vendor_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasePool newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasePool newQuery()
 *
 * @mixin Eloquent
 */
class PurchasePool extends Model
{
    /** @use HasFactory<\Database\Factories\Apps\BulkBuy\PurchasePoolFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'start_date',
        'end_date',
        'target_delivery_date',
        'status',
        'product_id',
        'vendor_id',
        'discount_percentage',
        'min_orders_for_discount',
        'max_orders',
    ];
}
