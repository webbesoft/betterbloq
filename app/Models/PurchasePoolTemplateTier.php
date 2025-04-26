<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchasePoolTemplateTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_pool_template_id',
        'name',
        'description',
        'discount_percentage',
        'min_volume',
        'max_volume',
    ];

    public function casts(): array
    {
        return [
            'discount_percentage' => 'decimal:2',
            'min_volume' => 'decimal:2',
            'max_volume' => 'decimal:2',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(PurchasePoolTemplate::class, 'purchase_pool_template_id');
    }
}
