<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\LandingController;
use App\Http\Controllers\Public\PolicyPageController;
use App\Http\Controllers\Public\SeoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Platform routes (Tim 1: auth, landing page, dashboard shell, profile)
|--------------------------------------------------------------------------
*/

Route::get('/', [LandingController::class, 'show'])->name('landing');

Route::get('/kebijakan/{slug}', [PolicyPageController::class, 'show'])->name('policies.show');

Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');

Route::get('/dashboard', function () {
    return auth()->user()->hasRole('admin')
        ? redirect()->route('admin.dashboard')
        : redirect()->route('customer.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/../auth.php';
