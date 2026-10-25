<?php

use Illuminate\Support\Facades\Route;

// Temporary placeholder for home until Phase 3 implements full StorefrontController
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');
