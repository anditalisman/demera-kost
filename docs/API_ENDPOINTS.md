# API Endpoints

Demera merender halaman lewat Inertia (bukan SPA yang mengonsumsi REST API — lihat
`docs/ARSITEKTUR.md`), jadi rute web di `docs/SITEMAP.md` bukan "API" dalam artian JSON.
Dokumen ini khusus untuk lapisan JSON publik di `/api/v1/*`, yang didesain untuk klien
eksternal (mobile app di masa depan, integrasi pihak ketiga, webhook).

**Dokumentasi interaktif (selalu jadi sumber kebenaran)**: `/api/documentation` (Swagger UI)
atau `/docs` (JSON mentah). Dihasilkan dari atribut PHP `#[OA\...]` di
`app/Http/Controllers/Api/*` dan `app/Http/Controllers/Fashion/FashionSubscriberController.php`
— regenerasi lewat `php artisan l5-swagger:generate` setiap kali endpoint berubah.

## Demera Living

### `GET /api/v1/living/rooms`

Daftar kamar publik (semua status, is_active=true), dipaginasi 12/halaman.

```json
{
  "current_page": 1,
  "data": [
    {
      "id": 11,
      "slug": "demera-living-kemang-b105",
      "room_number": "B105",
      "status": "available",
      "monthly_price": "1800000.00",
      "deposit_amount": "1000000.00",
      "capacity": 1,
      "size_sqm": "14.00",
      "primary_image": { "id": 21, "url": "https://.../rooms/11/....webp" },
      "room_type": { "id": 2, "name": "Deluxe" },
      "property": { "id": 1, "name": "Demera Living Kemang", "city": "Jakarta Selatan" }
    }
  ],
  "last_page": 1,
  "total": 12
}
```

### `GET /api/v1/living/rooms/{slug}`

Detail satu kamar (404 bila slug tidak ditemukan atau kamar tidak aktif). Menyertakan
`images[]`, `facilities[]`, `room_type`, `property` secara penuh.

## Demera Fashion

### `POST /api/v1/fashion/subscribe`

Body: `{ "name"?: string, "email"?: string, "whatsapp_number"?: string }` — wajib mengisi
`email` atau `whatsapp_number` (validasi 422 bila keduanya kosong atau salah satu duplikat).
Rate limit: 5 request/menit/IP.

Endpoint yang sama juga dipakai form web (`POST /fashion/subscribe`) — controller
mendeteksi `Accept: application/json` tanpa header `X-Inertia` untuk memutuskan
mengembalikan JSON (`201`) vs redirect Inertia biasa.

## Autentikasi API (Sanctum)

`GET /api/user` — mengembalikan user yang sedang login lewat Sanctum personal access
token (`Authorization: Bearer {token}`). Endpoint publik Living/Fashion di atas **tidak**
memerlukan autentikasi. Endpoint API bergerbang permission (booking, invoice, dsb.) belum
ada di Tahap 1 — akan ditambahkan seiring fitur backend-nya (Tahap 3+), memakai token
Sanctum yang sama.

## Menambah endpoint API baru

1. Controller di `app/Http/Controllers/Api/`.
2. Daftarkan di `routes/modules/api_living.php` atau `api_fashion.php` (atau file modul
   baru untuk domain lain), yang di-require dari `routes/api.php` di bawah prefix
   `v1` — jangan daftarkan langsung di `routes/api.php`.
3. Tambahkan atribut `#[OA\Get]`/`#[OA\Post]`/dst. di method controller, dan
   `#[OA\Schema]` di `app/OpenApi/` bila butuh skema response baru.
4. Jalankan `php artisan l5-swagger:generate` dan cek `/api/documentation`.
