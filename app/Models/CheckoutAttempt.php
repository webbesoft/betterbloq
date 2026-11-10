<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property array<string, mixed> $stripe_payload
 * @property list<array<string, mixed>> $snapshot
 * @property CarbonImmutable $expires_at
 */
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
