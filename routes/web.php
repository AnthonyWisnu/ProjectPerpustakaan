<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Member routes
    Route::middleware('member')->name('member.')->group(function () {
        Route::get('/', function () {
            return view('member.dashboard');
        })->name('dashboard');

        // Books
        Route::get('/books', [\App\Http\Controllers\Member\BookController::class, 'index'])->name('books.index');
        Route::get('/books/{id}', [\App\Http\Controllers\Member\BookController::class, 'show'])->name('books.show');

        // Cart
        Route::get('/cart', [\App\Http\Controllers\Member\CartController::class, 'index'])->name('cart.index');
        Route::post('/cart', [\App\Http\Controllers\Member\CartController::class, 'store'])->name('cart.store');
        Route::delete('/cart/{id}', [\App\Http\Controllers\Member\CartController::class, 'destroy'])->name('cart.destroy');
        Route::post('/cart/clear', [\App\Http\Controllers\Member\CartController::class, 'clear'])->name('cart.clear');
        Route::get('/cart/checkout', [\App\Http\Controllers\Member\CartController::class, 'checkout'])->name('cart.checkout');
        Route::post('/cart/checkout', [\App\Http\Controllers\Member\CartController::class, 'processCheckout'])->name('cart.processCheckout');

        // Reservations
        Route::get('/reservations', [\App\Http\Controllers\Member\ReservationController::class, 'index'])->name('reservations.index');
        Route::get('/reservations/{id}', [\App\Http\Controllers\Member\ReservationController::class, 'show'])->name('reservations.show');
        Route::post('/reservations/{id}/cancel', [\App\Http\Controllers\Member\ReservationController::class, 'cancel'])->name('reservations.cancel');

        // Loans
        Route::get('/loans', [\App\Http\Controllers\Member\LoanController::class, 'index'])->name('loans.index');
        Route::get('/loans/{id}', [\App\Http\Controllers\Member\LoanController::class, 'show'])->name('loans.show');
        Route::post('/loans/{id}/extend', [\App\Http\Controllers\Member\LoanController::class, 'extend'])->name('loans.extend');
    });

    // Admin routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');
    });
});
