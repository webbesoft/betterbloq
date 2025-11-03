<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    /** @use HasFactory<\Database\Factories\AddressFactory> */
    use HasFactory;

    protected $fillable = [
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country_code',
        'model_id',
        'model_type',
    ];

    protected $casts = [
        'model_id' => 'integer',
        'model_type' => 'string',
    ];

    public function model()
    {
        return $this->morphTo();
    }

    public function getFullAddressAttribute(): string
    {
        $addressParts = [
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country_code,
        ];

        return implode(', ', array_filter($addressParts));
    }
}
