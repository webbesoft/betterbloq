<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

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
 * @property \Illuminate\Support\Carbon|null deleted_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order newModelQuery()
 */
class Order extends Model
{
    /** @use HasFactory<\Database\Factories\Apps\BulkBuy\OrderFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'email',
        'phone',
        'address',
        'status',
        'purchase_pool_id',
        'product_id',
        'user_id',
    ];

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
}
