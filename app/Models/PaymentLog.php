<?php

namespace App\Models;

use App\Enums\PaymentType;
use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    protected $fillable = ['order_id', 'transaction_id', 'type', 'request_payload', 'response_payload'];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'type' => PaymentType::class,
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
