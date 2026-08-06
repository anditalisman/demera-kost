<?php

use App\Http\Controllers\Admin\ApplicationSettingController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\BuildingController;
use App\Http\Controllers\Admin\ContentPageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FacilityController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\FloorController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\LeaseController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PropertyController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\RoomTypeController;
use App\Http\Controllers\Admin\TenantController;
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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

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
    Route::post('/settings/qris', [ApplicationSettingController::class, 'uploadQris'])->name('settings.qris.upload');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::put('/users/{user}/roles', [UserController::class, 'updateRoles'])->name('users.roles.update');
    Route::put('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
    Route::post('/properties', [PropertyController::class, 'store'])->name('properties.store');
    Route::put('/properties/{property}', [PropertyController::class, 'update'])->name('properties.update');
    Route::delete('/properties/{property}', [PropertyController::class, 'destroy'])->name('properties.destroy');

    Route::post('/properties/{property}/buildings', [BuildingController::class, 'store'])->name('buildings.store');
    Route::put('/buildings/{building}', [BuildingController::class, 'update'])->name('buildings.update');
    Route::delete('/buildings/{building}', [BuildingController::class, 'destroy'])->name('buildings.destroy');

    Route::post('/buildings/{building}/floors', [FloorController::class, 'store'])->name('floors.store');
    Route::put('/floors/{floor}', [FloorController::class, 'update'])->name('floors.update');
    Route::delete('/floors/{floor}', [FloorController::class, 'destroy'])->name('floors.destroy');

    Route::get('/room-types', [RoomTypeController::class, 'index'])->name('room-types.index');
    Route::post('/room-types', [RoomTypeController::class, 'store'])->name('room-types.store');
    Route::put('/room-types/{roomType}', [RoomTypeController::class, 'update'])->name('room-types.update');
    Route::delete('/room-types/{roomType}', [RoomTypeController::class, 'destroy'])->name('room-types.destroy');

    Route::get('/facilities', [FacilityController::class, 'index'])->name('facilities.index');
    Route::post('/facilities', [FacilityController::class, 'store'])->name('facilities.store');
    Route::put('/facilities/{facility}', [FacilityController::class, 'update'])->name('facilities.update');
    Route::delete('/facilities/{facility}', [FacilityController::class, 'destroy'])->name('facilities.destroy');

    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
    Route::get('/rooms/create', [RoomController::class, 'create'])->name('rooms.create');
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::get('/rooms/{room}/edit', [RoomController::class, 'edit'])->name('rooms.edit');
    Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
    Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');
    Route::put('/rooms/{room}/facilities', [RoomController::class, 'updateFacilities'])->name('rooms.facilities.update');
    Route::put('/rooms/{room}/status', [RoomController::class, 'updateStatus'])->name('rooms.status.update');
    Route::post('/rooms/bulk-status', [RoomController::class, 'bulkStatus'])->name('rooms.bulk-status');
    Route::post('/rooms/{room}/photos', [RoomController::class, 'storePhoto'])->name('rooms.photos.store');
    Route::put('/rooms/{room}/photos/reorder', [RoomController::class, 'reorderPhotos'])->name('rooms.photos.reorder');
    Route::put('/rooms/{room}/photos/{roomImage}/primary', [RoomController::class, 'setPrimaryPhoto'])->name('rooms.photos.primary');
    Route::delete('/rooms/{room}/photos/{roomImage}', [RoomController::class, 'destroyPhoto'])->name('rooms.photos.destroy');

    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');

    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{payment}/proof', [PaymentController::class, 'proof'])->name('payments.proof');
    Route::put('/payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
    Route::put('/payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');

    Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
    Route::get('/tenants/{tenant}', [TenantController::class, 'show'])->name('tenants.show');

    Route::get('/leases', [LeaseController::class, 'index'])->name('leases.index');
    Route::get('/leases/{lease}', [LeaseController::class, 'show'])->name('leases.show');
    Route::post('/leases/{lease}/extend', [LeaseController::class, 'extend'])->name('leases.extend');
    Route::post('/leases/{lease}/transfer', [LeaseController::class, 'transferRoom'])->name('leases.transfer');
    Route::post('/leases/{lease}/terminate', [LeaseController::class, 'terminate'])->name('leases.terminate');

    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::put('/bookings/{booking}/approve', [BookingController::class, 'approve'])->name('bookings.approve');
    Route::put('/bookings/{booking}/reject', [BookingController::class, 'reject'])->name('bookings.reject');

    Route::get('/maintenance-requests', [MaintenanceController::class, 'index'])->name('maintenance-requests.index');
    Route::get('/maintenance-requests/{maintenanceRequest}', [MaintenanceController::class, 'show'])->name('maintenance-requests.show');
    Route::put('/maintenance-requests/{maintenanceRequest}/status', [MaintenanceController::class, 'updateStatus'])->name('maintenance-requests.status.update');
    Route::post('/maintenance-requests/{maintenanceRequest}/comments', [MaintenanceController::class, 'storeComment'])->name('maintenance-requests.comments.store');
});

Route::middleware(['auth', 'verified'])->prefix('account')->name('customer.')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard/Customer/Dashboard');
    })->name('dashboard');
});
