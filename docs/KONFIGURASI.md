# Konfigurasi Layanan Eksternal

Semua nilai sensitif dikonfigurasi lewat environment variable (`.env`), tidak pernah
ditulis langsung di source code. Salin `.env.example` ke `.env` dan isi sesuai lingkungan
Anda — setiap variabel sudah dikomentari di file tersebut. Dokumen ini menjelaskan
**mengapa** masing-masing kelompok konfigurasi ada dan bagaimana memverifikasinya.

## MySQL 8

```
DB_CONNECTION=mysql
DB_HOST=mysql        # Docker: nama service. Non-Docker: host DB Anda.
DB_PORT=3306
DB_DATABASE=demera
DB_USERNAME=demera
DB_PASSWORD=<kuat, unik per lingkungan>
```

Verifikasi: `php artisan migrate:status` (Docker: `docker compose exec app php artisan
migrate:status`) harus menampilkan seluruh migration tanpa error koneksi.

## Redis (session, cache, queue)

```
REDIS_HOST=redis      # Docker: nama service. Non-Docker: host Redis Anda.
REDIS_PORT=6379
REDIS_PASSWORD=null    # Isi jika Redis Anda memakai auth (requirepass)
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
```

Demera memakai Redis untuk tiga hal sekaligus (session, cache, queue) — di produksi
dengan trafik tinggi, pertimbangkan `REDIS_CLIENT=phpredis` (default) dengan ekstensi
`redis` PHP native untuk performa terbaik (sudah termasuk di image PHP Docker kami).

Verifikasi: `php artisan tinker --execute="Illuminate\Support\Facades\Redis::ping();"`

## Object storage — MinIO (dev) / AWS S3 (prod)

```
OBJECT_STORAGE_KEY=...
OBJECT_STORAGE_SECRET=...
OBJECT_STORAGE_REGION=us-east-1
OBJECT_STORAGE_ENDPOINT=              # kosongkan untuk AWS S3 asli
OBJECT_STORAGE_ENDPOINT_PUBLIC_URL=   # kosongkan untuk AWS S3 asli
OBJECT_STORAGE_USE_PATH_STYLE=false   # true hanya untuk MinIO
OBJECT_STORAGE_BUCKET_PUBLIC=demera-public
OBJECT_STORAGE_BUCKET_PRIVATE=demera-private
```

Dua bucket wajib ada dan **tidak boleh ditukar**:

- `demera-public` — kebijakan akses publik (`mc anonymous set download`, sudah otomatis
  di `docker-compose.yml` lewat service `minio-init`). Untuk AWS S3 asli, gunakan bucket
  policy yang mengizinkan `s3:GetObject` publik, atau lebih aman: pasang CloudFront di
  depannya dan jangan buka bucket policy publik sama sekali.
- `demera-private` — **tidak pernah publik**. Diakses hanya lewat presigned URL berumur
  pendek dari `PrivateDocumentUrlService`.

Untuk AWS S3 asli, buat IAM user dengan policy minimal (hanya `s3:GetObject`,
`s3:PutObject`, `s3:DeleteObject` pada kedua bucket di atas — jangan pakai root
credentials).

Verifikasi: `php artisan tinker --execute="Illuminate\Support\Facades\Storage::disk('public_media')->put('test.txt','ok');"`
lalu cek file muncul di bucket.

## Email

```
MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=no-reply@demera.my.id
```

Default dev (`MAIL_MAILER=log`) menulis email ke `storage/logs/laravel.log` alih-alih
mengirim — berguna untuk melihat tautan verifikasi/reset password tanpa perlu SMTP asli
saat development.

## WhatsApp (fondasi Tahap 6)

```
WHATSAPP_PROVIDER=log   # Tahap 1: hanya mencatat, tidak mengirim
WHATSAPP_API_URL=
WHATSAPP_API_TOKEN=
WHATSAPP_SENDER_NUMBER=
```

`WHATSAPP_PROVIDER` adalah nama driver di belakang abstraction layer notifikasi (lihat
`docs/ARSITEKTUR.md`). Saat provider asli (Fonnte, WhatsApp Cloud API, dsb.)
diintegrasikan di Tahap 6, cukup tambah driver baru dan ganti nilai ini — tidak ada kode
bisnis yang perlu diubah.

## Payment Gateway (fondasi Tahap 4)

```
PAYMENT_GATEWAY_PROVIDER=manual   # Tahap 1: hanya transfer manual + verifikasi finance
# MIDTRANS_SERVER_KEY=
# MIDTRANS_CLIENT_KEY=
# MIDTRANS_IS_PRODUCTION=false
# MIDTRANS_WEBHOOK_SIGNATURE_KEY=
# XENDIT_SECRET_KEY=
# XENDIT_WEBHOOK_TOKEN=
```

Sama seperti WhatsApp, ini abstraction layer — integrasi Midtrans/Xendit di Tahap 4
tidak akan mengubah alur bisnis booking→invoice→payment yang sudah ada, hanya menambah
driver baru.

## Kredensial Super Admin

```
SUPERADMIN_NAME="Demera Super Admin"
SUPERADMIN_EMAIL=superadmin@demera.my.id
SUPERADMIN_WHATSAPP=+6281200000000
SUPERADMIN_PASSWORD=<isi password kuat sebelum seed produksi>
```

Dibaca sekali oleh `database/seeders/SuperAdminSeeder.php`. Akun yang dihasilkan
**wajib ganti password saat login pertama** (`must_change_password=true`) — nilai
`SUPERADMIN_PASSWORD` di atas hanya perlu bertahan sampai login pertama itu.
**Jangan gunakan password contoh dari `.env.example`/dokumentasi ini di produksi.**
