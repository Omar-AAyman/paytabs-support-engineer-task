@extends('layouts.app')

@section('content')
    <div class="glass-card" style="max-width: 800px; margin: 0 auto;">
        <h1>Create New Order</h1>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">Select products to add to the manual order.</p>

        <form action="{{ route('orders.store') }}" method="POST">
            @csrf

            @if ($errors->any())
                <div class="alert error" style="margin-bottom: 20px;">
                    <ul style="margin-left: 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="product-grid">
                @foreach ($products as $product)
                    <div class="product-card"
                        style="{{ $product->stock == 0 ? 'opacity: 0.6; pointer-events: none;' : '' }}">
                        <div class="flex-between mb-2">
                            <h3>{{ $product->name }}</h3>
                            <span style="color: var(--primary); font-weight: bold;">{{ number_format($product->price, 2) }}
                                EGP</span>
                        </div>

                        <div class="mb-4" style="font-size: 0.9rem;">
                            @if ($product->stock == 0)
                                <span class="badge" style="background: rgba(239, 68, 68, 0.2); color: #f87171;">Out of
                                    Stock</span>
                            @elseif($product->stock <= 5)
                                <span class="badge" style="background: rgba(234, 179, 8, 0.2); color: #facc15;">Low Stock:
                                    {{ $product->stock }} left</span>
                            @else
                                <span class="badge" style="background: rgba(34, 197, 94, 0.2); color: #4ade80;">In Stock:
                                    {{ $product->stock }}</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="products[{{ $loop->index }}][quantity]"
                                value="{{ old("products.{$loop->index}.quantity", 0) }}" min="0"
                                max="{{ $product->stock }}" class="form-input" style="width: 100%;"
                                {{ $product->stock == 0 ? 'disabled' : '' }}>

                            <input type="hidden" name="products[{{ $loop->index }}][id]" value="{{ $product->id }}"
                                {{ $product->stock == 0 ? 'disabled' : '' }}>

                            @error("products.{$loop->index}.quantity")
                                <div style="color: #f87171; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 text-right">
                <button type="submit" class="btn btn-primary">Proceed to Checkout</button>
            </div>
        </form>
    </div>
@endsection
