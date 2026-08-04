<?php

use App\Http\Controllers\Api\RoomApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public JSON API — Demera Living
|--------------------------------------------------------------------------
*/

Route::get('/living/rooms', [RoomApiController::class, 'index'])->name('living.rooms.index');
Route::get('/living/rooms/{slug}', [RoomApiController::class, 'show'])->name('living.rooms.show');
