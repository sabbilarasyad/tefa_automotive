# TEFA Automotive

Backend Laravel 13 untuk sistem manajemen bengkel TEFA Automotive. Autentikasi API memakai Laravel Sanctum bearer token; database pengembangan menggunakan MySQL.

## Persyaratan

- PHP 8.4.1 atau lebih baru (sesuai dependensi terkunci saat ini)
- Composer
- MySQL 8.0 atau lebih baru

## Setup lokal

1. Salin `.env.example` menjadi `.env` dan sesuaikan `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`.
2. Buat database `tefa_automotive` dengan charset `utf8mb4` dan collation `utf8mb4_unicode_ci`.
3. Jalankan:

```sh
composer install
php artisan key:generate
php artisan migrate:fresh --seed
```

Sanctum sudah menjadi dependensi project. Migration `migrate:fresh` menghapus seluruh tabel pada database yang dipilih; jangan jalankan pada database berisi data penting.

## Akun demo

Semua akun berikut memakai password `password` untuk development saja. Ganti password sebelum penggunaan nyata.

| Role | Username |
|---|---|
| SUPER_ADMIN | `superadmin` |
| HEAD_TEFA | `headtefa` |
| ADMIN_CS | `admincs` |
| MEKANIK | `mekanik` |
| KASIR | `kasir` |

Password disimpan menggunakan hashed cast Laravel (`Hash::make`/bcrypt), bukan plaintext.

## Auth API

- `POST /api/login` menerima `username`, `password`, dan optional `device_name`.
- `POST /api/logout` mencabut hanya Sanctum token yang sedang digunakan.
- `GET /api/me` mengembalikan user, role, dan daftar permission.

Kirim token pada header `Authorization: Bearer <access_token>`. Seluruh response mengikuti envelope `{ success, data, message }`; error validasi juga menyertakan `errors`.