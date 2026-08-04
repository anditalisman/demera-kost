# Panduan Penggunaan Admin

Panduan ini untuk staf yang memegang role `admin` (satu tingkat, memegang seluruh
permission — lihat `docs/ROLE_PERMISSION.md`).

## Login pertama kali

1. Buka `/login`, masuk dengan email dan password dari `SUPERADMIN_EMAIL`/
   `SUPERADMIN_PASSWORD` di `.env` produksi.
2. Sistem otomatis mengarahkan ke halaman **Ganti Kata Sandi** — ini wajib, tidak bisa
   dilewati (`must_change_password`). Isi kata sandi baru yang kuat dan unik.
3. Setelah itu Anda masuk ke `/admin/dashboard`.

## Dashboard

Kartu ringkasan menampilkan angka real-time: okupansi kamar, kamar tersedia/ditahan/
perawatan, penyewa aktif & calon penyewa, booking yang menunggu pembayaran, bukti bayar
yang menunggu verifikasi, tagihan belum lunas & terlambat, dan pendapatan bulan
berjalan. Di bawahnya ada grafik tren pendapatan 6 bulan terakhir, daftar kontrak yang
akan berakhir dalam 30 hari, dan (bila ada) daftar notifikasi yang gagal terkirim.
Menu **Menu Cepat** di bagian bawah hanya menampilkan tautan ke fitur yang benar-benar
bisa Anda akses.

## Struktur Properti, Tipe Kamar, Fasilitas, dan Kamar

1. **Struktur Properti** — kelola properti (alamat, kontak, peraturan kost), lalu di
   dalam kartu properti yang sama tambahkan gedung dan lantainya (bersarang, tanpa
   pindah halaman).
2. **Tipe Kamar** — harga dasar, deposit dasar, kapasitas default per properti.
3. **Fasilitas** — bedakan fasilitas kamar (AC, kasur) vs fasilitas bersama (dapur,
   laundry) lewat field Tipe.
4. **Kamar** — halaman tersendiri (bukan modal) karena banyak field:
   - Detail dasar: properti/gedung/lantai/tipe kamar (dropdown bersarang), nomor,
     harga, deposit, kapasitas.
   - **Foto**: unggah banyak foto sekaligus, seret (drag) untuk mengubah urutan, foto
     pertama otomatis jadi foto utama (atau atur manual lewat tombol "Jadikan Utama").
   - **Fasilitas**: centang fasilitas yang tersedia di kamar ini.
   - **Status**: ubah manual (mis. ke "Dalam Perawatan") dengan alasan opsional —
     setiap perubahan tercatat di riwayat status kamar. Di halaman daftar kamar, pilih
     banyak kamar sekaligus untuk ubah status massal.

## Booking

**Booking** menampilkan seluruh pemesanan dengan filter status. Buka detail booking
untuk melihat data penghuni, dokumen KTP, dan invoice terkait. Tombol **Setujui
Manual**/**Tolak Booking** adalah jalur cadangan di luar verifikasi bukti bayar
otomatis — pakai hanya bila pembayaran diterima di luar sistem (mis. tunai langsung).
Pada alur normal, booking otomatis berubah menjadi kontrak sewa begitu Anda
memverifikasi pembayarannya di menu **Pembayaran**.

## Pembayaran

Menu **Pembayaran** menampilkan bukti bayar yang diunggah pelanggan (transfer bank
maupun QRIS — keduanya diverifikasi dengan cara yang sama). Klik **Lihat Bukti** untuk
membuka gambar/PDF bukti lewat tautan yang berumur pendek (bukan tautan publik
permanen). Klik **Verifikasi** untuk menandai lunas — bila ini melunasi invoice booking
sepenuhnya, sistem otomatis membuat penyewa, kontrak sewa aktif, dan mengubah kamar
menjadi Terisi. Klik **Tolak** dan isi alasan bila bukti tidak valid — pelanggan bisa
mengunggah ulang.

## Penyewa & Kontrak

**Penyewa** menampilkan seluruh calon/penyewa aktif dengan riwayat kamar, tagihan,
pembayaran, dokumen identitas, dan keluhan mereka dalam satu halaman. Klik **Kelola**
pada sebuah kontrak untuk:

- **Perpanjang Kontrak** — tambah durasi bulan, opsional ubah harga sewa baru.
- **Pindah Kamar** — pilih kamar tujuan yang berstatus Tersedia; kamar lama otomatis
  dilepas, kontrak lama ditutup, kontrak baru dibuat dengan harga kamar baru.
- **Akhiri Sewa** — isi jumlah deposit yang dikembalikan dan catatan potongan (bila
  ada kerusakan dsb.); kamar otomatis kembali Tersedia dan status penyewa jadi
  Tidak Aktif.

## Invoice & Laporan Keuangan

**Invoice** menampilkan seluruh tagihan (booking maupun sewa bulanan) dengan filter
status. Tagihan bulanan dan penerapan denda keterlambatan berjalan otomatis setiap hari
— tidak perlu dibuat manual.

## Keluhan & Perawatan

**Keluhan** menampilkan laporan dari penyewa (kategori, prioritas, foto bila ada). Buka
detail untuk mengubah status (Baru → Diproses → Selesai/Ditutup) dan membalas di thread
diskusi yang sama dengan penyewa.

## Laporan

Menu **Laporan** menyediakan 10 jenis laporan (okupansi, penyewa aktif, kontrak akan
berakhir, tagihan, pendapatan per periode, pembayaran per metode, deposit, pembatalan,
riwayat perubahan kamar, performa notifikasi). Gunakan filter tanggal/status yang
muncul sesuai jenis laporan yang dipilih, lalu unduh sebagai **PDF**, **Excel**, atau
**CSV** lewat tombol di kanan atas.

## Mengelola Konten (Hero & Halaman, Galeri, Testimoni, FAQ)

Menu **Hero & Halaman** mengelola tiga jenis konten sekaligus, dipilih lewat tab:

- **Hero Banner** — teks & gambar yang tampil di bagian atas landing page.
- **Info Bisnis** — deskripsi singkat Demera Fashion dan Demera Living di landing page.
- **Kebijakan** — halaman Syarat Penggunaan, Kebijakan Privasi, dan Kebijakan
  Pembayaran & Pembatalan (tautan-tautan ini muncul di footer & form registrasi —
  mengedit di sini langsung mengubah isi halaman publiknya).

Setiap perubahan (tambah/ubah/hapus) tercatat otomatis di **Audit Log**.

**Galeri**: klik **+ Tambah Foto**, pilih kategori dan unggah gambar (JPEG/PNG/WebP,
maksimal 8MB — sistem otomatis mengompres dan membuat thumbnail). Urutan tampil bisa
diubah dengan menyeret (drag) kartu foto.

**Testimoni**: tandai testimoni terbaik sebagai "Unggulan" agar tampil lebih menonjol di
landing page.

**FAQ**: kelompokkan pertanyaan lewat kategori (Umum, Pemesanan, Pembayaran, Fashion)
agar mudah ditemukan pengguna.

## Pengaturan

Menu **Pengaturan** kini terbagi beberapa kelompok:

- **Kontak & Lokasi**, **Media Sosial**, **SEO** — sama seperti Tahap 1, langsung
  dipakai di seluruh halaman publik tanpa perlu deploy ulang.
- **Pemesanan** — batas waktu pembayaran booking (jam) sebelum kamar dilepas kembali.
- **Pembayaran** — nomor rekening bank, biaya admin booking, dan tipe/nilai denda
  keterlambatan. Gambar QRIS diunggah lewat kartu terpisah di bagian bawah halaman
  (unggah ulang untuk mengganti — gambar lama otomatis dihapus).
- **Notifikasi** — jadwal pengingat tagihan (hari, dipisah koma, negatif = sebelum
  jatuh tempo, positif = setelah jatuh tempo, mis. `-7,-3,-1,0,1,3,7`).

## Mengelola Pengguna & Role

Menu **Pengguna** (izin `users.view`/`users.manage`):

1. Cari pengguna lewat nama, email, atau nomor WhatsApp.
2. Klik **Atur Role** untuk mengubah role seorang pengguna.
3. Klik **Nonaktifkan** untuk menangguhkan akun pengguna. Anda tidak bisa
   menonaktifkan akun Anda sendiri lewat halaman ini — ini pencegahan yang disengaja.

## Audit Log

Menu **Audit Log** (izin `audit-logs.view`) menampilkan riwayat: siapa mengubah apa,
kapan, dari IP mana. Bisa difilter berdasarkan jenis aksi.

## Pertanyaan Umum

**Q: Bagaimana notifikasi WhatsApp/email sebenarnya dikirim?**
A: Belum ada pengiriman sungguhan — setiap notifikasi (konfirmasi booking, verifikasi
pembayaran, pengingat tagihan) tetap tercatat penuh di sistem (in-app + log), tapi
WhatsApp/email fisik belum terkirim karena belum ada provider asli yang dikonfigurasi
(`WHATSAPP_PROVIDER=log`). Ini keputusan cakupan yang disengaja, bukan bug.

**Q: Kenapa tidak ada pilihan pembayaran kartu kredit/e-wallet/virtual account?**
A: Keputusan produk yang eksplisit — hanya transfer bank manual dan QRIS statis yang
tersedia, keduanya diverifikasi manual oleh admin, tanpa integrasi payment gateway.

**Q: Saya lupa password akun staf.**
A: Gunakan alur **Lupa Password** di halaman login seperti pengguna biasa — tautan
reset dikirim ke email yang terdaftar (di lingkungan dev, tautan muncul di
`storage/logs/laravel.log` karena `MAIL_MAILER=log`).

**Q: Bagaimana cara menambah admin baru?**
A: Minta calon admin mendaftar akun biasa lewat `/register` (otomatis dapat role
`customer`), lalu Anda ubah role-nya lewat menu **Pengguna**.
