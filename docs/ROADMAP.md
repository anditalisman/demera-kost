# Roadmap Implementasi Demera

Status per dokumen ini: **Tahap 1 selesai dan production-ready untuk cakupannya.**
Tahap 2–7 belum dikerjakan — deskripsi di bawah adalah rencana untuk sesi pengembangan
selanjutnya, disalin & disesuaikan dari spesifikasi awal proyek.

## ✅ Tahap 1 — Fondasi (selesai)

- [x] Setup Laravel 13 + Inertia + Vue 3 (TypeScript) + Tailwind, Docker Compose penuh
      (nginx, php-fpm, mysql8, redis, minio, queue worker, scheduler)
- [x] Struktur modular `App\Domain\{Platform,Living,Fashion}`
- [x] Skema database penuh — 35 tabel, seluruh migration untuk semua tahap sekaligus
      (lihat `docs/ERD.md`), supaya tahap berikutnya tidak perlu migration yang mengubah
      kontrak data yang sudah dipakai
- [x] Autentikasi: registrasi (nama/email/WhatsApp/password), login email-atau-WhatsApp,
      verifikasi email, lupa/reset password, logout semua perangkat, rate limiting, wajib
      ganti password pertama kali untuk akun seed
- [x] RBAC: 6 role, matriks permission, Policy per resource, nav yang sadar permission
- [x] Object storage: MinIO/S3, disk publik vs privat, signed URL, kompresi gambar +
      thumbnail otomatis
- [x] CMS: hero banner, info bisnis, galeri (+drag reorder), testimoni, FAQ, pengaturan
      (kontak/sosial/SEO) — semua tanpa perlu ubah kode
- [x] Landing page penuh, CMS-driven
- [x] Demera Fashion "Segera Hadir" + form notifikasi peluncuran
- [x] Demera Living publik: hub, katalog kamar (baca saja), detail kamar, galeri,
      fasilitas, lokasi, FAQ, kontak
- [x] Dashboard admin (shell) + manajemen pengguna/role
- [x] Audit log otomatis (perubahan CMS + event auth) + viewer admin
- [x] Dokumentasi OpenAPI/Swagger untuk API publik v1
- [x] Data awal: seeder lengkap (role, super-admin, staf demo, 1 properti, 12 kamar
      dengan 6 status berbeda, fasilitas, foto placeholder, penyewa contoh, booking
      contoh, invoice & pembayaran contoh, template notifikasi, FAQ, testimoni)
- [x] 75 automated test (PHPUnit) mencakup auth, RBAC, CRUD CMS, halaman publik, seeder
- [x] Dokumentasi: arsitektur, ERD, sitemap, alur pengguna, role & permission, endpoint
      API, struktur folder, panduan deployment Docker & non-Docker

### Sengaja tidak dikerjakan di Tahap 1

Fitur berikut punya skema database yang sudah lengkap (lihat `docs/ERD.md`) tapi belum
ada UI/logika bisnisnya — ini keputusan cakupan, bukan bagian yang terlewat:

- Katalog kamar dengan filter/sort/search dan panel admin CRUD kamar → **Tahap 2**
- Proses booking, penahanan kamar, pencegahan double-booking, upload dokumen identitas
  → **Tahap 3**
- Invoice, payment gateway, webhook, kuitansi PDF, tagihan berulang → **Tahap 4**
- Manajemen penyewa & kontrak aktif, perpindahan kamar → **Tahap 5**
- Notifikasi WhatsApp/email sungguhan (pengingat jatuh tempo dst.) → **Tahap 6**
- Dashboard admin dengan statistik okupansi/pendapatan, laporan & ekspor → **Tahap 7**

## Tahap 2 — Katalog Demera Living

- Panel admin CRUD kamar (properti, gedung, lantai, tipe kamar, harga, deposit, biaya
  tambahan, galeri dengan drag-and-drop, fasilitas)
- Filter & pencarian publik: ketersediaan, rentang harga, tipe kamar, lantai, kapasitas,
  fasilitas; pengurutan: terbaru, harga terendah/tertinggi
- Kalender okupansi admin
- Riwayat perubahan status kamar (tabel `room_status_histories` sudah siap dipakai)

## Tahap 3 — Pemesanan

- Profil pengguna diperluas (dokumen identitas, kontak darurat)
- Alur pemesanan kamar penuh: pilih tanggal & durasi, kalkulasi biaya, isi data
  penghuni, upload KTP, setujui peraturan, kode booking unik
- Penahanan kamar sementara + kedaluwarsa otomatis (job terjadwal)
- Pencegahan double-booking (database transaction + row locking)

## Tahap 4 — Pembayaran dan Tagihan

- Invoice otomatis dari booking
- Integrasi payment gateway (Virtual Account, QRIS, e-wallet) lewat abstraction layer
  yang sudah disiapkan (`PAYMENT_GATEWAY_PROVIDER`)
- Transfer manual + verifikasi finance
- Webhook + idempotency (tabel `payment_webhooks`, kolom `idempotency_key` sudah siap)
- Kuitansi & invoice PDF
- Tagihan bulanan otomatis (job terjadwal), prorata, denda keterlambatan

## Tahap 5 — Penyewa dan Kontrak

- Aktivasi penyewa dari booking yang lunas
- Kontrak sewa (dokumen PDF, persetujuan digital sederhana)
- Perpanjangan kontrak, perpindahan kamar
- Pengakhiran sewa + pengembalian deposit

## Tahap 6 — Notifikasi

- Provider WhatsApp sungguhan (ganti `WHATSAPP_PROVIDER=log` dengan implementasi nyata)
- Pengiriman email transaksional sungguhan
- Notification center dalam aplikasi (tabel `notifications` sudah dipakai untuk
  penghitungan badge, tinggal diisi event nyata)
- Pengingat jatuh tempo terjadwal (11 template sudah diseed: H-7 s.d. H+7, kontrak akan
  berakhir, booking dikonfirmasi, pembayaran terverifikasi)
- Retry + log pengiriman (tabel `notification_logs` sudah siap)

## Tahap 7 — Laporan dan Production Readiness

- Dashboard admin dengan statistik okupansi, pendapatan, tren
- Laporan: okupansi, penyewa aktif, kontrak akan berakhir, tagihan per periode/terlambat,
  pendapatan per periode, pembayaran per metode, deposit, ekspor PDF/Excel/CSV
- Rekonsiliasi pembayaran
- Hardening keamanan tambahan, load test, UAT, deployment staging → production

## Catatan integrasi untuk sesi lanjutan

- **Jangan mengedit migration yang sudah ada** — tambah migration baru untuk perubahan
  skema (misalnya menambah kolom). Ini sudah dipraktikkan di Tahap 1 sendiri: FK
  `room_status_histories.booking_id` ditambahkan lewat migration terpisah setelah tabel
  `bookings` dibuat, bukan mengedit migration `room_status_histories` yang sudah ada.
- **Harga di kontrak/invoice adalah snapshot** — jangan pernah membaca ulang harga dari
  `rooms`/`room_types` untuk transaksi yang sudah dibuat.
- **Reservasi permission baru** lewat `RolePermissionSeeder`, bukan hardcode nama role/
  permission di controller.
