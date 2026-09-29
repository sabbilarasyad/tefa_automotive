# Acuan Style & Desain Sistem
## TEFA Automotive (File 3 dari 5 — dipakai TETAP SAMA di semua fase)

**Fungsi dokumen ini:** disuapkan bersama File 2 (spec teknis) dan File 4 (prompt fase) ke AI apa pun yang dipakai generate kode, supaya hasil tiap fase konsisten gaya, struktur, dan konvensinya — walau digenerate terpisah-pisah dan mungkin gonta-ganti tool AI (Copilot → opencode, dst).

---

## 1. Stack & Alasan Singkat

| Layer | Pilihan | Alasan |
|---|---|---|
| Backend | Laravel (PHP) | Sudah dipelajari di sekolah, skema SQL di File 2 sudah cocok langsung |
| Frontend Web | **Blade + Tailwind CSS + Alpine.js** (bukan SPA/React/Vue terpisah) | Paling ringan untuk solo-dev 2 minggu, tanpa build tool rumit, tanpa perlu API terpisah untuk web (Android tetap konsumsi REST API yang sama) |
| Database | MySQL | Sesuai File 2 |
| Auth | Laravel Sanctum (bearer token, web dan Android sama) | Sudah final di API Contract Auth |
| Android | Kotlin native, arsitektur MVVM | Standar modern, didukung banyak referensi belajar gratis |
| HTTP Client Android | Retrofit + OkHttp | Paling umum dipakai bareng Sanctum bearer token |

> Web **tidak** memakai endpoint `/api/...` untuk render halaman biasa (pakai route Blade biasa + Controller yang langsung query Eloquent). Endpoint `/api/...` di File 2 disediakan supaya **Android** bisa konsumsi data yang sama. Ini menghindari kerja dua kali (bikin API dulu baru bikin web di atasnya) — web boleh langsung pakai Eloquent di controller, tanpa lewat lapisan API internal.

---

## 2. Struktur Folder Backend (Laravel)

```
app/
  Http/
    Controllers/
      Web/              # controller untuk halaman Blade (Admin, Mekanik, Kasir, dst)
      Api/               # controller khusus endpoint /api/... (dipakai Android)
      Public/            # controller company profile (guest)
    Requests/            # Form Request per aksi, contoh: StorePkbRequest.php
    Middleware/
  Models/                # 1 file = 1 tabel, singular PascalCase
  Policies/              # 1 file = 1 model yang butuh RBAC granular
  Services/              # logic bisnis lintas-tabel (lihat bagian 4)
  Observers/             # jika ada logic yang lebih pas jadi Observer daripada trigger DB
database/
  migrations/
  seeders/
resources/
  views/
    layouts/             # layout dasar per role (admin.blade.php, mekanik.blade.php, dst)
    public/              # beranda, tentang, layanan, galeri, kontak
    auth/                # halaman login
    admin/               # Servis Baru, daftar PKB, master data
    mekanik/
    kasir/
    superadmin/
routes/
  web.php                # route Blade
  api.php                # route khusus Android
```

---

## 3. Konvensi Penamaan

| Elemen | Aturan | Contoh |
|---|---|---|
| Tabel & kolom | `snake_case`, jamak untuk tabel | `pkb_spareparts`, `stock_qty` |
| Model | `PascalCase` tunggal | `Pkb`, `PkbSparepart`, `Documentation` |
| Controller | `PascalCase` + `Controller` | `PkbController`, `Api\PkbController` |
| Method controller CRUD standar | `index, store, show, update, destroy` | — |
| Method controller aksi khusus | kata kerja jelas, bukan generic | `assignMechanic`, `forceCancel`, `validateComplete` |
| Route web | `kebab-case`, mengikuti resource | `/admin/pkb/{pkb}/assign-mekanik` |
| Route api | sesuai File 2 apa adanya | `/api/pkb/{id}/force-cancel` |
| Form Request | `Store{Model}Request` / `Update{Model}Request` | `StorePkbRequest` |
| Service class (logic bisnis) | `{Domain}Service`, taruh di `app/Services` | `PkbWorkflowService`, `PaymentService`, `NumberGeneratorService`, `StockService`, `ReminderService` |
| Policy | `{Model}Policy` | `PkbPolicy`, `DocumentationPolicy` |
| Blade view file | `snake_case`, dikelompokkan per folder role | `admin/pkb/create.blade.php` |
| Variabel PHP | `camelCase` | `$estimatedTotal` |
| Migration | default Laravel (`timestamp_create_xxx_table.php`) | — |

---

## 4. Pola Service Layer & Transaction

**Aturan wajib:** logic bisnis lintas-tabel **tidak** ditulis langsung di Controller. Controller hanya menerima request tervalidasi lalu memanggil Service. Service yang membungkus `DB::transaction()`.

```php
// app/Services/PkbWorkflowService.php
class PkbWorkflowService
{
    public function create(array $data, User $creator): Pkb
    {
        return DB::transaction(function () use ($data, $creator) {
            DB::statement('SET @current_user_id = ?', [$creator->id]); // dibaca trigger di File 2
            $number = app(NumberGeneratorService::class)->next('PKB');
            // ... insert pkb, pkb_services, pkb_spareparts, cek stok, tentukan status awal
            return $pkb;
        });
    }
}
```

Operasi yang **wajib** lewat pola ini (daftar sama seperti File 2 bagian 4.3): buat PKB, finalisasi pekerjaan mekanik, proses pembayaran, force-cancel. Jangan ditulis inline di controller meskipun terlihat "cuma sedikit baris".

---

## 5. Pola RBAC (Policy/Gate)

Semua endpoint web maupun api **wajib** dicek lewat Policy, bukan `if ($user->role === 'ADMIN_CS')` yang bertebaran di controller.

```php
// app/Policies/PkbPolicy.php
class PkbPolicy
{
    public function forceCancel(User $user, Pkb $pkb): bool
    {
        return $user->role->name === 'SUPER_ADMIN';
    }

    public function viewOwn(User $user, Pkb $pkb): bool
    {
        return $pkb->mechanic_id === $user->id;
    }
}

// Dipakai di controller:
$this->authorize('forceCancel', $pkb);
```

Permission mengikuti persis tabel di File 2 bagian 5 — kalau ragu suatu aksi butuh policy apa, cek matriks itu dulu sebelum menulis kode.

---

## 6. Format Response API (ringkas, detail di File 2/Auth Contract)

Semua endpoint `/api/...` **wajib** memakai amplop response ini, tidak boleh ada endpoint yang format-nya beda sendiri:

```json
{ "success": true, "data": { }, "message": "..." }
{ "success": true, "data": [ ], "meta": { "current_page": 1, "per_page": 15, "total": 42 } }
{ "success": false, "message": "..." }
{ "success": false, "message": "Validasi gagal", "errors": { "field": ["pesan"] } }
```

Buat `app/Http/Responses/ApiResponse.php` berisi helper statis (`ApiResponse::success($data, $message)`, `ApiResponse::error($message, $errors)`) supaya tidak ada controller yang menulis array response manual berulang-ulang dengan struktur yang sedikit beda.

---

## 7. Konvensi Validasi

- Semua input lewat **Form Request class**, bukan `$request->validate()` inline di controller.
- Pesan error dalam Bahasa Indonesia (audiens Admin/CS/Mekanik/Kasir bukan penutur teknis Inggris).
- Validasi bisnis yang butuh query database (contoh: cek stok cukup) tetap di Form Request lewat `withValidator()`, bukan dipindah ke Controller.

```php
class StorePkbRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'complaint' => 'required|string',
            'services' => 'required|array|min:1',
            'spareparts' => 'nullable|array',
        ];
    }
}
```

---

## 8. Konvensi Frontend Web (Blade + Tailwind + Alpine.js)

- Satu layout dasar per role (`layouts/admin.blade.php`, dst) — jangan duplikasi `<html>`/navbar di tiap halaman.
- Komponen berulang (badge status, kartu ringkasan) jadi Blade Component (`<x-status-badge :status="$pkb->status" />`), bukan copy-paste HTML.
- Alpine.js dipakai untuk interaktivitas ringan saja (toggle, preview foto sebelum upload, dropdown) — bukan untuk logic bisnis. Kalau logic sudah mulai kompleks, itu tandanya harus jadi request ke server, bukan ditambal di Alpine.
- Warna status mengikuti bahasa manusia dari File 1 bagian 9.1: `Menunggu Part` (kuning), `Menunggu Dikerjakan` (abu), `Sedang Dikerjakan` (biru), `Menunggu Pembayaran` (oranye), `Selesai/Lunas` (hijau), `Dibatalkan` (merah) — dipetakan sekali di satu Blade Component, dipakai di semua halaman, supaya warnanya konsisten di seluruh sistem.
- Tombol yang butuh konfirmasi ekstra (misal bayar saat dokumentasi belum lengkap) **tetap aktif**, tapi menekannya memicu modal konfirmasi berisi daftar item yang belum lengkap — kasir harus klik "Tetap Lanjut" secara eksplisit sebelum transaksi benar-benar diproses. Pola ini dipakai untuk semua kasus soft-block (bukan tombol `disabled` total), supaya operasional tidak macet tapi tetap ada jejak konfirmasi.

---

## 9. Konvensi Android (Kotlin Native)

```
com.tefaautomotive.app/
  data/
    remote/         # Retrofit service interfaces, DTO
    repository/      # implementasi repository (read-only), mapping DTO -> domain model
  domain/
    model/           # data class murni Kotlin, tanpa dependency Android
  ui/
    login/
    pkblist/         # tab Aktif & Riwayat dalam satu layar (view-only)
    pkbdetail/       # detail PKB + dokumentasi foto (kalau ada) — semuanya view-only
    common/          # komponen UI reusable (status badge, dst — mirror Blade Component)
```

- Arsitektur **MVVM**: `ViewModel` per layar, `Repository` sebagai satu-satunya sumber data (tidak ada `ViewModel` yang panggil Retrofit langsung).
- **Android sekarang murni view-only** (Mekanik tidak input apa pun) — jadi tidak perlu form/upload/POST/PATCH sama sekali di sisi Android, cukup GET untuk daftar tugas aktif, riwayat, dan detail. Ini menyederhanakan Repository jadi hanya butuh method read.
- Token disimpan di `EncryptedSharedPreferences` (sesuai API Contract Auth), diakses lewat satu class `TokenManager`, tidak diakses langsung berulang di banyak tempat.
- Nama package & class `PascalCase`/`camelCase` standar Kotlin.
- Response API di-parse ke DTO yang bentuknya **persis** mengikuti amplop response di bagian 6 (`ApiResponse<T>` generic wrapper), supaya error handling di Android konsisten dengan web.
- Status PKB dipetakan ke warna/label yang **sama** dengan web (bagian 8) — kalau di web "Menunggu Part" warnanya kuning, di Android juga kuning.

## 9a. Pola Cetak Dokumen (PKB & Struk)

Dua dokumen dicetak dari sistem: **PKB** (kertas kerja untuk mekanik, dicetak Admin) dan **Struk/Invoice** (dicetak Kasir). Keduanya pakai pola yang sama supaya konsisten:

- Satu Blade layout khusus cetak (`layouts/print.blade.php`) — tanpa navbar/sidebar, hanya konten + sedikit CSS print-friendly (`@media print`).
- Endpoint `GET /api/pkb/{id}/print` dan `GET /api/invoices/{id}/print` mengembalikan data yang sama persis dengan yang dipakai halaman cetak versi web (jangan buat 2 sumber data berbeda untuk 1 dokumen).
- Tombol cetak memicu `window.print()` dari halaman cetak (bukan generate PDF di server), kecuali kalau kasir/admin secara eksplisit minta simpan sebagai PDF (baru pakai dompdf/barryvdh di kasus itu).

---

## 10. Konvensi Penulisan Kode & Komentar

- Komentar dipakai untuk menjelaskan **kenapa**, bukan mengulang apa yang sudah jelas dari kode itu sendiri.
- Setiap Service class dikasih komentar blok singkat di atas class: fungsinya apa, dipanggil dari mana.
- Bagian kode yang berhubungan langsung dengan keputusan di File 2 (misalnya `SET @current_user_id`) **wajib** dikomentari merujuk ke bagian di File 2, supaya kalau lupa kenapa baris itu ada, tinggal buka dokumennya lagi.
- Tidak perlu komentar berlebihan di kode yang sudah jelas dari penamaan variabel/fungsi yang baik.

---

## 11. Git & Commit (Proyek Individu, Tetap Rapi)

- Commit kecil per unit kerja selesai, bukan satu commit raksasa per fase.
- Format pesan commit: `[modul] penjelasan singkat` — contoh: `[pkb] tambah endpoint force-cancel dengan reversal stok`.
- Branch `main` selalu dalam kondisi bisa dijalankan; kalau perlu eksperimen besar, boleh branch terpisah lalu merge setelah dites.
- README project diisi minimal: cara install lokal, cara migrate+seed, akun demo tiap role — berguna juga untuk dokumentasi ke client/penguji.

---

## 12. "Definition of Done" Umum (berlaku semua fase)

Sebelum sebuah fase dianggap selesai (kriteria spesifik per fase ada di File 4), semua ini harus terpenuhi:

- [ ] Endpoint/halaman sudah dites langsung (bukan cuma dibaca kodenya) dengan akun role yang sesuai
- [ ] Endpoint/halaman ditolak dengan benar (403) saat dicoba dari role yang tidak berhak
- [ ] Response API mengikuti format standar (bagian 6)
- [ ] Operasi kritis (lihat bagian 4) benar-benar dibungkus transaksi, dicoba gagal di tengah untuk pastikan rollback jalan
- [ ] Tidak ada logic bisnis yang nyasar ditulis langsung di Controller/Blade
- [ ] Status/warna baru (jika ada) memakai Blade Component/mapping yang sama di web & Android

---

## 13. Palet Warna (Sementara — Oranye Otomotif, Masih Bisa Berubah)

Warna yang **sedang dipakai saat ini**: **oranye otomotif** sebagai warna aksen/brand utama (CTA, highlight, logo), dikombinasikan dengan **charcoal/navy gelap** sebagai warna dasar UI (nav, header, teks penting) — kombinasi klasik dunia bengkel/otomotif (mirip warna rambu keselamatan dipadukan aspal/logam gelap), sekaligus kontras yang baik untuk keterbacaan.

**Belum dikunci final** — masih terbuka untuk berubah kalau ada usulan lain atau ternyata kurang pas setelah dilihat langsung di sistem. Supaya gampang diganti kapan saja, tetap dipakai lewat **CSS variable terpusat**:

```css
/* resources/css/app.css — pilihan saat ini, belum final */
:root {
  --color-primary: #1e293b;    /* charcoal/navy gelap — bisa berubah */
  --color-accent: #f97316;     /* oranye otomotif — bisa berubah */
  --color-bg: #f9fafb;
  --color-text: #1f2937;
}
```

Konfigurasi `tailwind.config.js` merujuk ke variable ini (`colors: { primary: 'var(--color-primary)', accent: 'var(--color-accent)' }`), sehingga semua class Tailwind (`bg-primary`, `text-accent`, dst) otomatis konsisten di seluruh Blade.

Untuk Android, definisikan warna senada di `res/values/colors.xml` dengan nama yang sama persis (`color_primary`, `color_accent`) supaya visual Android dan web senada.

**Warna status** (bagian 8) tidak terpengaruh keputusan ini — tetap dipakai apa adanya karena fungsinya penanda status, bukan identitas brand.

---

### Catatan Penutup
Dokumen ini "hidup" — kalau di tengah jalan ketemu konvensi baru yang lebih baik, update di sini juga (bukan cuma diterapkan diam-diam di satu fase saja), supaya fase-fase berikutnya tetap konsisten mengikuti yang terbaru.
