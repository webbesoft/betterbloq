<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentOperation extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['line_amounts' => 'array', 'request' => 'array', 'response' => 'array'];
    }
}
