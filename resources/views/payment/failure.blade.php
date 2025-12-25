@extends('layouts.app')

@section('content')
    <script>
        if (window.self !== window.top) {
            window.top.location.href = window.location.href;
        }
    </script>
    <div class="glass-card"
        style="text-align: center; max-width: 600px; margin: 4rem auto; background: rgba(30, 41, 59, 0.9);">
        <div style="font-size: 4rem; color: #f87171; margin-bottom: 1rem;">❌</div>
        <h1 class="mb-4" style="color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">Payment Failed</h1>
        <p class="mb-4" style="color: #cbd5e1; font-size: 1.1rem;">We were unable to process your payment.</p>

        @if (request('message'))
            <div class="alert alert-error" style="display: inline-block;">
                {{ request('message') }}
            </div>
        @endif

        <div class="mt-4">
            <a href="{{ route('orders.checkout', $order) }}" class="btn btn-primary" target="_top"
                style="background: var(--text-muted); color: white; text-decoration: none;">Try Again</a>
            <a href="{{ route('orders.show', $order) }}" class="btn" target="_top"
                style="margin-left: 10px; color: white;">View Order</a>
        </div>
    </div>
@endsection
