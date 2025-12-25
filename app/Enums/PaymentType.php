<?php

namespace App\Enums;

enum PaymentType: string
{
    case Auth = 'auth';
    case Refund = 'refund';
}
