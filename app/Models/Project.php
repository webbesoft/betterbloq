<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Apps\BulkBuy\Models\Order;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Apps\BulkBuy\Models\Project
 *
 * @property int $id
 */
class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'budget',
        'start_date',
        'target_completion_date',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
