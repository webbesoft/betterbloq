<?php

namespace App\Models;

use Database\Factories\PlanLimitsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\PlanLimit
 *
 * @property int id
 */
class PlanLimit extends Model
{
    /** @use HasFactory<PlanLimitsFactory> */
    use HasFactory;

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
