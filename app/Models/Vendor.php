<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    /** @use HasFactory<\Database\Factories\Apps\BulkBuy\VendorFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'user_id',
    ];
}
