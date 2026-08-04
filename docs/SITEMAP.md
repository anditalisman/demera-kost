# Sitemap

Legenda akses: **Publik** (tanpa login) · **Auth** (login apa pun) · **Admin** (role
`admin`, digerbangi permission per halaman).

## Publik

| Rute | Halaman | Deskripsi |
|---|---|---|
| `/` | `Public/Landing` | Hero, dua kartu bisnis, kamar unggulan, galeri, keunggulan, testimoni, FAQ, lokasi, kontak |
| `/kebijakan/{slug}` | `Public/Policy` | `syarat-penggunaan`, `kebijakan-privasi`, `kebijakan-pembayaran` (CMS-driven) |
| `/fashion` | `Fashion/ComingSoon` | Hero editorial + form notifikasi peluncuran |
| `/fashion/products` | `Fashion/ComingSoon` | Pratinjau produk (statis) |
| `/fashion/categories` | `Fashion/ComingSoon` | Pratinjau kategori (statis) |
| `/fashion/product/{slug}` | `Fashion/ComingSoon` | Detail pratinjau 1 produk |
| `/living` | `Living/Index` | Hub Demera Living: tautan cepat, kamar tersedia, testimoni |
| `/living/rooms` | `Living/Rooms/Index` | Katalog kamar dengan filter (status, harga, tipe, lantai, kapasitas, fasilitas) & urutan |
| `/living/rooms/{slug}` | `Living/Rooms/Show` | Detail kamar: galeri lightbox, harga, fasilitas, peraturan kost, tombol Pesan Sekarang |
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
| `/account/dashboard` | `Dashboard/Customer/Dashboard` | Dashboard pelanggan: jelajahi kamar, tagihan saya, profil |
| `/profile` | `Profile/Edit` | Info profil, ganti password, keluar dari perangkat lain, hapus akun |
| `/force-password-update` | `Auth/ForcePasswordUpdate` | Wajib diisi bila `must_change_password=true` |
| `/confirm-password` | Breeze default | Konfirmasi ulang password untuk aksi sensitif |
| `/notifications` | `Notifications/Index` | Pusat notifikasi (admin & customer, layout menyesuaikan role) |
| `/living/rooms/{slug}/book` | `Living/Bookings/Create` | Form pemesanan kamar (data penghuni, KTP, rincian biaya) |
| `/bookings/{code}` | `Living/Bookings/Show` | Konfirmasi & status booking (hanya pemilik atau admin) |
| `/invoices` | `Living/Invoices/Index` | Daftar tagihan milik pengguna |
| `/invoices/{invoice}` | `Living/Invoices/Show` | Detail tagihan + tombol Bayar Sekarang |
| `/invoices/{invoice}/pdf` | *(download)* | Invoice PDF |
| `/invoices/{invoice}/pay` | `Living/Payments/Create` | Pilih metode (transfer/QRIS) + unggah bukti bayar |
| `/payments/{payment}/receipt` | *(download)* | Kuitansi PDF (hanya setelah lunas) |
| `/maintenance-requests` | `Living/Maintenance/Index` | Daftar keluhan milik penyewa |
| `/maintenance-requests/create` | `Living/Maintenance/Create` | Ajukan keluhan baru (hanya penyewa aktif) |
| `/maintenance-requests/{id}` | `Living/Maintenance/Show` | Detail keluhan + thread diskusi |

## Admin (permission-gated, lihat `docs/ROLE_PERMISSION.md`)

| Rute | Halaman | Permission |
|---|---|---|
| `/admin/dashboard` | `Dashboard/Admin/Dashboard` | role `admin` (kartu ringkasan operasional real-time) |
| `/admin/content/pages` | `Dashboard/Admin/Content/Pages/Index` | `content.view`/`content.manage` |
| `/admin/content/galleries` | `Dashboard/Admin/Content/Galleries/Index` | `content.view`/`content.manage` |
| `/admin/content/testimonials` | `Dashboard/Admin/Content/Testimonials/Index` | `content.view`/`content.manage` |
| `/admin/content/faqs` | `Dashboard/Admin/Content/Faqs/Index` | `content.view`/`content.manage` |
| `/admin/settings` | `Dashboard/Admin/Content/Settings/Index` | `settings.view`/`settings.manage` (kontak, sosial, SEO, booking, pembayaran/QRIS, notifikasi) |
| `/admin/users` | `Dashboard/Admin/Users/Index` | `users.view`/`users.manage` |
| `/admin/audit-logs` | `Dashboard/Admin/AuditLogs/Index` | `audit-logs.view` |
| `/admin/properties` | `Dashboard/Admin/Living/Properties/Index` | `rooms.view`/`rooms.manage` (properti/gedung/lantai bersarang) |
| `/admin/room-types` | `Dashboard/Admin/Living/RoomTypes/Index` | `rooms.view`/`rooms.manage` |
| `/admin/facilities` | `Dashboard/Admin/Living/Facilities/Index` | `rooms.view`/`rooms.manage` |
| `/admin/rooms` | `Dashboard/Admin/Living/Rooms/Index` | `rooms.view`/`rooms.manage` (filter + bulk status) |
| `/admin/rooms/create`, `/admin/rooms/{room}/edit` | `Dashboard/Admin/Living/Rooms/Form` | `rooms.manage` (foto, fasilitas, status, riwayat) |
| `/admin/bookings` | `Dashboard/Admin/Living/Bookings/Index` | `bookings.view`/`bookings.manage` |
| `/admin/bookings/{booking}` | `Dashboard/Admin/Living/Bookings/Show` | `bookings.view`/`bookings.manage` (approve/reject manual) |
| `/admin/tenants` | `Dashboard/Admin/Living/Tenants/Index` | `tenants.view`/`tenants.manage` |
| `/admin/tenants/{tenant}` | `Dashboard/Admin/Living/Tenants/Show` | `tenants.view`/`tenants.manage` (riwayat kamar/tagihan/keluhan/dokumen) |
| `/admin/leases` | `Dashboard/Admin/Living/Leases/Index` | `leases.view`/`leases.manage` |
| `/admin/leases/{lease}` | `Dashboard/Admin/Living/Leases/Show` | `leases.manage` (perpanjang, pindah kamar, akhiri sewa) |
| `/admin/invoices` | `Dashboard/Admin/Living/Invoices/Index` | `invoices.view`/`invoices.manage` |
| `/admin/invoices/{invoice}` | `Dashboard/Admin/Living/Invoices/Show` | `invoices.view`/`invoices.manage` |
| `/admin/payments` | `Dashboard/Admin/Living/Payments/Index` | `payments.view`/`payments.verify`/`payments.manage` (verifikasi bukti bayar) |
| `/admin/maintenance-requests` | `Dashboard/Admin/Living/Maintenance/Index` | `maintenance.view`/`maintenance.manage` |
| `/admin/maintenance-requests/{id}` | `Dashboard/Admin/Living/Maintenance/Show` | `maintenance.manage` (ubah status, balas thread) |
| `/admin/reports` | `Dashboard/Admin/Living/Reports/Index` | `reports.view` (10 jenis laporan, ekspor PDF/Excel/CSV via `reports.export`) |

## JSON API v1 (publik, tanpa halaman)

| Rute | Method | Deskripsi |
|---|---|---|
| `/api/v1/living/rooms` | GET | Daftar kamar (paginated) |
| `/api/v1/living/rooms/{slug}` | GET | Detail kamar |
| `/api/v1/fashion/subscribe` | POST | Daftar notifikasi peluncuran (JSON) |
| `/api/user` | GET | User saat ini (Sanctum) |

Booking, tagihan, pembayaran, dan fitur transaksional lain sengaja **tidak** diekspos
lewat `/api/v1/*` — semuanya dilayani sebagai halaman Inertia biasa di atas, konsisten
dengan keputusan arsitektur di `docs/ARSITEKTUR.md` (monolith Inertia, bukan SPA+API).

## Belum ada (di luar cakupan 7 tahap)

Katalog/keranjang/checkout Demera Fashion penuh sengaja tidak ada — "Segera Hadir" tetap
jadi halaman resminya sampai modul Fashion dikembangkan di proyek terpisah.
