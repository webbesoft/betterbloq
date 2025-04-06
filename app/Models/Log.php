<?php

namespace App\Models;

use Database\Factories\LogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Log extends Model
{
    /** @use HasFactory<LogFactory> */
    use HasFactory;

    protected $table = 'logs';

    protected $fillable = [
        'user_id',
        'level',
        'class',
        'method',
        'message',
        'exception_type',
        'exception_message',
        'exception_trace',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
