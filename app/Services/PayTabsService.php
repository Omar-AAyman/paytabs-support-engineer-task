<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayTabsService
{
    protected $profileId;

    protected $serverKey;

    protected $baseUrl;

    protected $currency;

    protected $callbackUrl;

    public function __construct()
    {
        $this->profileId = config('paytabs.profile_id');
        $this->serverKey = config('paytabs.server_key');
        $this->baseUrl = config('paytabs.base_url');
        $this->currency = config('paytabs.currency');
        $this->callbackUrl = config('paytabs.callback_base_url');
    }

    /**
     * Send a payment request to PayTabs to initiate a transaction.
     *
     * @param Order $order The order details.
     * @param array $customerDetails Customer information (name, email, etc.).
     * @return array Response payload from PayTabs.
     */
    public function sendPaymentRequest(Order $order, array $customerDetails)
    {
        $callbackUrl = "{$this->callbackUrl}/paytabs/callback";
        $returnUrl = "{$this->callbackUrl}/paytabs/return";

        $payload = [
            'profile_id' => $this->profileId,
            'tran_type' => 'sale',
            'tran_class' => 'ecom',
            'cart_id' => (string) $order->id,
            'cart_description' => "Order #{$order->id}",
            'cart_currency' => $this->currency,
            'cart_amount' => number_format($order->cart_total, 2, '.', ''),
            'callback' => $callbackUrl,
            'return' => $returnUrl,
            'hide_shipping' => true,
            'framed' => true,
            'customer_details' => $customerDetails,
        ];

        return $this->sendRequest($payload);
    }

    /**
     * Send a refund request for a specific transaction.
     *
     * @param Order $order The order associated with the transaction.
     * @param string $transactionId The PayTabs transaction reference to refund.
     * @return array Response payload from PayTabs.
     */
    public function sendRefundRequest(Order $order, string $transactionId)
    {
        $payload = [
            'profile_id' => $this->profileId,
            'tran_type' => 'refund',
            'tran_class' => 'ecom',
            'tran_ref' => $transactionId,
            'cart_id' => (string) $order->id,
            'cart_currency' => $this->currency,
            'cart_amount' => number_format($order->cart_total, 2, '.', ''),
            'cart_description' => "Refund Order #{$order->id}",
        ];

        return $this->sendRequest($payload);
    }

    /**
     * Query the status of a specific transaction from PayTabs API.
     *
     * @param string $transactionId The transaction reference to query.
     * @return array An array containing 'payload', 'response', and 'success' status.
     */
    public function queryTransaction(string $transactionId)
    {
        $queryUrl = str_replace('/request', '/query', $this->baseUrl);
        
        $payload = [
            'profile_id' => $this->profileId,
            'tran_ref' => $transactionId,
        ];

        $response = Http::withHeaders([
            'Authorization' => $this->serverKey,
            'Content-Type' => 'application/json',
        ])->post($queryUrl, $payload);

        return [
            'payload' => $payload,
            'response' => $response->json(),
            'success' => $response->successful(),
        ];
    }
    
    /**
     * Validate the signature of a PayTabs redirect or callback.
     * Uses HMAC SHA-256 with the Server Key.
     *
     * @param array $postValues The received POST parameters.
     * @return bool True if signature is valid, False otherwise.
     */
    public function isValidRedirect(array $postValues)
    {
        if (empty($postValues) || !array_key_exists('signature', $postValues)) {
            return false;
        }

        $serverKey = $this->serverKey;
        $requestSignature = $postValues["signature"];
        unset($postValues["signature"]);
        
        $fields = array_filter($postValues);

        ksort($fields);

        $query = http_build_query($fields);

        $signature = hash_hmac('sha256', $query, $serverKey);

        return hash_equals($signature, $requestSignature);
    }

    /**
     * Helper to send the standard POST request to PayTabs.
     *
     * @param array $payload Request payload.
     * @return array Containing 'payload' and 'response'.
     */
    private function sendRequest(array $payload)
    {
        Log::info('PayTabs Request Payload', $payload);

        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withHeaders([
            'Authorization' => $this->serverKey,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl, $payload);

        Log::info('PayTabs API Response', $response->json());

        return [
            'payload' => $payload,
            'response' => $response->json(),
            'success' => $response->successful(),
        ];
    }
}
