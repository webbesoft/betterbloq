<?php

namespace App\Models;

use Database\Factories\Apps\BulkBuy\OrderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\Order
 *
 * @property int id
 * @property string name
 * @property string email
 * @property string phone
 * @property string address
 * @property int product_id
 * @property int user_id
 * @property int purchase_pool_id
 * @property string status
 * @property Carbon|null deleted_at
 *
 * @method static Builder|Order newQuery()
 * @method static Builder|Order newModelQuery()
 *
 * @mixin \Eloquent
 */
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'email',
        'phone',
        'address',
        'status',
        'quantity',
        'purchase_pool_id',
        'product_id',
        'user_id',
        'stripe_session_id',
        'project_id',
        'vendor_id',
    ];

    public function casts(): array
    {
        return [
            // Casting address to 'array' or 'object' automatically handles
            // JSON encoding/decoding when saving/retrieving the model.
            // Choose 'array' (associative array) or 'object' (stdClass object).
            'address' => 'object',
            'billing_address' => 'object',
            'shipping_address' => 'object',
            'quantity' => 'integer',
        ];
    }

    public function name(): string
    {
        return '#'.$this->id;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function purchasePool(): BelongsTo
    {
        return $this->belongsTo(PurchasePool::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
