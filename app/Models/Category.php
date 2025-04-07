<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $fillable = [
        'name',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::created(function ($category) {
            Cache::forget('shop_available_filters');
        });

        static::updated(function ($category) {
            Cache::forget('shop_available_filters');
        });
    }
}
