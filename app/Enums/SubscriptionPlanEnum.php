<?php

namespace App\Enums;

enum SubscriptionPlanEnum: string
{
    case BASIC = 'basic_monthly';
    case PRO = 'pro_monthly';
    case FREE = 'free_yearly';
}
