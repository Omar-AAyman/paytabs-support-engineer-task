<?php

namespace App\Enums;

enum ShippingMethod: string
{
    case Pickup = 'pickup';
    case Shipping = 'shipping';
}
