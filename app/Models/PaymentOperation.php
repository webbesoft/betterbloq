<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property array<int, array{amount: int, discount_basis_points: int}> $line_amounts
 * @property array<string, mixed>|null $request
 * @property array<string, mixed>|null $response
 */
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
