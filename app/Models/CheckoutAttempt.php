<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckoutAttempt extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['snapshot' => 'array', 'stripe_payload' => 'array', 'expires_at' => 'immutable_datetime'];
    }
}
