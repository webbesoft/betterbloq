<?php

namespace App\Apps\BulkBuy\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\Apps\BulkBuy\CategoryFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $fillable = [
        'name',
    ];
}
