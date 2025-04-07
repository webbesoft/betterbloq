<?php

namespace App\Models;

use Database\Factories\PlanFeaturesFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\PlanFeature
 *
 * @property int id
 * @property string description
 * @property string plan_id
 *
 * @method static Builder|PlanFeature query()
 * @method static Builder|PlanFeature newModelQuery()
 * @method static Builder|PlanFeature newQuery()
 */
class PlanFeature extends Model
{
    /** @use HasFactory<PlanFeaturesFactory> */
    use HasFactory;

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
