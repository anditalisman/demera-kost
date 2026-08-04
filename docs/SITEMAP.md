# Sitemap — Tahap 1

Legenda akses: **Publik** (tanpa login) · **Auth** (login apa pun) · **Admin** (role
`admin`, digerbangi permission per halaman).

## Publik

| Rute | Halaman | Deskripsi |
|---|---|---|
| `/` | `Public/Landing` | Hero, dua kartu bisnis, kamar unggulan, galeri, keunggulan, testimoni, FAQ, lokasi, kontak |
| `/kebijakan/{slug}` | `Public/Policy` | `syarat-penggunaan`, `kebijakan-privasi`, `kebijakan-pembayaran` (CMS-driven) |
| `/fashion` | `Fashion/ComingSoon` | Hero editorial + form notifikasi peluncuran |
| `/fashion/products` | `Fashion/ComingSoon` | Pratinjau produk (statis, Tahap 1) |
| `/fashion/categories` | `Fashion/ComingSoon` | Pratinjau kategori (statis, Tahap 1) |
| `/fashion/product/{slug}` | `Fashion/ComingSoon` | Detail pratinjau 1 produk |
| `/living` | `Living/Index` | Hub Demera Living: tautan cepat, kamar tersedia, testimoni |
| `/living/rooms` | `Living/Rooms/Index` | Katalog kamar (baca saja, tanpa filter/sort — Tahap 2) |
| `/living/rooms/{slug}` | `Living/Rooms/Show` | Detail kamar: galeri lightbox, harga, fasilitas, peraturan kost, kamar terkait |
| `/living/gallery` | `Living/Gallery` | Galeri foto per kategori |
| `/living/facilities` | `Living/Facilities` | Daftar fasilitas kamar & bersama |
| `/living/location` | `Living/Location` | Alamat properti + peta |
| `/living/faq` | `Living/Faq` | FAQ khusus Living |
| `/living/contact` | `Living/Contact` | Kontak + tombol WhatsApp |
| `/login`, `/register` | `Auth/Login`, `Auth/Register` | — |
| `/forgot-password`, `/reset-password/{token}` | Breeze default | Lupa/reset password |
| `/verify-email` | Breeze default | Prompt verifikasi email |
| `/api/documentation` | Swagger UI | Dokumentasi OpenAPI interaktif |
| `/docs` | JSON | Spesifikasi OpenAPI mentah |

## Auth (butuh login — peran apa pun)

| Rute | Halaman | Deskripsi |
|---|---|---|
| `/dashboard` | *(redirect)* | Mengarahkan ke `admin.dashboard` atau `customer.dashboard` sesuai role |
| `/account/dashboard` | `Dashboard/Customer/Dashboard` | Dashboard pengguna non-staff |
| `/profile` | `Profile/Edit` | Info profil, ganti password, keluar dari perangkat lain, hapus akun |
| `/force-password-update` | `Auth/ForcePasswordUpdate` | Wajib diisi bila `must_change_password=true` |
| `/confirm-password` | Breeze default | Konfirmasi ulang password untuk aksi sensitif |

## Admin (permission-gated, lihat `docs/ROLE_PERMISSION.md`)

| Rute | Halaman | Permission |
|---|---|---|
| `/admin/dashboard` | `Dashboard/Admin/Dashboard` | *(kartu menu menyesuaikan permission user)* |
| `/admin/content/pages` | `Dashboard/Admin/Content/Pages/Index` | `content.view`/`content.manage` |
| `/admin/content/galleries` | `Dashboard/Admin/Content/Galleries/Index` | `content.view`/`content.manage` |
| `/admin/content/testimonials` | `Dashboard/Admin/Content/Testimonials/Index` | `content.view`/`content.manage` |
| `/admin/content/faqs` | `Dashboard/Admin/Content/Faqs/Index` | `content.view`/`content.manage` |
| `/admin/settings` | `Dashboard/Admin/Content/Settings/Index` | `settings.view`/`settings.manage` |
| `/admin/users` | `Dashboard/Admin/Users/Index` | `users.view`/`users.manage` |
| `/admin/audit-logs` | `Dashboard/Admin/AuditLogs/Index` | `audit-logs.view` |

## JSON API v1 (publik, tanpa halaman)

| Rute | Method | Deskripsi |
|---|---|---|
| `/api/v1/living/rooms` | GET | Daftar kamar (paginated) |
| `/api/v1/living/rooms/{slug}` | GET | Detail kamar |
| `/api/v1/fashion/subscribe` | POST | Daftar notifikasi peluncuran (JSON) |
| `/api/user` | GET | User saat ini (Sanctum) |

## Belum ada di Tahap 1 (lihat `docs/ROADMAP.md`)

Halaman booking, checkout, invoice/kuitansi pengguna, manajemen kamar/booking/penyewa/
kontrak/tagihan/pembayaran admin, dan laporan **sengaja tidak ada** di sitemap ini — bukan
terlewat. Tombol yang secara alami mengarah ke sana (misalnya "Pesan Sekarang" di detail
kamar) ditampilkan dalam keadaan nonaktif dengan keterangan jujur, bukan tautan mati.
