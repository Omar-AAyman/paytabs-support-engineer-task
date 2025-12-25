<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentType;
use App\Enums\ShippingMethod;
use App\Http\Requests\InitiatePaymentRequest;
use App\Models\Order;
use App\Models\PaymentLog;
use App\Services\PayTabsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayTabsController extends Controller
{
    /** @var PayTabsService */
    protected $payTabs;

    /**
     * @param PayTabsService $payTabs
     */
    public function __construct(PayTabsService $payTabs)
    {
        $this->payTabs = $payTabs;
    }

    /**
     * Initiate a payment request to PayTabs.
     *
     * @param InitiatePaymentRequest $request Custom request validation.
     * @param Order $order The order model instance.
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPayment(InitiatePaymentRequest $request, Order $order)
    {
        // Defaults for simplified checkout
        $country = 'EG';
        $zip = '00000';
        $state = $request->city; // Fallback state to city

        $order->update([
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'address' => "{$request->address}, {$request->city}, {$state}, {$zip}, {$country}",
            'shipping_method' => ShippingMethod::from($request->shipping_method),
        ]);

        $customer = [
            'name' => $order->customer_name,
            'email' => $order->customer_email,
            'phone' => $request->customer_phone,
            'street' => $request->address,
            'city' => $request->city,
            'state' => $state,
            'country' => $country,
            'zip' => $zip,
            'ip' => $request->ip(),
        ];

        $result = $this->payTabs->sendPaymentRequest($order, $customer);
        $tranRef = $result['response']['tran_ref'] ?? null;

        if ($tranRef) {
            session(['paytabs_tran_ref' => $tranRef]);
        }

        $order->paymentLogs()->create([
            'type' => PaymentType::Auth,
            'transaction_id' => $tranRef,
            'request_payload' => $result['payload'],
            'response_payload' => $result['response'],
        ]);

        if (isset($result['response']['redirect_url'])) {
            return response()->json(['redirect_url' => $result['response']['redirect_url']]);
        }

        return response()->json(['error' => 'Payment initiation failed'], 400);
    }

    /**
     * Handle the client-side return from PayTabs.
     * Uses transaction reference to query actual status from API.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function return(Request $request)
    {
        $tranRef = $request->input('tranRef') ?? $request->input('tran_ref') ?? session('paytabs_tran_ref');

        if (!$tranRef) {
            return redirect()->route('home')->with('error', 'Invalid payment return.');
        }

        $log = PaymentLog::where('transaction_id', $tranRef)->first();
        if (!$log || !$log->order) {
            return redirect()->route('home')->with('error', 'Order not found.');
        }

        $order = $log->order;

        // Query API for authoritative status
        $verification = $this->payTabs->queryTransaction($tranRef);
        $response = $verification['response'];
        $status = $response['payment_result']['response_status'] ?? '';
        $isSuccess = $status === 'A';

        $this->updateOrder($order, $isSuccess, $response, $tranRef);

        return $isSuccess
            ? view('payment.success', compact('order'))
            : view('payment.failure', compact('order'));
    }

    /**
     * Handle Server-to-Server Callback (Webhook/IPN).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function callback(Request $request)
    {
        $data = $request->all();

        if (!$this->payTabs->isValidRedirect($data)) {
            Log::warning('PayTabs Callback: Invalid Signature', ['data' => $data]);
            return response()->json(['message' => 'Invalid Signature'], 400);
        }

        $tranRef = $request->input('tran_ref') ?? $request->input('tranRef');
        $cartId = $request->input('cart_id') ?? $request->input('cartId');

        $order = Order::find($cartId);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $status = $data['payment_result']['response_status'] ?? '';
        $isSuccess = $status === 'A';

        $this->updateOrder($order, $isSuccess, $data, $tranRef);

        return response()->json(['message' => 'OK']);
    }

    /**
     * Process a refund for an order.
     *
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function refund(Request $request, Order $order)
    {
        $authLog = $order->paymentLogs()
            ->where('type', PaymentType::Auth)
            ->whereNotNull('transaction_id')
            ->latest()
            ->first();

        if (!$authLog) {
            return back()->with('error', 'No transaction found to refund.');
        }

        $result = $this->payTabs->sendRefundRequest($order, $authLog->transaction_id);
        $status = $result['response']['payment_result']['response_status'] ?? '';
        $success = $status === 'A';

        $order->paymentLogs()->create([
            'type' => PaymentType::Refund,
            'transaction_id' => $result['response']['tran_ref'] ?? null,
            'request_payload' => $result['payload'],
            'response_payload' => $result['response'],
        ]);

        if ($success) {
            $order->update(['status' => OrderStatus::Refunded]);
            foreach ($order->items as $item) {
                $item->product->increment('stock', $item->quantity);
            }
        }

        return back()->with($success ? 'success' : 'error', $result['response']['payment_result']['response_message'] ?? 'Refund processed');
    }

    /**
     * Update order status based on payment result.
     *
     * @param Order $order
     * @param bool $success
     * @param array $data Response payload
     * @param string|null $tranRef
     * @return void
     */
    protected function updateOrder(Order $order, bool $success, array $data, $tranRef)
    {
        if ($success && $order->status !== OrderStatus::Completed) {
            $order->update(['status' => OrderStatus::Completed]);
        } elseif (!$success && $order->status !== OrderStatus::Failed && $order->status !== OrderStatus::Completed) {
            $order->update(['status' => OrderStatus::Failed]);
        }

        $order->paymentLogs()->create([
            'type' => PaymentType::Auth,
            'transaction_id' => $tranRef,
            'request_payload' => [],
            'response_payload' => $data,
        ]);
    }
}
