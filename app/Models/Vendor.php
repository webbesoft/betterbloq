<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * App\Models\Vendor
 *
 * @property int id
 * @property string phone
 */
class Vendor extends Model
{
    /** @use HasFactory<\Database\Factories\VendorFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'user_id',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::created(function ($vendor) {
            Cache::forget('shop_available_filters');
        });

        static::updated(function ($vendor) {
            Cache::forget('shop_available_filters');
        });
    }
}
