<?php

namespace App\Enums;

enum NotificationType: string
{
    case MATTER_AUTHORISED = 'matter_authorised';
    case MATTER_UNLOCKED = 'matter_unlocked';
    case PAYMENT_PROCESSING_SUCCESSFUL = 'payment_successful';
    case PAYMENT_PROCESSING_FAILED = 'payment_failed';
}
