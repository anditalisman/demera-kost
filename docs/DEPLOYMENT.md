# Panduan Deployment

Dua jalur deployment didukung: **Docker** (direkomendasikan, dan yang divalidasi
langsung selama pengembangan Tahap 1) dan **non-Docker** (bare-metal/VM tradisional).
Keduanya memakai `.env` yang sama — lihat `docs/KONFIGURASI.md` untuk arti tiap variabel.

## Opsi A — Docker (direkomendasikan)

### Development

```bash
cp .env.example .env
# isi minimal: APP_KEY (lihat di bawah), SUPERADMIN_PASSWORD

docker compose build app
docker compose up -d
docker compose exec app php artisan key:generate
docker compose exec app composer install
docker compose exec app php artisan migrate --seed
npm install && npm run build   # atau: docker compose --profile dev-assets up -d vite
```

Akses aplikasi di `http://localhost` (atau `${APP_PORT}` bila Anda mengubahnya di
`.env` — port `80` sudah dipakai proses lain di sebagian mesin dev).

### Production

`Dockerfile` punya target `production` terpisah dari `dev`: sudah menjalankan
`composer install --no-dev`, `npm run build`, `l5-swagger:generate`, dan `storage:link`
saat build image, serta menjalankan sebagai user non-root (`www-data`) dengan
Supervisor mengelola queue worker + scheduler dalam satu container
(`docker/supervisor/supervisord.conf`).

```bash
docker build --target production -t demera/app:latest .
```

Untuk produksi, jalankan container `production` (bukan `dev`) sebagai pengganti service
`app`/`queue`/`scheduler` di `docker-compose.yml`, dengan `.env` produksi (kredensial
asli, `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true` di belakang
HTTPS). Nginx tetap mem-proxy ke container ini di port `9000` seperti di dev.

Setelah deploy image baru:

```bash
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

## Opsi B — Non-Docker (bare-metal / VM)

Prasyarat: PHP 8.4-FPM + ekstensi (`pdo_mysql mbstring exif pcntl bcmath intl zip gd
redis opcache` — daftar identik dengan `Dockerfile`), Composer 2, Node 20 (hanya untuk
build aset, tidak perlu di server produksi bila build dilakukan di CI), MySQL 8, Redis,
Nginx, Supervisor.

```bash
git clone <repo> /var/www/demera && cd /var/www/demera
cp .env.example .env
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan key:generate
php artisan storage:link
php artisan l5-swagger:generate
php artisan migrate --force --seed   # --seed hanya untuk deploy pertama kali
php artisan config:cache && php artisan route:cache && php artisan view:cache

chown -R www-data:www-data storage bootstrap/cache
```

**Nginx** — salin `deploy/nginx/demera.conf` ke `/etc/nginx/sites-available/`, sesuaikan
path sertifikat TLS dan socket PHP-FPM, lalu:

```bash
ln -s /etc/nginx/sites-available/demera.conf /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx
```

**Queue worker** — salin `deploy/supervisor/demera-queue.conf` ke
`/etc/supervisor/conf.d/`, lalu:

```bash
supervisorctl reread && supervisorctl update && supervisorctl start demera-queue:*
```

**Scheduler** — Laravel tidak butuh proses long-running untuk scheduler di luar Docker;
cukup satu entri cron. Salin isi `deploy/systemd/demera-scheduler.cron` ke crontab user
`www-data` (`crontab -u www-data -e`) atau ke `/etc/cron.d/demera-scheduler`.

## Backup & Restore

### Database

```bash
# Backup
mysqldump --single-transaction -u demera -p demera > backup_$(date +%Y%m%d_%H%M%S).sql

# Restore
mysql -u demera -p demera < backup_20260101_020000.sql
```

Docker: jalankan perintah yang sama lewat `docker compose exec mysql mysqldump ...` /
`docker compose exec -T mysql mysql ...`. Jadwalkan backup harian lewat cron terpisah
(di luar aplikasi) yang memanggil `mysqldump` dan mengunggah hasilnya ke penyimpanan
terpisah dari server aplikasi (mis. bucket S3 lain, bukan `demera-public`/`demera-private`).

### Object storage (MinIO/S3)

MinIO: gunakan `mc mirror` untuk menyalin bucket ke lokasi backup:

```bash
mc mirror local/demera-public  backup-target/demera-public
mc mirror local/demera-private backup-target/demera-private
```

AWS S3: aktifkan **versioning** pada kedua bucket dan replikasi lintas region
(`S3 Cross-Region Replication`) sebagai pengganti backup manual berkala.

### Kebijakan retensi data

- Dokumen identitas & bukti pembayaran (`private_documents`): retensi mengikuti masa
  sewa penyewa + periode wajib simpan sesuai kebijakan internal — hapus lewat proses
  terjadwal, bukan manual, saat masa retensi berakhir (akan diimplementasikan bersama
  fitur booking/pembayaran di Tahap 3–4).
- `audit_logs`: tidak di-soft-delete (log tidak pernah diedit/dihapus lewat aplikasi);
  kebijakan pemangkasan log lama (mis. arsip setelah 2 tahun) adalah keputusan
  operasional, dijalankan lewat job terjadwal terpisah bila diperlukan.

## Checklist go-live

- [ ] `.env` produksi: `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` unik,
      `SESSION_SECURE_COOKIE=true`
- [ ] `SUPERADMIN_PASSWORD` diganti dari nilai contoh, dan diganti lagi lewat UI saat
      login pertama
- [ ] `DB_PASSWORD`, `MINIO_ROOT_PASSWORD`/`OBJECT_STORAGE_SECRET`, `REDIS_PASSWORD`
      seluruhnya unik dan kuat — tidak ada yang memakai nilai dari `.env.example`
- [ ] Bucket `demera-private` diverifikasi **tidak** publik (`curl` langsung ke objek di
      dalamnya harus mengembalikan 403)
- [ ] TLS aktif, `demera.my.id` mengarah ke server, HSTS aktif
- [ ] `php artisan migrate --force` sudah dijalankan, `php artisan test` hijau di CI
      sebelum deploy
- [ ] Queue worker dan scheduler berjalan (cek `supervisorctl status` / `docker compose
      ps`) — tanpa keduanya, job seperti kedaluwarsa booking (Tahap 3+) tidak akan jalan
- [ ] Backup database + object storage terjadwal dan sudah diuji proses restore-nya
      sekali sebelum go-live
