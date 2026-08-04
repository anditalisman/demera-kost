# Roadmap Implementasi Demera

Status per dokumen ini: **Tahap 1–7 selesai.** Seluruh cakupan Demera Living (katalog,
booking, tagihan & pembayaran manual/QRIS, penyewa & kontrak, notifikasi, dashboard &
laporan) sudah dibangun, diuji (PHPUnit + verifikasi manual end-to-end lewat sesi
sungguhan), dan di-commit per klaster. Demera Fashion tetap sengaja minim ("Segera
Hadir") sesuai keputusan awal — pengembangan katalog/keranjang/checkout Fashion penuh
ada di luar cakupan roadmap ini.

## ✅ Tahap 1 — Fondasi

- Setup Laravel 13 + Inertia + Vue 3 (TypeScript) + Tailwind, Docker Compose penuh
  (nginx, php-fpm, mysql8, redis, minio, queue worker, scheduler)
- Struktur modular `App\Domain\{Platform,Living,Fashion}`
- Skema database penuh — 36 tabel (35 + `maintenance_comments` yang ditambahkan di
  Tahap 5), lihat `docs/ERD.md`
- Autentikasi: registrasi, login email-atau-WhatsApp, verifikasi email, lupa/reset
  password, logout semua perangkat, rate limiting, wajib ganti password pertama untuk
  akun seed
- Object storage: MinIO/S3, disk publik vs privat, signed URL, kompresi gambar +
  thumbnail otomatis
- CMS: hero banner, info bisnis, galeri, testimoni, FAQ, pengaturan
- Landing page penuh, CMS-driven; Demera Fashion "Segera Hadir"
- Dashboard admin (shell) + manajemen pengguna/role
- Audit log otomatis + viewer admin
- Dokumentasi OpenAPI/Swagger untuk API publik v1

## ✅ Tahap 2 — Katalog Demera Living

- Panel admin: Struktur Properti (properti/gedung/lantai bersarang), Tipe Kamar,
  Fasilitas, dan Kamar (form lengkap + galeri foto drag-reorder + foto utama + assign
  fasilitas + ubah status manual/massal + riwayat status)
- Filter & pencarian publik di `/living/rooms`: status, rentang harga, tipe kamar,
  lantai, kapasitas, fasilitas; urutan terbaru/harga terendah/tertinggi

## ✅ Tahap 3 — Pemesanan

- `BookingLifecycleService`: `createHold()` (row-locked, mencegah double-booking),
  `expire()` (dipanggil scheduled command `bookings:expire` tiap 5 menit), `confirm()`
  (konversi ke Tenant + Lease + Deposit setelah pembayaran diverifikasi)
- Alur booking penuh di halaman publik: pilih tanggal & durasi, isi data penghuni,
  unggah KTP, rincian biaya, kode booking unik, halaman konfirmasi
- Tombol "Pesan Sekarang" di detail kamar aktif (redirect ke login dengan intent bila
  guest)

## ✅ Tahap 4 — Pembayaran dan Tagihan (manual + QRIS)

- `PaymentVerificationService`: customer unggah bukti transfer bank **atau** QRIS
  (gambar diunggah admin di halaman Pengaturan), admin memverifikasi kedua metode
  dengan cara yang sama (lihat bukti via signed URL → verifikasi/tolak)
- `InvoiceService`: tagihan bulanan otomatis (`invoices:generate-monthly`, harian) —
  bulan pertama sudah dibayar saat booking, periode berikutnya prorata otomatis bila
  tidak jatuh pas di `billing_cycle_day`; denda keterlambatan (`invoices:mark-overdue`,
  harian, flat/persentase via Pengaturan)
- Kuitansi & invoice PDF (dompdf)
- **Tidak ada payment gateway pihak ketiga** — keputusan eksplisit: hanya transfer
  manual + QRIS statis, sesuai `PAYMENT_GATEWAY_PROVIDER=manual`

## ✅ Tahap 5 — Penyewa dan Kontrak

- `LeaseManagementService`: perpanjang kontrak (`LeaseExtension` + snapshot harga
  baru opsional), pindah kamar (lease lama selesai, lease baru + kamar baru terisi),
  akhiri sewa (kamar dilepas, penyewa nonaktif, deposit dikembalikan penuh/sebagian)
- Admin bisa menyetujui/menolak booking secara manual (jalur cadangan di luar
  verifikasi pembayaran otomatis, mis. pembayaran tunai)
- Keluhan & perawatan: tabel baru `maintenance_comments`, thread dua arah
  penyewa↔admin, foto opsional

## ✅ Tahap 6 — Notifikasi

- `NotificationDispatcher`: setiap notifikasi selalu tercatat in-app (channel
  always-on) + driver channel spesifik (`LogWhatsAppDriver`/`LogEmailDriver`) yang
  mencatat penuh ke `notification_logs` tanpa benar-benar mengirim
  (`WHATSAPP_PROVIDER=log` tetap dipertahankan sesuai keputusan awal — tinggal ganti
  binding driver saat provider asli tersedia)
- `notifications:send-due-reminders` (harian): 11 template H-7 s.d. H+7 + kontrak akan
  berakhir; `notifications:retry-failed` (tiap jam)
- Pusat notifikasi (lonceng + halaman `/notifications`) di kedua layout (admin & customer)

## ✅ Tahap 7 — Laporan dan Dashboard

- Dashboard admin: kartu okupansi/kamar/penyewa/booking/pembayaran/tagihan, tren
  pendapatan 6 bulan, kontrak akan berakhir, notifikasi gagal kirim
- 10 laporan (okupansi, penyewa aktif, kontrak akan berakhir, tagihan, pendapatan per
  periode, pembayaran per metode, deposit, pembatalan, riwayat perubahan kamar,
  performa notifikasi), semua bisa diekspor PDF/Excel/CSV lewat satu jalur generik

## Catatan integrasi untuk sesi lanjutan

- **Jangan mengedit migration yang sudah ada** — tambah migration baru untuk perubahan
  skema. Dipraktikkan dua kali: FK `room_status_histories.booking_id` (Tahap 1) dan
  tabel `maintenance_comments` (Tahap 5).
- **Harga di kontrak/invoice adalah snapshot** — jangan pernah membaca ulang harga dari
  `rooms`/`room_types` untuk transaksi yang sudah dibuat.
- **Reservasi permission baru** lewat `RolePermissionSeeder`, bukan hardcode nama role/
  permission di controller.
- **Perhitungan tanggal berbasis bulan** (kontrak, invoice) harus memakai
  `addMonthsNoOverflow()`/`addMonthNoOverflow()`, bukan `addMonths()`/`addMonth()` biasa
  — bug nyata sempat ditemukan di Tahap 5 (tanggal 31 Desember + 2 bulan salah menjadi
  3 Maret, bukan 28 Februari) sebelum diperbaiki dan disamakan di seluruh
  `BookingLifecycleService`/`LeaseManagementService`/`InvoiceService`.
- **Perbandingan strict terhadap hasil `Carbon::diffInDays()`** harus meng-cast ke
  `(int)` dulu — method ini mengembalikan `float`, sehingga `in_array($x, $arr, true)`
  akan selalu gagal tanpa cast. Bug ini sempat membuat seluruh pengingat tagihan
  Tahap 6 tidak pernah terkirim sampai tertangkap oleh test.

## Belum dikerjakan (di luar cakupan 7 tahap ini)

- Katalog/keranjang/checkout Demera Fashion penuh (tetap "Segera Hadir")
- Integrasi payment gateway pihak ketiga (keputusan sadar: manual transfer + QRIS saja)
- Provider WhatsApp/email transaksional sungguhan (arsitektur driver sudah siap,
  tinggal ganti binding — lihat `docs/ARSITEKTUR.md`)
- Hardening keamanan produksi lanjutan, load test, UAT formal, deployment staging
  sungguhan (lihat `docs/DEPLOYMENT.md` untuk checklist go-live)
