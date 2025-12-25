<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\ShippingMethod;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'customer_name',
        'customer_email',
        'address',
        'cart_total',
        'status',
        'shipping_method'
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'shipping_method' => ShippingMethod::class,
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentLogs()
    {
        return $this->hasMany(PaymentLog::class);
    }
}
