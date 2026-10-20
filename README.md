# TEFA Automotive

Backend Laravel 13 untuk sistem manajemen bengkel TEFA Automotive. Autentikasi API memakai Laravel Sanctum bearer token; database pengembangan menggunakan MySQL.

## Persyaratan Server

- **PHP >= 8.4.1** (direkomendasikan 8.5+)
- **Composer** 2.x
- **MySQL** 8.0+ (atau MariaDB 10.5+)
- **Ekstensi PHP wajib**: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`
- **Ekstensi PHP opsional** (untuk fitur gambar): `gd`, `exif`
- **MySQL tools** (untuk backup): `mysqldump`, `mysql` client di PATH

## Setup Lokal

1. Salin `.env.example` menjadi `.env` dan sesuaikan:
   ```
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=tefa_automotive
   DB_USERNAME=root
   DB_PASSWORD=
   ADMIN_USERNAME=superadmin
   ADMIN_PASSWORD=changeme123
   ```
2. Buat database `tefa_automotive` dengan charset `utf8mb4` dan collation `utf8mb4_unicode_ci`.
3. Jalankan:
   ```sh
   composer install
   php artisan key:generate
   php artisan migrate:fresh --seed
   php artisan storage:link
   ```
4. (Opsional) Jalankan `npm install && npm run build` untuk asset frontend.

**Penting**: `migrate:fresh` menghapus seluruh tabel database yang dipilih; **jangan** jalankan pada database berisi data penting.

### Seeders

- **ProductionSeeder** (`APP_ENV=production`): Hanya roles, permissions, dan 1 akun SUPER_ADMIN (kredensial dari `.env` atau prompt interaktif). Tanpa akun demo, tanpa data dummy.
- **DemoSeeder** (`APP_ENV!=production`): Semua akun demo + data referensi (customers, vehicles, services, spareparts). Menampilkan peringatan "JANGAN dijalankan di produksi!" dan meminta konfirmasi (lewati dengan `--force`).
- **DatabaseSeeder**: Memanggil `ProductionSeeder` jika `APP_ENV=production`, selain itu `DemoSeeder`.

## Akun Demo (hanya development)

| Role | Username | Password |
|---|---|---|
| SUPER_ADMIN | `superadmin` | `password` |
| HEAD_TEFA | `headtefa` | `password` |
| ADMIN_CS | `admincs` | `password` |
| MEKANIK | `mekanik` | `password` |
| KASIR | `kasir` | `password` |

Semua password di-hash dengan bcrypt (`Hash::make`), bukan plaintext.

## Matriks Hak Akses (ringkasan)

| Fitur / Area | SUPER_ADMIN | HEAD_TEFA | ADMIN_CS | MEKANIK | KASIR |
|---|---|---|---|---|---|
| PKB (kelola, progress, cancel, assign) | ✓ | ✓ (view) | ✓ | view own | view waiting payment |
| Stok & Restok | ✓ | ✗ | ✓ | ✗ | ✗ |
| Master Jasa / Sparepart | ✓ | ✗ | ✓ | ✗ | ✗ |
| Laporan (semua) | ✓ | ✓ | ✓ | ✗ | ✗ |
| Kasir / Pembayaran | ✓ | ✗ | ✗ | ✗ | ✓ |
| Manajemen User | ✓ | ✗ | ✗ | ✗ | ✗ |
| Reminder | ✓ | ✗ | ✓ | ✗ | manage |
| Pesan Kontak | ✓ | ✗ | ✓ | ✗ | ✗ |
| Tugas Mekanik | ✓ | ✗ | ✓ | view own | ✗ |

Detail lengkap di `database/seeders/TefaAccessSeeder.php` dan kebijakan di `app/Policies/`.

## Autentikasi API

| Endpoint | Method | Deskripsi |
|---|---|---|
| `/api/login` | POST | Login, return token |
| `/api/logout` | POST | Revoke current token |
| `/api/me` | GET | Current user + permissions |
| `/api/contact-messages` | POST | Publik (throttle 10/menit) |

**Header**: `Authorization: Bearer <token>`  
**Format Response**: `{ success: bool, data: any, message: string, errors?: object }`  
**Error Validasi**: `422` dengan `errors` per field.  
**Unauthorized**: `401` (token hilang/invalid/expired).  
**Forbidden**: `403` (role tidak berhak).  
**Not Found**: `404`.  
**Server Error**: `500`.

## Endpoint Utama (ringkasan)

### PKB
- `GET /api/pkb` – list (filter: low_stock, status)
- `POST /api/pkb` – create
- `GET /api/pkb/{pkb}` – show
- `POST /api/pkb/{pkb}/start` – mulai kerja (KASIR/ADMIN_CS)
- `PATCH /api/pkb/{pkb}/spareparts` – update qty_used
- `POST /api/pkb/{pkb}/complete` – validasi selesai (KASIR/ADMIN_CS)

### Stok
- `GET /api/spareparts` – list (filter low_stock)
- `GET /api/spareparts/{id}` – show
- `POST /api/spareparts/{id}/stock-adjustments` – adjust stock (MANUAL_IN/ADJUSTMENT/RETURN)

### Master Data (SUPER_ADMIN & ADMIN_CS)
- `/api/master/services` – CRUD jasa
- `/api/master/spareparts` – CRUD sparepart

### User (SUPER_ADMIN)
- `/api/users` – CRUD + reset password + deactivate (bukan delete permanen)

### Pesan Kontak
- `GET /admin/contact-messages` – list + filter + mark read/unread
- `PUT /admin/contact-messages/{id}/mark-read`
- `PUT /admin/contact-messages/{id}/mark-unread`

## Perintah Artisan Khusus

| Perintah | Deskripsi |
|---|---|
| `php artisan tefa:backup [--compress] [--keep=7]` | Dump DB + zip `storage/app/public` → `storage/app/backups/backup_YYYYMMDD_HHMMSS.tar[.gz]`, simpan N terakhir |
| `php artisan tefa:restore <file> [--force]` | Restore DB + `storage/app/public` dari file backup di `storage/app/backups/` (konfirmasi interaktif, lewati `--force`) |
| `php artisan tefa:check` | Cek siap deploy: PHP version, ekstensi, APP_DEBUG, DB, trigger/function, storage link, writable dirs |
| `php artisan migrate:fresh --seed` | Reset DB + seed (pilih seeder via APP_ENV) |
| `php artisan storage:link` | Buat symlink `public/storage` → `storage/app/public` |

### Backup & Restore Detail
- Backup menyimpan: `database.sql` (mysqldump), `storage_app_public/` (copy recursive), `manifest.json`.
- Restore meminta konfirmasi (lewati `--force`), menimpa DB dan `storage/app/public` sepenuhnya.
- Hanya 7 backup terbaru yang disimpan (default `--keep=7`).
- Butuh `mysqldump`, `mysql`, `tar` di PATH server.
- **Konfigurasi path MySQL tools** (opsional, di `.env` atau `config/database.php`):
  - `MYSQLDUMP_PATH` / `config('database.mysqldump_path')` – path ke binary `mysqldump` (default: `mysqldump` dari PATH)
  - `MYSQL_PATH` / `config('database.mysql_path')` – path ke binary `mysql` client (default: `mysql` dari PATH)
  - Contoh di `.env`:
    ```
    MYSQLDUMP_PATH=/usr/bin/mysqldump
    MYSQL_PATH=/usr/bin/mysql
    ```

## SQL Manual (Fallback)

File: `database/sql/mysql_programmability.sql`  
Berisi semua trigger & function (bisa dijalankan via phpMyAdmin / CLI MySQL jika migrasi ditolak hosting):

- `fn_calculate_pkb_total(pkb_id)` – total PKB
- `fn_documentation_complete(pkb_id)` – cek kelengkapan dokumentasi
- `trg_pkb_status_history` – log perubahan status PKB
- `trg_prevent_negative_stock` – blokir stok negatif
- `trg_stock_movement_apply` – update stok otomatis

Jalankan **setelah** migrasi tabel selesai. Butuh privilege `SUPER` atau `log_bin_trust_function_creators=1` untuk CREATE FUNCTION.

## Troubleshooting

| Masalah | Solusi |
|---|---|
| `mysqldump`/`mysql` not found | Tambahkan path MySQL bin ke PATH server, atau set `MYSQLDUMP_PATH`/`MYSQL_PATH` di `.env` (lihat konfigurasi di atas) |
| `CREATE FUNCTION` butuh SUPER privilege | Minta hosting set `log_bin_trust_function_creators=1` atau jalankan SQL manual |
| Trigger sudah ada error | `DROP TRIGGER IF EXISTS ...` sebelum create |
| Storage link gagal di Windows | `rmdir /s /q public\storage` lalu `php artisan storage:link` (butuh admin untuk symlink) |
| `APP_DEBUG=true` di production | Set `APP_DEBUG=false` di `.env` production |
| Token expired 401 | Login ulang untuk dapatkan token baru |

## Penjadwalan Backup (Cron)

Contoh crontab untuk backup harian jam 02:00:
```cron
0 2 * * * /usr/bin/php /path/to/tefa-automotive/artisan tefa:backup --compress --keep=7 >> /var/log/tefa-backup.log 2>&1
```

## Lisensi & Kredit

Proyek internal TEFA Automotive. Tidak untuk distribusi publik.