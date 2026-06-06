<?php

namespace App\Enums;

enum ReservationOrigin: string
{
    case Web = 'web';
    case Phone = 'phone';
    case Admin = 'admin';
}
