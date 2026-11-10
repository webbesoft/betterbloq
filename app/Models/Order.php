<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * App\Models\Order
 *
 * @property-read Collection<int, OrderLineItem> $lineItems
 * @property int id
 * @property string name
 * @property string email
 * @property string phone
 * @property string address
 * @property int product_id
 * @property int user_id
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

    public const ACTIVE_STATUSES = [
        'created',
        'paid',
        'payment_authorized',
        'pending_finalization',
        'processing_capture',
    ];

    protected $fillable = [
        'notes',
        'email',
        'phone',
        'address',
        'status',
        'quantity',
        'purchase_cycle_id',
        'product_id',
        'user_id',
        'stripe_session_id',
        'payment_intent_id',
        'initial_amount',
        'final_amount',
        'project_id',
        'vendor_id',
        'total_amount',
        'shipping_address_id',
        'billing_address_id',
        'product_subtotal',
        'storage_cost_applied',
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

        $invalidate = function ($model) {
            DB::afterCommit(function () use ($model) {
                Cache::put('user_orders_version_'.$model->user_id, (string) Str::uuid());
                Cache::forget('dashboard_data_'.$model->user_id);
            });
        };
        static::saved($invalidate);
        static::deleted($invalidate);
    }

    public function name(): string
    {
        return '#'.$this->id;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function purchaseCycle(): BelongsTo
    {
        return $this->belongsTo(PurchaseCycle::class, 'purchase_cycle_id');
    }

    /** @return HasMany<OrderLineItem, $this> */
    public function lineItems(): HasMany
    {
        return $this->hasMany(OrderLineItem::class);
    }

    public function getOrderTotalAttribute(): float
    {
        return $this->status === 'completed' && $this->final_amount !== null
            ? (float) $this->final_amount
            : (float) $this->total_amount;
    }

    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }
}
