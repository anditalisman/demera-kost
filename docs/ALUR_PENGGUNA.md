# Alur Pengguna — Tahap 1

## 1. Pengunjung menjelajahi kamar tanpa login

```mermaid
flowchart LR
    A[Buka demera.my.id] --> B{Pilih lini bisnis}
    B -->|Demera Living| C[/living]
    B -->|Demera Fashion| D[/fashion — Segera Hadir]
    C --> E[/living/rooms — katalog]
    E --> F[/living/rooms/slug — detail]
    F --> G{Tombol Pesan Sekarang}
    G -->|Tahap 1| H[Nonaktif, keterangan jujur:\n\"Pemesanan online segera hadir\"]
    G -.Tahap 3.-> I[Alur booking — belum ada]
    F --> J[Tombol WhatsApp — aktif, langsung wa.me]
```

Tidak ada satu pun langkah di sini yang memerlukan login — sesuai kriteria penerimaan
"Pengguna dapat melihat kamar tanpa login".

## 2. Registrasi & verifikasi

```mermaid
flowchart TD
    A[/register] --> B[Isi nama, email, WhatsApp, password]
    B --> C[Centang Syarat Penggunaan & Kebijakan Privasi]
    C --> D{Submit}
    D -->|WhatsApp/email sudah dipakai| E[Error validasi, tetap di form]
    D -->|Valid| F[User dibuat, role customer otomatis]
    F --> G[Auto-login, redirect ke /dashboard]
    G --> H[Middleware verified memaksa ke /verify-email]
    H --> I[Klik tautan verifikasi di email]
    I --> J[email_verified_at terisi]
    J --> K[Dashboard pengguna dapat diakses penuh]
```

Verifikasi WhatsApp memakai kolom `whatsapp_verified_at` yang sudah ada di skema, tapi
alur verifikasi aktif (kirim OTP dsb.) menunggu provider WhatsApp asli di Tahap 6 —
`WHATSAPP_PROVIDER=log` di Tahap 1 berarti belum ada pengiriman sungguhan.

## 3. Login (email atau WhatsApp)

```mermaid
flowchart TD
    A[/login] --> B[Isi kolom \"Email atau WhatsApp\" + password]
    B --> C{Format terdeteksi email?}
    C -->|Ya| D[Auth::attempt email+password]
    C -->|Tidak| E[Auth::attempt whatsapp_number+password]
    D --> F{Berhasil?}
    E --> F
    F -->|Gagal 5x dalam 1 menit| G[Rate limited, tunggu N detik]
    F -->|Berhasil| H{must_change_password?}
    H -->|true, contoh: akun super-admin baru| I[/force-password-update — wajib]
    H -->|false| J[/dashboard — redirect sesuai role]
```

## 4. Dashboard mengarahkan sesuai role

```mermaid
flowchart LR
    A[/dashboard] --> B{hasAnyRole super-admin/admin/property-manager/finance?}
    B -->|Ya| C[/admin/dashboard]
    B -->|Tidak — customer/tenant| D[/account/dashboard]
```

## 5. Admin mengelola konten (CMS)

```mermaid
flowchart TD
    A[/admin/content/pages] --> B[Klik + Tambah Konten]
    B --> C[Modal form: grup, key, judul, isi, gambar, CTA, SEO]
    C --> D{Submit}
    D -->|Valid| E[Tersimpan, audit_logs mencatat aksi created]
    E --> F[Muncul di landing page/halaman publik terkait\npada request berikutnya — tanpa deploy ulang]
    D -->|Key duplikat dalam grup yang sama| G[Error validasi]
```

Pola yang sama berlaku untuk Galeri (+ drag-to-reorder), Testimoni, dan FAQ.

## 6. Super-admin mengubah role pengguna

```mermaid
flowchart TD
    A[/admin/users] --> B[Cari pengguna]
    B --> C[Klik Atur Role]
    C --> D[Centang/hapus centang role di modal]
    D --> E{Submit}
    E --> F[syncRoles dijalankan]
    F --> G[audit_logs mencatat role_changed:\nrole lama vs role baru]
    G --> H[Nav sidebar pengguna tsb berubah\npada login/request berikutnya]
```

## 7. Keamanan akun: logout dari perangkat lain

```mermaid
flowchart TD
    A[/profile] --> B[Bagian \"Keluar dari Perangkat Lain\"]
    B --> C[Masukkan password saat ini]
    C --> D{Password benar?}
    D -->|Salah| E[Error validasi]
    D -->|Benar| F[Hash password di-rotate\n Auth::logoutOtherDevices]
    F --> G[Sesi di perangkat lain otomatis\nditolak pada request berikutnya\nlewat AuthenticateSession middleware]
    F --> H[Sesi di perangkat ini tetap aktif]
```

## 8. Fashion — pendaftaran notifikasi peluncuran

```mermaid
flowchart TD
    A[/fashion] --> B[Isi email dan/atau nomor WhatsApp]
    B --> C{Submit}
    C -->|Keduanya kosong| D[Error: isi salah satu]
    C -->|Sudah pernah daftar| E[Error: email/WhatsApp sudah terdaftar]
    C -->|Valid| F[Tersimpan ke fashion_launch_subscribers]
    F --> G[Pesan sukses: akan dikabari saat peluncuran]
```

## Alur yang belum ada (menunggu tahap berikutnya)

Booking kamar, upload dokumen identitas untuk booking, pembayaran, penerbitan invoice/
kuitansi, perpanjangan kontrak, dan pengajuan keluhan/perawatan **sengaja tidak
digambarkan di sini** — skemanya sudah siap (lihat `docs/ERD.md`) tapi UI/alur
transaksinya menyusul di Tahap 2–6 sesuai `docs/ROADMAP.md`.
