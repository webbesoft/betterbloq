<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\PurchasePoolRequestFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\PurchasePoolRequest
 *
 * @property int id
 * @property int product_id
 * @property int vendor_id
 * @property int user_id
 * @property Carbon $target_date
 *
 * @method static Builder|PurchasePoolRequest newQuery()
 * @method static Builder|PurchasePoolRequest newModelQuery()
 * @method static Builder|PurchasePoolRequest query()
 * @method static Builder|Product whereIsPending()
 * @method static Builder|PurchasePoolRequest whereUserId($value)
 * @method static Builder|PurchasePoolRequest whereProductId($value)
 *
 * @mixin \Illuminate\Database\Eloquent\
 */
class PurchasePoolRequest extends Model
{
    /** @use HasFactory<PurchasePoolRequestFactory> */
    use HasFactory;

    protected $table = 'purchase_pool_requests';

    protected $fillable = [
        'vendor_id',
        'quantity',
        'target_date',
        'user_id',
        'product_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeWhereUserId(Builder $query, string $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeWhereProductId(Builder $query, string $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    public function scopeWhereVendorId(Builder $query, string $vendorId): Builder
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeWhereIsPending($query)
    {
        return $query->where('status', 'pending');
    }
}
