<?php

namespace App\Services;

use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class LogService
{
    /**
     * Log a message.
     */
    public function createLog(string $level, string $message, ?string $class = null, ?string $method = null, array $context = []): Log
    {
        return Log::create([
            'user_id' => Auth::user() ? Auth::id() : null,
            'level' => $level,
            'class' => $class,
            'method' => $method,
            'message' => $message,
            'context' => $context,
        ]);
    }

    /**
     * Log an exception.
     */
    public function logException(\Throwable $exception, ?string $class = null, ?string $method = null, array $context = []): Log
    {
        return Log::create([
            'user_id' => Auth::id(),
            'level' => 'error', // Default level for exceptions
            'class' => $class,
            'method' => $method,
            'message' => $exception->getMessage(),
            'exception_type' => get_class($exception),
            'exception_message' => $exception->getMessage(),
            'exception_trace' => $exception->getTraceAsString(),
            // TODO: add context
            // 'context' => $context,
        ]);
    }
}
