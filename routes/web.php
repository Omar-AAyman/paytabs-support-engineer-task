<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\PayTabsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('orders.index');
})->name('home');

// Orders
Route::resource('orders', OrderController::class)->only(['index', 'create', 'store', 'show']);
Route::get('orders/{order}/checkout', [OrderController::class, 'checkout'])->name('orders.checkout');
Route::post('orders/{order}/pay', [PayTabsController::class, 'createPayment'])->name('orders.pay');
Route::post('orders/{order}/refund', [PayTabsController::class, 'refund'])->name('orders.refund');

// PayTabs Callback & Return
Route::post('paytabs/callback', [PayTabsController::class, 'callback'])->name('paytabs.callback');
Route::any('paytabs/return', [PayTabsController::class, 'return'])->name('paytabs.return');
