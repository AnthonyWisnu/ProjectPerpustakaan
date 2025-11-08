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
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

        // Books Management
        Route::resource('books', \App\Http\Controllers\Admin\BookController::class);
        Route::post('/books/{id}/restore', [\App\Http\Controllers\Admin\BookController::class, 'restore'])->name('books.restore');

        // Categories Management
        Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class)->except(['show']);

        // Users Management
        Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::get('/users/{id}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
        Route::post('/users/{id}/status', [\App\Http\Controllers\Admin\UserController::class, 'updateStatus'])->name('users.updateStatus');
        Route::post('/users/{id}/role', [\App\Http\Controllers\Admin\UserController::class, 'updateRole'])->name('users.updateRole');
        Route::post('/users/{id}/reset-password', [\App\Http\Controllers\Admin\UserController::class, 'resetPassword'])->name('users.resetPassword');

        // Reservations Management
        Route::get('/reservations', [\App\Http\Controllers\Admin\ReservationController::class, 'index'])->name('reservations.index');
        Route::get('/reservations/{id}', [\App\Http\Controllers\Admin\ReservationController::class, 'show'])->name('reservations.show');
        Route::post('/reservations/{id}/mark-ready', [\App\Http\Controllers\Admin\ReservationController::class, 'markReady'])->name('reservations.markReady');
        Route::post('/reservations/{id}/process-pickup', [\App\Http\Controllers\Admin\ReservationController::class, 'processPickup'])->name('reservations.processPickup');
        Route::post('/reservations/{id}/cancel', [\App\Http\Controllers\Admin\ReservationController::class, 'cancel'])->name('reservations.cancel');
        Route::post('/reservations/auto-cancel-expired', [\App\Http\Controllers\Admin\ReservationController::class, 'autoCancelExpired'])->name('reservations.autoCancelExpired');

        // Loans Management
        Route::get('/loans', [\App\Http\Controllers\Admin\LoanController::class, 'index'])->name('loans.index');
        Route::get('/loans/{id}', [\App\Http\Controllers\Admin\LoanController::class, 'show'])->name('loans.show');
        Route::post('/loans/{id}/return', [\App\Http\Controllers\Admin\LoanController::class, 'processReturn'])->name('loans.processReturn');
        Route::post('/loans/{id}/pay-fine', [\App\Http\Controllers\Admin\LoanController::class, 'payFine'])->name('loans.payFine');
        Route::post('/loans/{id}/waive-fine', [\App\Http\Controllers\Admin\LoanController::class, 'waiveFine'])->name('loans.waiveFine');
        Route::post('/loans/{id}/extend', [\App\Http\Controllers\Admin\LoanController::class, 'extend'])->name('loans.extend');
    });
});
