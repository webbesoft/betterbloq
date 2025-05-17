<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

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
 * @property float total_amount
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
        'payment_intent_id',
        'initial_amount',
        'final_amount',
        'project_id',
        'vendor_id',
        'total_amount',
    ];

    public function casts(): array
    {
        return [
            'address' => 'object',
            'billing_address' => 'object',
            'shipping_address' => 'object',
            'quantity' => 'integer',
        ];
    }

    public static function boot(): void
    {
        parent::boot();

        static::created(function ($model) {
            $userId = $model->user_id;
            $cacheKeyPrefix = 'user_orders_'.$userId.'_page_';

            // Invalidate all pages of the user's orders cache
            Cache::forget($cacheKeyPrefix.'1');
        });
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

    public function lineItems(): HasMany
    {
        return $this->hasMany(OrderLineItem::class);
    }
}
