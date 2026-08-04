# Arsitektur Sistem — Demera (Tahap 1)

## Ringkasan

Demera adalah aplikasi monolith Laravel yang me-render UI lewat Inertia.js + Vue 3
(TypeScript) — bukan SPA terpisah dengan REST API murni. Laravel tetap mengekspos
sebagian kecil JSON API publik berversi (`/api/v1/...`) untuk kebutuhan klien
eksternal/mobile di masa depan, tapi seluruh halaman web (publik, dashboard admin,
dashboard pengguna) dirender server-side lewat Inertia.

Keputusan ini diambil secara sadar (lihat riwayat percakapan perencanaan): pendekatan
Inertia dipilih dibanding SPA+API terpisah karena satu codebase lebih sederhana untuk
tim kecil, sementara kebutuhan dokumentasi OpenAPI dan kesiapan mobile tetap terpenuhi
lewat lapisan `/api/v1` yang independen dari halaman web.

## Stack

| Lapisan | Teknologi |
|---|---|
| Backend | Laravel 13 (PHP 8.4) |
| Frontend | Vue 3 + TypeScript, dirender via Inertia.js |
| Styling | Tailwind CSS v3 (design tokens kustom, lihat `tailwind.config.js`) |
| Database | MySQL 8 |
| Cache / Session / Queue | Redis |
| Object storage | S3-compatible (MinIO di dev, AWS S3 kompatibel di produksi) |
| Auth | Laravel session (web) + Sanctum (personal access token untuk API/mobile) |
| RBAC | `spatie/laravel-permission` |
| Image processing | `intervention/image` (kompresi WebP + thumbnail otomatis) |
| Dokumentasi API | `darkaonline/l5-swagger` (OpenAPI 3) |
| Web server | Nginx → PHP-FPM |

## Modularitas: Platform / Living / Fashion

Tiga domain bisnis dipisah lewat namespace `App\Domain\{Platform,Living,Fashion}`
(detail di `docs/STRUKTUR_FOLDER.md`). Aturan intinya:

- **Platform** memuat semua yang dipakai bersama: autentikasi, CMS, pengaturan aplikasi,
  audit log, notifikasi.
- **Living** memuat seluruh entitas bisnis kost: properti sampai pembayaran.
- **Fashion** di Tahap 1 sengaja minim — hanya `fashion_launch_subscribers` — supaya modul
  katalog/keranjang/checkout Fashion bisa ditambahkan di tahap berikutnya tanpa migrasi
  ulang atau menyentuh tabel Living.

Tidak ada foreign key silang antara tabel Living dan tabel Fashion.

## Alur Request (halaman web)

```
Browser → Nginx → PHP-FPM → Laravel Router → Controller
                                                 │
                                                 ├─ Eloquent Model (MySQL)
                                                 ├─ Policy (otorisasi per-resource)
                                                 ├─ Service (ImageUploadService, dll.)
                                                 └─ Inertia::render('Page/Component', $props)
                                                        │
                                                        ▼
                                          Inertia Middleware menyisipkan JSON props
                                          ke dalam HTML awal (SSR-like first load),
                                          lalu Vue app.ts mengambil alih di client
                                          untuk navigasi berikutnya (XHR + swap).
```

Props global yang dibagikan ke setiap halaman (lihat `app/Http/Middleware/
HandleInertiaRequests.php`): `auth` (user + roles + permissions + jumlah notifikasi
belum dibaca), `flash` (pesan sukses/error dari session), `settings` (kontak, media
sosial, SEO default — dibaca dari tabel `application_settings` lewat cache).

## Penyimpanan Objek: Dua Disk yang Tegas Terpisah

- **`public_media`** — foto kamar, galeri, foto testimoni. Bucket publik, dapat diakses
  langsung tanpa signature. Semua unggahan lewat `ImageUploadService`: validasi MIME/size,
  nama file acak (UUID, tidak pernah nama asli klien), resize + kompresi WebP, generate
  thumbnail.
- **`private_documents`** — dokumen identitas, kontrak, bukti pembayaran (dipakai mulai
  Tahap 3–4). Bucket privat, hanya bisa diakses lewat *signed URL* berumur pendek
  (`PrivateDocumentUrlService`). Nginx juga secara eksplisit memblokir akses langsung ke
  `/storage/private/*` sebagai lapisan pertahanan kedua.

Di lingkungan dev, MinIO diakses dari dalam container lewat hostname internal Docker
(`http://minio:9000`), tapi presigned URL harus bisa dibuka browser pengguna — karena itu
`PrivateDocumentUrlService` menulis ulang host presigned URL ke endpoint publik
(`OBJECT_STORAGE_ENDPOINT_PUBLIC_URL`) saat kedua nilai itu berbeda. Di produksi dengan AWS
S3 asli, nilai itu dikosongkan sehingga tidak ada penulisan ulang.

## RBAC

2 role (`admin`, `customer`) dengan
matriks permission bergaya `{modul}.{aksi}` (lihat `docs/ROLE_PERMISSION.md` untuk daftar
lengkap). Diberlakukan di tiga lapis:

1. **Policy class** (`app/Policies/*`) — dipanggil lewat `$this->authorize()` di controller.
2. **Nav yang sadar permission** — `AdminLayout.vue` hanya merender tautan ke fitur yang
   benar-benar bisa diakses user (tidak ada menu dummy).
3. **Middleware `role`/`permission`** dari spatie — tersedia sebagai alias, dipakai bila
   suatu route perlu proteksi langsung tanpa lewat controller/policy.

## Audit Log

`app/Domain/Platform/Concerns/Auditable.php` — trait yang di-`use` pada model CMS
(ContentPage, Gallery, Testimonial, Faq, ApplicationSetting) sehingga setiap create/update/
delete otomatis tercatat ke `audit_logs` lewat `AuditLogger::log()` (best-effort, tidak
pernah melempar exception yang membatalkan request). Event login/logout/registrasi/login
gagal dicatat lewat listener Laravel standar (`app/Listeners/*`) yang otomatis terdeteksi
tanpa registrasi manual. Perubahan role dan aktivasi/nonaktivasi user dicatat manual di
`UserController` (sengaja tidak lewat trait otomatis, supaya hash password User tidak
pernah ikut tersimpan di kolom `old_values`/`new_values`).

## Notifikasi (fondasi untuk Tahap 6)

Skema `notification_templates`, `notifications`, `notification_logs` sudah lengkap di
Tahap 1, termasuk 11 template siap pakai (pengingat H-7 s.d. H+7, kontrak akan berakhir,
konfirmasi booking, verifikasi pembayaran). Pengiriman sungguhan (WhatsApp/email) belum
diaktifkan — `WHATSAPP_PROVIDER=log` di Tahap 1 berarti pesan hanya ditulis ke log, bukan
dikirim. Ini murni keputusan cakupan Tahap 1, bukan keterbatasan desain: provider asli
tinggal diplug lewat `WHATSAPP_PROVIDER` env var dan sebuah implementasi baru dari
kontrak provider (belum dibuat — akan hadir di Tahap 6).

## Pembayaran (fondasi untuk Tahap 4)

Skema (`invoices`, `invoice_items`, `payments`, `payment_webhooks`, `refunds`, `deposits`)
sudah lengkap dengan `idempotency_key` unik pada `payments` dan tabel `payment_webhooks`
terpisah untuk audit trail webhook mentah. `PAYMENT_GATEWAY_PROVIDER=manual` di Tahap 1
berarti hanya alur transfer manual + QRIS (gambar diunggah admin) + verifikasi admin
yang aktif; integrasi Midtrans/
Xendit menyusul di Tahap 4 lewat abstraction layer yang sama.

## Docker vs Non-Docker

`Dockerfile` multi-stage (`vendor` → `frontend` → `php-base` → `dev`/`production`) dan
`docker-compose.yml` adalah cara utama menjalankan Demera di semua lingkungan (dev,
staging, produksi). Konfigurasi non-Docker (Nginx vhost bare-metal, Supervisor, cron)
disediakan di `deploy/` dan didokumentasikan di `docs/DEPLOYMENT.md` untuk server yang
tidak mendukung container.
