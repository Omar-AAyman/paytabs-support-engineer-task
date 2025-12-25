<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;

class PayTabsService
{
    private $profileId;

    private $serverKey;

    private $baseUrl;

    public function __construct()
    {
        $this->profileId = config('paytabs.profile_id');
        $this->serverKey = config('paytabs.server_key');
        $this->baseUrl = config('paytabs.base_url');
    }

    public function sendPaymentRequest(Order $order, array $customerDetails)
    {
        $callbackUrl = route('paytabs.callback');
        $returnUrl = route('paytabs.return');

        if ($tunnelUrl = config('paytabs.callback_base_url')) {
            $callbackUrl = $tunnelUrl.'/paytabs/callback';
            $returnUrl = $tunnelUrl.'/paytabs/return';
        }

        $payload = [
            'profile_id' => $this->profileId,
            'tran_type' => 'sale',
            'tran_class' => 'ecom',
            'cart_id' => (string) $order->id,
            'cart_description' => "Order #{$order->id}",
            'cart_currency' => config('paytabs.currency'),
            'cart_amount' => $order->cart_total,
            'callback' => $callbackUrl,
            'return' => $returnUrl,
            'hide_shipping' => true,
            'framed' => true,
            'customer_details' => $customerDetails,
        ];

        return $this->sendRequest($payload);
    }

    public function sendRefundRequest(Order $order, string $transactionId)
    {
        $payload = [
            'profile_id' => $this->profileId,
            'tran_type' => 'refund',
            'tran_class' => 'ecom',
            'tran_ref' => $transactionId,
            'cart_id' => (string) $order->id,
            'cart_currency' => config('paytabs.currency'),
            'cart_amount' => $order->cart_total,
            'cart_description' => "Refund Order #{$order->id}",
        ];

        return $this->sendRequest($payload);
    }

    private function sendRequest(array $payload)
    {
        $response = Http::withHeaders([
            'Authorization' => $this->serverKey,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl, $payload);

        return [
            'payload' => $payload,
            'response' => $response->json(),
            'success' => $response->successful(),
        ];
    }
}
