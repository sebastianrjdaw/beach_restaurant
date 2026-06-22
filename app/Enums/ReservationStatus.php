<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case Pending = 'pending';
    case PendingEmailVerification = 'pending_email_verification';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';
    case Completed = 'completed';
}
