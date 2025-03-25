<?php

namespace App\Apps\BulkBuy\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Apps\BulkBuy\Models\Product
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property float $price
 * @property int $category_id
 * @property int $vendor_id
 * @property string $image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 *
 * @mixin Eloquent
 */
class Product extends Model
{
    /** @use HasFactory<\Database\Factories\Apps\BulkBuy\ProductFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'category_id',
        'vendor_id',
        'image',
        'price',
        'unit',
        'amount',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
