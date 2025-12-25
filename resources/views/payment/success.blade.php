@extends('layouts.app')

@section('content')
    <script>
        if (window.self !== window.top) {
            window.top.location.href = window.location.href;
        }
    </script>
    <div class="glass-card"
        style="text-align: center; max-width: 600px; margin: 4rem auto; background: rgba(30, 41, 59, 0.9);">
        <div style="font-size: 4rem; color: #4ade80; margin-bottom: 1rem;">✅</div>
        <h1 class="mb-4" style="color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">Payment Successful!</h1>
        <p class="mb-4" style="color: #cbd5e1; font-size: 1.1rem;">Thank you for your purchase. Your payment has been
            processed
            successfully.</p>

        <div class="mb-4">
            <span class="badge badge-completed" style="font-size: 1rem; padding: 0.5rem 1rem;">Order
                #{{ $order->id }}</span>
        </div>

        <a href="{{ route('orders.show', $order) }}" class="btn btn-primary" target="_top"
            style="color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; background: #6366f1;">View
            Order Details</a>
    </div>
@endsection
