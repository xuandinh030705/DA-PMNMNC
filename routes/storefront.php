<?php

use App\Http\Controllers\Storefront\OrderController;
use App\Http\Controllers\Storefront\StorefrontController;
use App\Http\Controllers\Storefront\TicketController;
use Illuminate\Support\Facades\Route;

// Public event browsing
Route::get('/', [StorefrontController::class, 'index'])->name('home');
Route::get('/events/{event:slug}', [StorefrontController::class, 'show'])->name('events.show');

// Customer authenticated and active group
Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/events/{event:slug}/checkout', [OrderController::class, 'checkout'])->name('checkout.store');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order:order_code}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order:order_code}/pay', [OrderController::class, 'pay'])->name('orders.pay');
    Route::post('/orders/{order:order_code}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    Route::get('/my-tickets', [TicketController::class, 'index'])->name('tickets.index');
});
