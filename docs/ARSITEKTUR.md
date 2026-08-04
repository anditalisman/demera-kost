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

## Layanan Domain (Living)

Logika bisnis bertahap/transaksional hidup di `app/Domain/Living/Services/`, dipanggil
dari controller lewat dependency injection — controller tetap tipis (validasi + panggil
service + render/redirect):

| Service | Tanggung jawab |
|---|---|
| `BookingLifecycleService` | `createHold()` (row-locked, mencegah double-booking), `expire()`, `confirm()` (booking → Tenant + Lease + Deposit) |
| `PaymentVerificationService` | `submitProof()`, `verify()` (memicu `confirm()` bila invoice lunas), `reject()` |
| `InvoiceService` | `generateMonthlyInvoice()` (idempotent, prorata otomatis), `markOverdue()` (denda, idempotent) |
| `LeaseManagementService` | `extend()`, `transferRoom()`, `terminate()` |
| `ReportService` | Menghasilkan seluruh 10 laporan admin sebagai struktur `{headings, rows}` seragam |

Operasi yang mengubah status kamar/booking/lease dan menyentuh lebih dari satu tabel
selalu dibungkus `DB::transaction()` dengan `lockForUpdate()` pada baris yang diperebutkan
(kamar, booking, lease) — pola ini mencegah race condition seperti dua pelanggan memesan
kamar yang sama secara bersamaan.

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

## Notifikasi

`App\Domain\Platform\Services\NotificationDispatcher::dispatch()` adalah satu-satunya
jalur pengiriman notifikasi. Setiap panggilan selalu menulis sebuah `Notification`
in-app (channel always-on, muncul di lonceng notifikasi kedua layout) plus
`NotificationLog`-nya; bila `NotificationTemplate` yang dipakai menargetkan channel
`whatsapp`/`email`, driver terkait (`LogWhatsAppDriver`/`LogEmailDriver`, implementasi
`NotificationChannelDriver`) juga menulis log pengirimannya sendiri. Kedua driver itu
adalah *placeholder* yang disengaja — `WHATSAPP_PROVIDER=log` berarti pesan dicatat
penuh ke `notification_logs` (termasuk alasan "belum ada provider asli dikonfigurasi")
tapi tidak benar-benar terkirim. Mengganti ke provider sungguhan berarti membuat
implementasi `NotificationChannelDriver` baru dan mengubah binding di
`NotificationDispatcher`'s constructor — tidak ada kode pemanggil (`BookingLifecycleService`,
`PaymentVerificationService`, `SendDueReminders` command) yang perlu berubah.

11 `NotificationTemplate` diseed di Tahap 1 dan seluruhnya sudah dipakai: konfirmasi
booking (`booking_confirmed`/`_wa`), verifikasi pembayaran (`payment_verified`),
pengingat tagihan H-7 s.d. H+7, dan kontrak akan berakhir. `SendDueReminders` (harian)
dan `RetryFailedNotifications` (tiap jam) adalah scheduled command yang memicu
pengiriman berkala.

## Pembayaran

Hanya dua metode aktif, keduanya manual, sesuai keputusan eksplisit user: **transfer
bank** (customer unggah bukti) dan **QRIS statis** (gambar diunggah admin lewat halaman
Pengaturan, customer memindai lalu tetap mengunggah bukti — diverifikasi dengan cara
yang sama seperti transfer bank). `PaymentVerificationService` menangani submit bukti
dan verifikasi/penolakan oleh admin; tidak ada payment gateway atau webhook yang
diintegrasikan. Kolom skema untuk gateway (`gateway_provider`, `gateway_transaction_id`,
`va_number`, tabel `payment_webhooks`) tetap ada di migration dari Tahap 1 tapi sengaja
tidak dipakai — bila suatu saat integrasi Midtrans/Xendit dibutuhkan, kolom itu sudah
siap tanpa migration tambahan.

`idempotency_key` unik pada `payments` tetap dipertahankan meski tanpa webhook, karena
berguna juga untuk mencegah duplikasi submit bukti dari sisi client.

## Docker vs Non-Docker

`Dockerfile` multi-stage (`vendor` → `frontend` → `php-base` → `dev`/`production`) dan
`docker-compose.yml` adalah cara utama menjalankan Demera di semua lingkungan (dev,
staging, produksi). Konfigurasi non-Docker (Nginx vhost bare-metal, Supervisor, cron)
disediakan di `deploy/` dan didokumentasikan di `docs/DEPLOYMENT.md` untuk server yang
tidak mendukung container.
