<?php

use App\Http\Controllers\Fashion\FashionController;
use App\Http\Controllers\Fashion\FashionSubscriberController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Demera Fashion routes ("Segera Hadir" for Tahap 1)
|--------------------------------------------------------------------------
*/

Route::prefix('fashion')->name('fashion.')->group(function () {
    Route::get('/', [FashionController::class, 'index'])->name('index');
    Route::get('/products', [FashionController::class, 'products'])->name('products');
    Route::get('/categories', [FashionController::class, 'categories'])->name('categories');
    Route::get('/product/{slug}', [FashionController::class, 'product'])->name('product.show');

    Route::post('/subscribe', [FashionSubscriberController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('subscribe');
});
