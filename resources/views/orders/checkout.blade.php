@extends('layouts.app')

@section('content')
    <div class="grid-2">
        <!-- Checkout Form -->
        <div class="glass-card" id="checkout-section">
            <h2 class="mb-4">Checkout Information</h2>

            <form id="payment-form" data-url="{{ route('orders.pay', $order) }}">
                @csrf
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="customer_name" class="form-input" required>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="customer_email" class="form-input" required>
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="customer_phone" class="form-input" required>
                </div>

                <div class="form-group">
                    <label>Shipping Method</label>
                    <select name="shipping_method" class="form-select" required>
                        <option value="pickup">Store Pickup</option>
                        <option value="shipping">Home Delivery</option>
                    </select>
                </div>

                <h3 class="mt-4 mb-4">Billing Address</h3>
                <div class="form-group">
                    <label>Street Address</label>
                    <input type="text" name="address" class="form-input" required>
                </div>

                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="city" class="form-input" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;" id="pay-btn">
                    Pay {{ number_format($order->cart_total, 2) }} EGP
                </button>
                <div id="loading" style="display:none; text-align:center; margin-top:10px;">Processing...</div>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="glass-card" style="height: fit-content;">
            <h2>Order Summary</h2>
            <div style="margin-top: 1rem;">
                @foreach ($order->items as $item)
                    <div class="flex-between" style="padding: 0.5rem 0; border-bottom: 1px solid var(--border);">
                        <span>{{ $item->product->name }} x {{ $item->quantity }}</span>
                        <span>{{ number_format($item->price * $item->quantity, 2) }}</span>
                    </div>
                @endforeach
                <div class="flex-between" style="padding: 1rem 0; font-weight: 700; font-size: 1.2rem;">
                    <span>Total</span>
                    <span>{{ number_format($order->cart_total, 2) }} EGP</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Iframe Container -->
    <div id="payment-iframe-container" class="glass-card mt-4" style="display: none;">
        <h2 class="mb-4">Secure Payment</h2>
        <iframe id="payment-iframe" src=""></iframe>
        <div id="fallback-area" style="text-align: center;"></div>
    </div>

    <script src="{{ asset('js/payment.js') }}"></script>
@endsection
