<?php

use App\Http\Controllers\Fashion\FashionSubscriberController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public JSON API — Demera Fashion
|--------------------------------------------------------------------------
*/

Route::post('/fashion/subscribe', [FashionSubscriberController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('fashion.subscribe');
