<?php

namespace App\Providers;

use App\Domain\Living\Models\Booking;
use App\Domain\Living\Models\Building;
use App\Domain\Living\Models\Facility;
use App\Domain\Living\Models\Floor;
use App\Domain\Living\Models\Invoice;
use App\Domain\Living\Models\Lease;
use App\Domain\Living\Models\MaintenanceRequest;
use App\Domain\Living\Models\Payment;
use App\Domain\Living\Models\Property;
use App\Domain\Living\Models\Room;
use App\Domain\Living\Models\RoomType;
use App\Domain\Living\Models\Tenant;
use App\Domain\Platform\Models\ApplicationSetting;
use App\Domain\Platform\Models\AuditLog;
use App\Domain\Platform\Models\ContentPage;
use App\Domain\Platform\Models\Faq;
use App\Domain\Platform\Models\Gallery;
use App\Domain\Platform\Models\Testimonial;
use App\Models\User;
use App\Policies\ApplicationSettingPolicy;
use App\Policies\AuditLogPolicy;
use App\Policies\ContentPagePolicy;
use App\Policies\FaqPolicy;
use App\Policies\GalleryPolicy;
use App\Policies\BookingPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\LeasePolicy;
use App\Policies\MaintenanceRequestPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\RoomManagementPolicy;
use App\Policies\TenantPolicy;
use App\Policies\TestimonialPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(ContentPage::class, ContentPagePolicy::class);
        Gate::policy(Gallery::class, GalleryPolicy::class);
        Gate::policy(Testimonial::class, TestimonialPolicy::class);
        Gate::policy(Faq::class, FaqPolicy::class);
        Gate::policy(ApplicationSetting::class, ApplicationSettingPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);

        Gate::policy(Property::class, RoomManagementPolicy::class);
        Gate::policy(Building::class, RoomManagementPolicy::class);
        Gate::policy(Floor::class, RoomManagementPolicy::class);
        Gate::policy(RoomType::class, RoomManagementPolicy::class);
        Gate::policy(Room::class, RoomManagementPolicy::class);
        Gate::policy(Facility::class, RoomManagementPolicy::class);
        Gate::policy(Booking::class, BookingPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Tenant::class, TenantPolicy::class);
        Gate::policy(Lease::class, LeasePolicy::class);
        Gate::policy(MaintenanceRequest::class, MaintenanceRequestPolicy::class);
    }
}
