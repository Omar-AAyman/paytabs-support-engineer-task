<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Product;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::latest()->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        $products = Product::all();

        return view('orders.create', compact('products'));
    }

    public function store(StoreOrderRequest $request)
    {
        $total = 0;
        $orderItems = [];

        foreach ($request->products as $item) {
            $qty = $item['quantity'] ?? 0;
            if ($qty > 0) {
                $product = Product::find($item['id'] ?? null);
                
                // Decrement Stock
                $product->decrement('stock', $item['quantity']);

                $total += $product->price * $item['quantity'];
                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ];
            }
        }

        if (empty($orderItems)) {
            return back()->withErrors(['products' => 'Please select at least one product.']);
        }

        $order = Order::create([
            'cart_total' => $total,
            'status' => OrderStatus::Pending,
            'customer_name' => 'Guest',
            'customer_email' => 'guest@example.com',
            'address' => 'Pending Address',
        ]);

        foreach ($orderItems as $item) {
            $order->items()->create($item);
        }

        return redirect()->route('orders.checkout', $order);
    }

    public function checkout(Order $order)
    {
        if ($order->status === OrderStatus::Completed) {
            return redirect()->route('orders.show', $order);
        }

        return view('orders.checkout', compact('order'));
    }

    public function show(Order $order)
    {
        $order->load(['items.product', 'paymentLogs']);

        return view('orders.show', compact('order'));
    }
}
