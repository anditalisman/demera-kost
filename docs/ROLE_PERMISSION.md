# Role & Permission

Sumber kebenaran: `database/seeders/RolePermissionSeeder.php`. Permission bernama
`{modul}.{aksi}`, diperiksa lewat `$user->can('...')` dan ditegakkan di Policy
(`app/Policies/*`) serta di route/controller admin.

## 2 Role

Disederhanakan dari desain awal 6-role menjadi 2 role sesuai kebutuhan operasional:

| Role | Deskripsi | Cakupan |
|---|---|---|
| `admin` | Staf pengelola (satu tingkat, tanpa sub-peran) | **Seluruh permission** — konten, pengguna, pengaturan, kamar, booking, penyewa, kontrak, tagihan, pembayaran, laporan, audit log |
| `customer` | Calon penyewa & penyewa aktif (default saat registrasi) | Tidak ada permission admin — akses lewat dashboard pengguna biasa. Status "calon" vs "penyewa aktif" dibedakan lewat data (`tenants.status`), bukan role terpisah |

Akun admin pertama (root) dibuat dari `SUPERADMIN_*` di `.env` lewat
`SuperAdminSeeder` — nama environment variable-nya dipertahankan (`SUPERADMIN_*`)
untuk kompatibilitas, meski rolenya sekarang bernama `admin`, bukan `super-admin`.

## Matriks Permission

| Permission | admin | customer |
|---|:---:|:---:|
| `users.view` / `users.manage` | ✅ | |
| `content.view` / `content.manage` | ✅ | |
| `settings.view` / `settings.manage` | ✅ | |
| `rooms.view` / `rooms.manage` | ✅ | |
| `bookings.view` / `bookings.manage` | ✅ | |
| `tenants.view` / `tenants.manage` | ✅ | |
| `leases.view` / `leases.manage` | ✅ | |
| `maintenance.view` / `maintenance.manage` | ✅ | |
| `invoices.view` / `invoices.manage` | ✅ | |
| `payments.view` / `payments.verify` / `payments.manage` | ✅ | |
| `reports.view` / `reports.export` | ✅ | |
| `audit-logs.view` | ✅ | |

`customer` tidak memegang permission apa pun di atas — permission granular tetap
dipertahankan di dalam kode (Policy per resource) walau saat ini hanya satu role
("admin") yang memegang semuanya lewat wildcard `syncPermissions(Permission::all())`
— ini sengaja, supaya bila di masa depan dibutuhkan sub-peran staf lagi (mis.
finance-only), tinggal menambah role baru dengan subset permission tanpa mengubah
satu pun Policy/controller yang sudah ada.

Diverifikasi lewat `tests/Feature/Admin/RolePermissionMatrixTest.php`.

## Migrasi dari desain 6-role

`RolePermissionSeeder` menyertakan langkah migrasi otomatis: bila database masih
punya user dengan role lama (`super-admin`, `property-manager`, `finance` → jadi
`admin`; `tenant` → jadi `customer`), seeder memetakan ulang role mereka lalu
menghapus role lama itu sendiri. Aman dijalankan berkali-kali (`php artisan
db:seed --class=RolePermissionSeeder`) baik di database baru maupun database yang
masih memakai skema role lama.

## Cara kerja penegakan

1. **Policy** — setiap resource admin punya Policy class yang memeriksa permission di
   atas: `ContentPage`, `Gallery`, `Testimonial`, `Faq`, `ApplicationSetting`, `User`,
   `AuditLog` (Tahap 1), `RoomManagementPolicy` (dipakai bersama oleh `Property`,
   `Building`, `Floor`, `RoomType`, `Room`, `Facility` — satu class karena aturannya
   identik), `BookingPolicy`, `InvoicePolicy`, `PaymentPolicy`, `TenantPolicy`,
   `LeasePolicy`, `MaintenanceRequestPolicy` (Tahap 2–5; beberapa di antaranya, seperti
   `BookingPolicy::view`, juga mengizinkan pemilik resource sendiri — bukan hanya
   admin — melihat booking/invoice/pembayaran/keluhan miliknya). Controller memanggil
   `$this->authorize(...)` sebelum melakukan aksi apa pun. Laporan (`/admin/reports`)
   dan dashboard admin tidak terikat ke satu Eloquent model, jadi diperiksa langsung
   lewat `$user->can('reports.view')`/`hasRole('admin')`, bukan lewat Policy class.
2. **Halaman 403, bukan redirect diam-diam** — bila `authorize()` gagal, Laravel
   melempar `AuthorizationException` → HTTP 403. Diuji eksplisit di
   `RolePermissionMatrixTest::test_admin_area_routes_reject_customers`.
3. **Nav yang jujur** — `AdminLayout.vue` memfilter item menu berdasar
   `page.props.auth.permissions` (dikirim lewat `HandleInertiaRequests`), sehingga user
   tidak pernah melihat tautan ke halaman yang akan menolaknya.
4. **Role baru/berubah** — perubahan role lewat `/admin/users` tercatat ke `audit_logs`
   (aksi `role_changed`) dengan role lama & baru.

## Menambah permission baru

Tambahkan entri baru ke `RolePermissionSeeder::PERMISSIONS`, lalu jalankan ulang
seeder (`syncPermissions` bersifat idempotent). `admin` otomatis mendapat permission
baru lewat wildcard; tidak perlu menyentuh daftar role. Jangan mengedit migration
`create_permission_tables` yang sudah dipakai; skema tabel `roles`/`permissions` dari
`spatie/laravel-permission` sudah final.
