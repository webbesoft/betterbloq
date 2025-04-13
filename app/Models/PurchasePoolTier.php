<?php

namespace App\Models;

use Database\Factories\PurchasePoolTierFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PurchasePoolTier
 *
 * @property int id
 * @property string name
 * @property string description
 * @property float discount_percentage
 * @property int min_volume
 * @property int max_volume
 *
 * @method static Builder|PurchasePoolTier query()
 * @method static Builder|PurchasePoolTier queryModel()
 */
class PurchasePoolTier extends Model
{
    /** @use HasFactory<PurchasePoolTierFactory> */
    use HasFactory;

    protected $table = 'purchase_pool_tiers';

    protected $fillable = [
        'name',
        'description',
        'discount_percentage',
        'min_volume',
        'max_volume',
    ];
}
