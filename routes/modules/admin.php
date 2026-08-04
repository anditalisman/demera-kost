<?php

use App\Http\Controllers\Admin\ApplicationSettingController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ContentPageController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Admin & customer dashboard routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard/Admin/Dashboard');
    })->name('dashboard');

    Route::get('/content/pages', [ContentPageController::class, 'index'])->name('content-pages.index');
    Route::post('/content/pages', [ContentPageController::class, 'store'])->name('content-pages.store');
    Route::put('/content/pages/{contentPage}', [ContentPageController::class, 'update'])->name('content-pages.update');
    Route::delete('/content/pages/{contentPage}', [ContentPageController::class, 'destroy'])->name('content-pages.destroy');

    Route::get('/content/galleries', [GalleryController::class, 'index'])->name('galleries.index');
    Route::post('/content/galleries', [GalleryController::class, 'store'])->name('galleries.store');
    Route::put('/content/galleries/{gallery}', [GalleryController::class, 'update'])->name('galleries.update');
    Route::post('/content/galleries/reorder', [GalleryController::class, 'reorder'])->name('galleries.reorder');
    Route::delete('/content/galleries/{gallery}', [GalleryController::class, 'destroy'])->name('galleries.destroy');

    Route::get('/content/testimonials', [TestimonialController::class, 'index'])->name('testimonials.index');
    Route::post('/content/testimonials', [TestimonialController::class, 'store'])->name('testimonials.store');
    Route::put('/content/testimonials/{testimonial}', [TestimonialController::class, 'update'])->name('testimonials.update');
    Route::delete('/content/testimonials/{testimonial}', [TestimonialController::class, 'destroy'])->name('testimonials.destroy');

    Route::get('/content/faqs', [FaqController::class, 'index'])->name('faqs.index');
    Route::post('/content/faqs', [FaqController::class, 'store'])->name('faqs.store');
    Route::put('/content/faqs/{faq}', [FaqController::class, 'update'])->name('faqs.update');
    Route::delete('/content/faqs/{faq}', [FaqController::class, 'destroy'])->name('faqs.destroy');

    Route::get('/settings', [ApplicationSettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [ApplicationSettingController::class, 'update'])->name('settings.update');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::put('/users/{user}/roles', [UserController::class, 'updateRoles'])->name('users.roles.update');
    Route::put('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');

    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
});

Route::middleware(['auth', 'verified'])->prefix('account')->name('customer.')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard/Customer/Dashboard');
    })->name('dashboard');
});
