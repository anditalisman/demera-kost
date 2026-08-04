# Struktur Folder

```
app/
  Domain/
    Platform/
      Models/        ContentPage, Gallery, Testimonial, Faq, ApplicationSetting,
                      AuditLog, NotificationTemplate, Notification, NotificationLog
      Services/       ImageUploadService, PrivateDocumentUploadService,
                      PrivateDocumentUrlService, AuditLogger, PlaceholderImageGenerator
      Concerns/       Auditable (trait — auto-logs create/update/delete to audit_logs)
    Living/
      Models/        Property, Building, Floor, RoomType, Room, RoomImage, Facility,
                      RoomStatusHistory, Booking, BookingGuest, BookingDocument, Tenant,
                      Lease, LeaseExtension, Invoice, InvoiceItem, Payment, PaymentWebhook,
                      Refund, Deposit, MaintenanceRequest, MaintenanceAttachment
    Fashion/
      Models/        FashionLaunchSubscriber (only Fashion table in Tahap 1 — kept
                      isolated from Living per the brief's "jangan mencampurkan data" rule)

  Models/            User, UserProfile — kept at the Laravel-conventional path (not under
                      Domain/Platform) because Sanctum, spatie/laravel-permission, and the
                      auth scaffold all assume App\Models\User by convention.

  Enums/             RoomStatus, BookingStatus, InvoiceStatus, PaymentStatus, PaymentMethod,
                      LeaseStatus, MaintenanceStatus, MaintenancePriority, NotificationChannel,
                      NotificationDeliveryStatus, RefundStatus — PHP backed enums, each with
                      a label() method for Indonesian display text.

  Http/
    Controllers/
      Admin/          CMS + user/settings/audit-log management (role: admin)
      Auth/           Breeze-derived, customized for WhatsApp login + force-password-change
      Api/            Public JSON API v1 controllers (Room, ...)
      Fashion/        Coming-soon pages + launch subscription
      Living/         Public catalog + room detail
      Public/         Landing page, static policy pages
      ProfileController.php  (shared across every role)
    Middleware/       HandleInertiaRequests, EnsurePasswordIsNotExpired
    Requests/         Auth\LoginRequest, ProfileUpdateRequest

  Policies/           One per admin-managed resource (ContentPage, Gallery, Testimonial,
                      Faq, ApplicationSetting, User, AuditLog) — registered explicitly via
                      Gate::policy() in AppServiceProvider because Laravel's auto-discovery
                      convention (App\Policies\{Model}Policy for App\Models\{Model}) doesn't
                      reach into the App\Domain\* namespace.

  Listeners/          Auth event listeners (login/failed-login/logout/registered) that feed
                      the audit log — auto-discovered by Laravel, no manual registration.

  OpenApi/            PHP attribute classes holding shared OpenAPI metadata (#[OA\Info],
                      reusable #[OA\Schema] definitions) that don't belong on any one controller.

routes/
  web.php             Just requires the module files below, in order.
  modules/
    platform.php      Landing route, dashboard redirect, profile, policy pages, requires auth.php
    living.php        /living/* public routes
    fashion.php        /fashion/* public routes
    admin.php         /admin/* and /account/* (customer) routes
    api_living.php     /api/v1/living/* (required from routes/api.php)
    api_fashion.php    /api/v1/fashion/* (required from routes/api.php)
  auth.php            Breeze-derived auth routes (register/login/verify/reset/logout/
                      other-browser-sessions/force-password-update)
  api.php             /api/user (Sanctum) + requires the api_* module files under /api/v1

database/
  migrations/          One table per file, timestamp-ordered so FK dependencies resolve
                      (e.g. bookings before the FK is added to room_status_histories).
  factories/          Mirrors each model's namespace exactly (e.g.
                      database/factories/Domain/Living/Models/RoomFactory.php with
                      namespace Database\Factories\Domain\Living\Models) — this is required
                      for Laravel's default factory-name resolver to find them automatically;
                      it does NOT special-case models outside App\Models.
  seeders/            Chained from DatabaseSeeder in dependency order — see
                      docs/ROADMAP.md's "Data Awal" section for what each one creates.

resources/js/
  Pages/
    Public/           Landing, Policy (ToS/privacy/payment-policy — CMS-driven)
    Living/           Public Living pages, Rooms/Index + Rooms/Show
    Fashion/          ComingSoon
    Auth/             Login, Register, ForcePasswordUpdate, + Breeze defaults
    Profile/          Edit + Partials (profile info, password, logout-other-devices, delete)
    Dashboard/
      Admin/          Dashboard, Content/{Pages,Galleries,Testimonials,Faqs,Settings},
                      Users, AuditLogs
      Customer/       Dashboard
  Layouts/            PublicLayout (marketing pages), AdminLayout (sidebar + permission-aware
                      nav), CustomerLayout, AuthenticatedLayout (shared account-settings shell,
                      used only by /profile), GuestLayout (auth forms)
  Components/
    layout/           PublicHeader, PublicFooter, WhatsAppFloatingButton
    ui primitives      PrimaryButton, SecondaryButton, TextInput, InputLabel, InputError,
                      Checkbox, Modal, Pagination, ApplicationLogo (all restyled to the
                      Demera palette — not stock Breeze colors)
  lib/                whatsapp.ts (wa.me link builder), roomStatus.ts (status labels/colors/
                      IDR currency formatter)
  types/              index.d.ts — shared Inertia PageProps shape (auth, flash, settings)

docker/
  php/                php.ini, opcache.ini overrides
  nginx/              default.conf (Docker reverse proxy to php-fpm)
  supervisor/         supervisord.conf (production image: queue workers + scheduler loop)

deploy/               Non-Docker deployment artifacts — see docs/DEPLOYMENT.md
  nginx/
  supervisor/

docs/                 You are here.
```

## Mengapa `App\Domain\*` dan bukan `nwidart/laravel-modules`?

Modularitas dicapai lewat namespace PSR-4 biasa (`App\Domain\Living`, `App\Domain\Fashion`,
`App\Domain\Platform`) tanpa package modul tambahan. Alasannya:

- Tidak butuh service provider/autoload terpisah per modul — `composer.json` sudah memetakan
  seluruh `App\` ke `app/`, jadi sub-namespace apa pun otomatis ter-autoload.
- Aturan brief "jangan mencampurkan data Fashion dan Living" ditegakkan lewat migration,
  model, dan route yang benar-benar terpisah per domain — bukan lewat isolasi package.
- Menambah modul Fashion penuh (produk, kategori, keranjang) di tahap berikutnya berarti
  menambah folder baru di `app/Domain/Fashion/`, tanpa menyentuh `Living` atau `Platform`.
