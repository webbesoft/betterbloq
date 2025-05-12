<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Warehouse extends Model
{
    /** @use HasFactory<\Database\Factories\WarehouseFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'total_capacity',
        'available_capacity',
        'total_capacity_unit',
        'default_storage_price_per_unit',
        'default_storage_price_period',
        'supported_storage_conditions',
        'is_active',
    ];

    public function casts(): array
    {
        return [
            'supported_storage_conditions' => 'array',
        ];
    }

    public function storageTiers(): HasMany
    {
        return $this->hasMany(StorageTier::class);
    }

    public function storageOrders(): HasMany
    {
        return $this->hasMany(StorageOrder::class);
    }

    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'model');
    }
}
