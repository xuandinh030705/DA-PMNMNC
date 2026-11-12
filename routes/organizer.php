<?php

use App\Http\Controllers\Organizer\CheckinController;
use App\Http\Controllers\Organizer\DashboardController;
use App\Http\Controllers\Organizer\EventController;
use App\Http\Controllers\Organizer\OrderController;
use App\Http\Controllers\Organizer\TicketTypeController;
use Illuminate\Support\Facades\Route;

Route::prefix('organizer')
    ->name('organizer.')
    ->middleware(['auth', 'active', 'role:organizer'])
    ->group(function () {
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Events CRUD & Submit
        Route::resource('events', EventController::class);
        Route::post('events/{event}/submit', [EventController::class, 'submit'])->name('events.submit');

        // Ticket Types nested shallow in Events
        Route::resource('events.ticket-types', TicketTypeController::class)
            ->shallow()
            ->except(['index', 'show']);

        // Orders of Organizer's events
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order:order_code}', [OrderController::class, 'show'])->name('orders.show');

        // Checkin / Ticket verification
        Route::get('checkin', [CheckinController::class, 'form'])->name('checkin.form');
        Route::post('checkin', [CheckinController::class, 'store'])->name('checkin.store');
    });
