<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\PolicyPageController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Platform routes (Tim 1: auth, landing page, dashboard shell, profile)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Public/Landing');
})->name('landing');

Route::get('/kebijakan/{slug}', [PolicyPageController::class, 'show'])->name('policies.show');

Route::get('/dashboard', function () {
    $user = auth()->user();

    return match (true) {
        $user->hasAnyRole(['super-admin', 'admin', 'property-manager', 'finance']) => redirect()->route('admin.dashboard'),
        default => redirect()->route('customer.dashboard'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/../auth.php';
