# Demera — Fashion & Living

Platform dua lini bisnis Demera: **Demera Living** (katalog, pemesanan, dan pengelolaan
kamar kost) dan **Demera Fashion** (fashion editorial — halaman "Segera Hadir" di tahap
ini). Domain produksi: `https://demera.my.id`.

**Status**: Tahap 1 (Fondasi) selesai dan production-ready untuk cakupannya. Lihat
[`docs/ROADMAP.md`](docs/ROADMAP.md) untuk apa yang sudah ada vs. yang menyusul di
Tahap 2–7.

## Stack

Laravel 13 (PHP 8.4) · Inertia.js + Vue 3 (TypeScript) · Tailwind CSS · MySQL 8 · Redis ·
S3-compatible object storage (MinIO/AWS S3) · Sanctum · `spatie/laravel-permission` ·
`darkaonline/l5-swagger`. Detail lengkap di [`docs/ARSITEKTUR.md`](docs/ARSITEKTUR.md).

## Mulai Cepat (Docker)

```bash
cp .env.example .env
docker compose build app
docker compose up -d
docker compose exec app php artisan key:generate
docker compose exec app composer install
docker compose exec app php artisan migrate --seed
npm install && npm run build
```

Buka `http://localhost` (atau port di `APP_PORT` bila 80 sudah dipakai proses lain).
Login dengan kredensial dari `SUPERADMIN_EMAIL`/`SUPERADMIN_PASSWORD` di `.env` — Anda
akan diminta mengganti password pada login pertama.

Akun demo lain (password: `password`, kecuali super-admin):

| Email | Role |
|---|---|
| `admin@demera.my.id` | admin |
| `pengelola@demera.my.id` | property-manager |
| `finance@demera.my.id` | finance |
| `customer@demera.my.id` | customer |
| `penyewa1@demera.my.id`, `penyewa2@demera.my.id` | tenant (dengan kontrak aktif contoh) |

Panduan instalasi lengkap (termasuk non-Docker) dan checklist go-live produksi ada di
[`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md).

## Menjalankan Test

```bash
docker compose exec app vendor/bin/phpunit
```

## Dokumentasi

| Dokumen | Isi |
|---|---|
| [`docs/ARSITEKTUR.md`](docs/ARSITEKTUR.md) | Arsitektur sistem, stack, alur request, desain storage/RBAC/audit |
| [`docs/ERD.md`](docs/ERD.md) | Entity Relationship Diagram (seluruh 35 tabel) |
| [`docs/SITEMAP.md`](docs/SITEMAP.md) | Peta seluruh halaman & endpoint |
| [`docs/ALUR_PENGGUNA.md`](docs/ALUR_PENGGUNA.md) | Diagram alur pengguna utama |
| [`docs/ROLE_PERMISSION.md`](docs/ROLE_PERMISSION.md) | 6 role & matriks permission |
| [`docs/API_ENDPOINTS.md`](docs/API_ENDPOINTS.md) | Endpoint JSON API v1 publik |
| [`docs/STRUKTUR_FOLDER.md`](docs/STRUKTUR_FOLDER.md) | Struktur folder & alasan desainnya |
| [`docs/ROADMAP.md`](docs/ROADMAP.md) | Status Tahap 1 + rencana Tahap 2–7 |
| [`docs/KONFIGURASI.md`](docs/KONFIGURASI.md) | Konfigurasi MySQL/Redis/MinIO/Mail/WhatsApp/Payment Gateway |
| [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md) | Deployment Docker & non-Docker, backup/restore, checklist go-live |
| [`docs/PANDUAN_ADMIN.md`](docs/PANDUAN_ADMIN.md) | Panduan penggunaan untuk staf admin |

Dokumentasi API interaktif (OpenAPI/Swagger): `/api/documentation` setelah aplikasi
berjalan.

## Struktur Modular

```
app/Domain/Platform/   Autentikasi, CMS, pengaturan, audit log, notifikasi (dipakai bersama)
app/Domain/Living/     Properti → kamar → booking → penyewa → kontrak → tagihan → pembayaran
app/Domain/Fashion/    Terisolasi total dari Living — hanya form notifikasi peluncuran di Tahap 1
```

Tidak ada foreign key silang antara tabel Living dan Fashion — modul Fashion penuh
(katalog, keranjang, checkout) bisa ditambahkan di tahap berikutnya tanpa mengubah
Living. Detail di [`docs/STRUKTUR_FOLDER.md`](docs/STRUKTUR_FOLDER.md).

## Keamanan

Validasi frontend + backend, Policy per resource, CSRF/XSS protection bawaan Laravel,
query terparameter lewat Eloquent, rate limiting pada login/registrasi/subscribe,
password di-hash (bcrypt), MIME/ukuran file divalidasi dan nama file diacak saat
unggah, dokumen identitas hanya lewat signed URL berumur pendek (tidak pernah publik),
audit trail otomatis. Tidak ada nomor kartu atau data finansial sensitif yang disimpan
di aplikasi ini.
