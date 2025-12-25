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
    private $payTabsService;

    public function __construct(PayTabsService $payTabsService)
    {
        $this->payTabsService = $payTabsService;
    }

    public function createPayment(InitiatePaymentRequest $request, Order $order)
    {
        $order->update([
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'address' => "{$request->address}, {$request->city}, {$request->state}, {$request->zip}, {$request->country}",
            'shipping_method' => ShippingMethod::from($request->shipping_method),
        ]);

        $customerDetails = [
            'name' => $order->customer_name,
            'email' => $order->customer_email,
            'street' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'zip' => $request->zip,
            'ip' => $request->ip(),
        ];

        $result = $this->payTabsService->sendPaymentRequest($order, $customerDetails);

        $order->paymentLogs()->create([
            'type' => PaymentType::Auth,
            'transaction_id' => $result['response']['tran_ref'] ?? null,
            'request_payload' => $result['payload'],
            'response_payload' => $result['response'],
        ]);

        if (isset($result['response']['redirect_url'])) {
            return response()->json(['redirect_url' => $result['response']['redirect_url']]);
        }

        return response()->json(['error' => 'Failed to initiate payment', 'details' => $result['response']], 400);
    }

    public function return(Request $request)
    {
        $tranRef = $request->tranRef;
        if (! $tranRef) {
            return redirect()->route('home')->with('error', 'Invalid payment return.');
        }

        $log = PaymentLog::where('transaction_id', $tranRef)->first();

        if (! $log) {
            return redirect()->route('home');
        }

        $order = Order::find($log->order_id);

        if ($order->status === OrderStatus::Completed) {
            return view('payment.success', compact('order'));
        }

        return view('payment.failure', compact('order'));
    }

    public function callback(Request $request)
    {
        $data = $request->input();
        Log::info('PayTabs Callback Received', $data);

        $tranRef = $data['tran_ref'] ?? null;
        $orderId = $data['cart_id'] ?? null;

        if (! $orderId || ! $tranRef) {
            Log::error('PayTabs Callback Missing Data', ['order_id' => $orderId, 'tran_ref' => $tranRef]);

            return response()->json(['message' => 'Invalid data'], 400);
        }

        $order = Order::find($orderId);
        if (! $order) {
            Log::error('PayTabs Callback Order Not Found', ['order_id' => $orderId]);

            return response()->json(['message' => 'Order not found'], 404);
        }

        $success = isset($data['payment_result']['response_status']) && $data['payment_result']['response_status'] === 'A';
        
        if ($success) {
            $order->update(['status' => OrderStatus::Completed]);
            Log::info("Order #{$order->id} status updated to Completed (TranRef: {$tranRef})");
        } else {
            $order->update(['status' => OrderStatus::Failed]);
            Log::warning("Order #{$order->id} status updated to Failed (TranRef: {$tranRef})");
        }

        $order->paymentLogs()->create([
            'type' => PaymentType::Auth,
            'transaction_id' => $tranRef,
            'request_payload' => [],
            'response_payload' => $data,
        ]);

        return response()->json(['message' => 'OK']);
    }

    public function refund(Request $request, Order $order)
    {
        Log::info("Attempting refund for Order #{$order->id}");

        $authLog = $order->paymentLogs()
            ->where('type', PaymentType::Auth)
            ->whereNotNull('transaction_id')
            ->latest()
            ->first();

        if (! $authLog) {
            Log::warning("Refund failed: No valid transaction log found for Order #{$order->id}");

            return back()->with('error', 'No successful transaction found to refund.');
        }

        Log::info("Found transaction for refund: {$authLog->transaction_id}");

        $result = $this->payTabsService->sendRefundRequest($order, $authLog->transaction_id);

        Log::info("Refund API Response for Order #{$order->id}", $result['response']);

        $success = isset($result['response']['payment_result']['response_status'])
            && $result['response']['payment_result']['response_status'] === 'A';

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

            Log::info("Order #{$order->id} marked as Refunded and Stock Restored");
        } else {
            Log::error("Refund failed for Order #{$order->id}. Message: ".($result['response']['payment_result']['response_message'] ?? 'Unknown'));
        }

        $msg = $result['response']['payment_result']['response_message'] ?? 'Refund processed';

        return back()->with($success ? 'success' : 'error', $msg);
    }
}
