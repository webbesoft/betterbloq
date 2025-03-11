<?php

namespace App\Apps\BulkBuy\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchasePool extends Model
{
    /** @use HasFactory<\Database\Factories\Apps\BulkBuy\PurchasePoolFactory> */
    use HasFactory, SoftDeletes;
}
