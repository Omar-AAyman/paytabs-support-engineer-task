@extends('layouts.app')

@section('content')
    <div class="glass-card">
        <div class="flex-between" style="margin-bottom: 2rem;">
            <h1>Orders History</h1>
            <a href="{{ route('orders.create') }}" class="btn btn-primary">Create New Order</a>
        </div>

        <table class="orders-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                        <td>
                            <div style="font-weight: 600;">{{ $order->customer_name }}</div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $order->customer_email }}</div>
                        </td>
                        <td>{{ number_format($order->cart_total, 2) }} EGP</td>
                        <td>{{ ucfirst($order->shipping_method?->value ?? '-') }}</td>
                        <td>
                            <span class="badge badge-{{ $order->status->value }}">
                                {{ ucfirst($order->status->value) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-primary">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    </div>
@endsection
