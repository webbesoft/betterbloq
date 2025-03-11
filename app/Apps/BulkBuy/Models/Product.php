<?php

namespace App\Apps\BulkBuy\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
