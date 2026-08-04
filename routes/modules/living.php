<?php

use App\Http\Controllers\Living\BookingController;
use App\Http\Controllers\Living\InvoiceController;
use App\Http\Controllers\Living\LivingController;
use App\Http\Controllers\Living\PaymentController;
use App\Http\Controllers\Living\RoomController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Demera Living public routes
|--------------------------------------------------------------------------
*/

Route::prefix('living')->name('living.')->group(function () {
    Route::get('/', [LivingController::class, 'index'])->name('index');
    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
    Route::get('/rooms/{slug}', [RoomController::class, 'show'])->name('rooms.show');
    Route::get('/gallery', [LivingController::class, 'gallery'])->name('gallery');
    Route::get('/facilities', [LivingController::class, 'facilities'])->name('facilities');
    Route::get('/location', [LivingController::class, 'location'])->name('location');
    Route::get('/faq', [LivingController::class, 'faq'])->name('faq');
    Route::get('/contact', [LivingController::class, 'contact'])->name('contact');

    Route::middleware(['auth', 'verified'])->get('/rooms/{slug}/book', [BookingController::class, 'create'])->name('rooms.book');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{code}', [BookingController::class, 'show'])->name('bookings.show');

    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');

    Route::get('/invoices/{invoice}/pay', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/invoices/{invoice}/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
});
