@extends('layouts.app')

@section('content')
    <div class="glass-card">
        <div class="flex-between">
            <div>
                <h1>Order #{{ $order->id }}</h1>
                <span class="badge badge-{{ $order->status->value }}">{{ ucfirst($order->status->value) }}</span>
            </div>
            @if ($order->status === \App\Enums\OrderStatus::Completed)
                <form action="{{ route('orders.refund', $order) }}" method="POST"
                    onsubmit="return confirm('Are you sure you want to refund this order?');">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="background: var(--secondary);">Refund Order</button>
                </form>
            @endif
        </div>



        <div class="grid-2 mt-4">
            <div>
                <h3>Customer Details</h3>
                <p><strong>Name:</strong> {{ $order->customer_name }}</p>
                <p><strong>Email:</strong> {{ $order->customer_email }}</p>
                <p><strong>Shipping:</strong> {{ ucfirst($order->shipping_method?->value ?? 'N/A') }}</p>
            </div>
            <div>
                <h3>Billing/Shipping Address</h3>
                <p>{{ $order->address }}</p>
            </div>
        </div>

        <h2 class="mt-4">Order Items</h2>
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ number_format($item->price, 2) }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->price * $item->quantity, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right"><strong>Total:</strong></td>
                    <td><strong>{{ number_format($order->cart_total, 2) }} EGP</strong></td>
                </tr>
            </tfoot>
        </table>

        <h2 class="mt-4">Payment & System Logs</h2>
        @foreach ($order->paymentLogs->sortByDesc('created_at') as $log)
            <div class="glass-card mt-4 log-card">
                <div class="flex-between mb-4">
                    <h4>Transaction: {{ $log->transaction_id ?? 'N/A' }} <small>({{ $log->created_at }})</small></h4>
                    @if (count($log->request_payload) === 0)
                        <span class="badge badge-callback">Callback</span>
                    @else
                        @php
                            $badgeClass = match ($log->type->value) {
                                'auth' => 'badge-auth',
                                'refund' => 'badge-refund',
                                default => 'badge-other',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ ucfirst($log->type->value) }}</span>
                    @endif
                </div>

                <div class="grid-2">
                    @if (count($log->request_payload) > 0)
                        <div>
                            <h5>Request Payload</h5>
                            <pre>{{ json_encode($log->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                        </div>
                    @else
                        <div>
                            <h5>Event Type</h5>
                            <div class="callback-info">
                                <strong>Start of Callback / IPN</strong><br>
                                This log represents an incoming server-to-server notification from PayTabs.
                            </div>
                        </div>
                    @endif
                    <div>
                        <h5>Response Payload</h5>
                        <pre>{{ json_encode($log->response_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
