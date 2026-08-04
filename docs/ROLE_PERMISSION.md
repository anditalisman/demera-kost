# Role & Permission

Sumber kebenaran: `database/seeders/RolePermissionSeeder.php`. Permission bernama
`{modul}.{aksi}`, diperiksa lewat `$user->can('...')` dan ditegakkan di Policy
(`app/Policies/*`) serta di route/controller admin.

## 6 Role

| Role | Deskripsi | Cakupan |
|---|---|---|
| `super-admin` | Pemilik sistem | **Seluruh permission**, termasuk yang tidak dimiliki `admin` (users.manage, settings.manage) |
| `admin` | Staf operasional harian | Konten, lihat pengguna, lihat pengaturan, kelola kamar/booking/penyewa/kontrak/perawatan, lihat tagihan/pembayaran, laporan, audit log |
| `property-manager` | Pengelola kost | Hanya operasional kamar: rooms, bookings, tenants, leases, maintenance, lihat laporan |
| `finance` | Tim keuangan | Hanya uang: invoices, payments, lihat & ekspor laporan |
| `customer` | Calon penyewa (default saat registrasi) | Tidak ada permission admin — akses lewat dashboard pengguna biasa |
| `tenant` | Penyewa aktif | Sama seperti customer secara permission; dibedakan untuk keperluan tampilan/status di masa depan (kontrak aktif, dsb.) |

## Matriks Permission

| Permission | super-admin | admin | property-manager | finance |
|---|:---:|:---:|:---:|:---:|
| `users.view` | ✅ | ✅ | | |
| `users.manage` | ✅ | | | |
| `content.view` | ✅ | | | |
| `content.manage` | ✅ | ✅ | | |
| `settings.view` | ✅ | ✅ | | |
| `settings.manage` | ✅ | | | |
| `rooms.view` / `rooms.manage` | ✅ | ✅ | ✅ | |
| `bookings.view` / `bookings.manage` | ✅ | ✅ | ✅ | |
| `tenants.view` / `tenants.manage` | ✅ | ✅ | ✅ | |
| `leases.view` / `leases.manage` | ✅ | ✅ | ✅ | |
| `maintenance.view` / `maintenance.manage` | ✅ | ✅ | ✅ | |
| `invoices.view` | ✅ | ✅ | | |
| `invoices.manage` | ✅ | | | ✅ |
| `payments.view` | ✅ | ✅ | | |
| `payments.verify` / `payments.manage` | ✅ | | | ✅ |
| `reports.view` | ✅ | ✅ | ✅ | ✅ |
| `reports.export` | ✅ | ✅ | | ✅ |
| `audit-logs.view` | ✅ | ✅ | | |

`customer` dan `tenant` tidak memegang permission apa pun di atas — keduanya diverifikasi
lewat test otomatis (`tests/Feature/Admin/RolePermissionMatrixTest.php`).

## Cara kerja penegakan

1. **Policy** — setiap resource admin (ContentPage, Gallery, Testimonial, Faq,
   ApplicationSetting, User, AuditLog) punya Policy class yang memeriksa permission di
   atas. Controller memanggil `$this->authorize(...)` sebelum melakukan aksi apa pun.
2. **Halaman 403, bukan redirect diam-diam** — bila `authorize()` gagal, Laravel
   melempar `AuthorizationException` → HTTP 403. Diuji eksplisit di
   `RolePermissionMatrixTest::test_admin_area_routes_reject_roles_without_the_matching_permission`.
3. **Nav yang jujur** — `AdminLayout.vue` memfilter item menu berdasar
   `page.props.auth.permissions` (dikirim lewat `HandleInertiaRequests`), sehingga user
   tidak pernah melihat tautan ke halaman yang akan menolaknya.
4. **Role baru/berubah** — perubahan role lewat `/admin/users` tercatat ke `audit_logs`
   (aksi `role_changed`) dengan role lama & baru.

## Menambah permission baru (Tahap 2+)

Tambahkan entri baru ke `RolePermissionSeeder::PERMISSIONS` dan masukkan ke role yang
sesuai di `RolePermissionSeeder::ROLES`, lalu jalankan ulang seeder (`syncPermissions`
bersifat idempotent — aman dijalankan berkali-kali). Jangan mengedit migration
`create_permission_tables` yang sudah dipakai; skema tabel `roles`/`permissions` dari
`spatie/laravel-permission` sudah final untuk seluruh tahap.
