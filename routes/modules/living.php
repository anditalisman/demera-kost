<?php

use App\Http\Controllers\Living\LivingController;
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
});
