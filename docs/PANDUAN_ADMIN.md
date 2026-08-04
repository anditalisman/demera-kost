# Panduan Penggunaan Admin — Tahap 1

Panduan ini untuk staf yang memegang role `admin`. Fitur kamar,
booking, penyewa, kontrak, tagihan, dan laporan belum ada di Tahap 1 — panduan
penggunaannya menyusul bersama fiturnya di Tahap 2–7 (lihat `docs/ROADMAP.md`).

## Login pertama kali (Admin)

1. Buka `/login`, masuk dengan email dan password dari `SUPERADMIN_EMAIL`/
   `SUPERADMIN_PASSWORD` di `.env` produksi.
2. Sistem otomatis mengarahkan ke halaman **Ganti Kata Sandi** — ini wajib, tidak bisa
   dilewati (`must_change_password`). Isi kata sandi baru yang kuat dan unik.
3. Setelah itu Anda masuk ke `/admin/dashboard`.

## Dashboard Admin

Kartu menu di dashboard hanya menampilkan fitur yang benar-benar bisa Anda akses sesuai
role Anda — bila Anda tidak melihat suatu menu, akun Anda memang tidak memiliki
permission untuk itu (lihat `docs/ROLE_PERMISSION.md`).

## Mengelola Konten (Hero & Halaman, Galeri, Testimoni, FAQ)

Menu **Hero & Halaman** mengelola tiga jenis konten sekaligus, dipilih lewat tab:

- **Hero Banner** — teks & gambar yang tampil di bagian atas landing page.
- **Info Bisnis** — deskripsi singkat Demera Fashion dan Demera Living di landing page.
- **Kebijakan** — halaman Syarat Penggunaan, Kebijakan Privasi, dan Kebijakan
  Pembayaran & Pembatalan (tautan-tautan ini sudah muncul di footer & form registrasi
  sejak Tahap 1 — mengedit di sini langsung mengubah isi halaman publiknya).

Setiap perubahan (tambah/ubah/hapus) tercatat otomatis di **Audit Log** — tidak perlu
langkah tambahan apa pun.

**Galeri**: klik **+ Tambah Foto**, pilih kategori dan unggah gambar (JPEG/PNG/WebP,
maksimal 8MB — sistem otomatis mengompres dan membuat thumbnail). Urutan tampil bisa
diubah dengan menyeret (drag) kartu foto.

**Testimoni**: tandai testimoni terbaik sebagai "Unggulan" agar tampil lebih menonjol di
landing page.

**FAQ**: kelompokkan pertanyaan lewat kategori (Umum, Pemesanan, Pembayaran, Fashion)
agar mudah ditemukan pengguna.

## Pengaturan (Kontak, Media Sosial, SEO)

Menu **Pengaturan** mengubah nomor WhatsApp, email, alamat, URL peta, tautan media
sosial, dan judul/deskripsi SEO default — semuanya langsung dipakai di seluruh halaman
publik (tombol WhatsApp, footer, embed peta) tanpa perlu deploy ulang.

## Mengelola Pengguna & Role

Menu **Pengguna** (hanya terlihat bila akun Anda punya izin `users.view`/`users.manage`,
biasanya semua admin, karena hanya ada satu tingkat admin):

1. Cari pengguna lewat nama, email, atau nomor WhatsApp.
2. Klik **Atur Role** untuk mengubah role seorang pengguna (bisa lebih dari satu role).
3. Klik **Nonaktifkan** untuk menangguhkan akun pengguna (mereka tidak bisa login
   selama akun nonaktif). Anda tidak bisa menonaktifkan akun Anda sendiri lewat halaman
   ini — ini pencegahan yang disengaja.

## Audit Log

Menu **Audit Log** (izin `audit-logs.view`) menampilkan riwayat: siapa mengubah apa,
kapan, dari IP mana. Bisa difilter berdasarkan jenis aksi (`created`, `updated`,
`deleted`, `login`, `login_failed`, `role_changed`, `user_activated`,
`user_deactivated`, dst).

## Pertanyaan Umum

**Q: Saya tidak melihat menu Kamar/Booking/Tagihan.**
A: Fitur tersebut belum dikembangkan di Tahap 1 — bukan masalah izin akses. Lihat
`docs/ROADMAP.md` untuk jadwal tahap berikutnya.

**Q: Saya lupa password akun staf.**
A: Gunakan alur **Lupa Password** di halaman login seperti pengguna biasa — tautan
reset dikirim ke email yang terdaftar (di lingkungan dev, tautan muncul di
`storage/logs/laravel.log` karena `MAIL_MAILER=log`).

**Q: Bagaimana cara menambah admin baru?**
A: Minta calon admin mendaftar akun biasa lewat `/register` (otomatis dapat role
`customer`), lalu Anda ubah role-nya lewat menu **Pengguna**.
