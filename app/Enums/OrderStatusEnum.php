<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case CREATED = 'created';
    case PAYMENT_AUTHORIZED = 'payment_authorized';
    case PENDING_FINALIZATION = 'pending_finalization';
    case PROCESSING_CAPTURE = 'processing_capture';
    case COMPLETED = 'completed';
    case CAPTURE_FAILED = 'capture_failed';
    case CANCELLED = 'cancelled';
}
