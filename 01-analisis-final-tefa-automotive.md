**ANALISIS FINAL SISTEM**

**TEFA AUTOMOTIVE**

*Company Profile & Service Management System*

| **Dokumen** | **Keterangan** |
| --- | --- |
| Status | Finalisasi kebutuhan dan ruang lingkup V1.0 — **disetujui & ditandatangani client**; revisi PKB cetak & mekanik view-only berasal langsung dari client setelah proposal diajukan |
| Tanggal | 24 September 2026 |
| Tujuan | Acuan bersama client dan pegangan pribadi sebelum & selama implementasi |
| Platform | Website responsif + Android Kotlin native |
| Konteks | Proyek individu — tugas ujikom RPL, deadline 2 minggu, dikerjakan dengan bantuan AI |
| Prinsip | Sederhana di depan, serius di belakang |
| Dokumen terkait | File 2 — Database & API Spesifikasi Teknis Final (ERD, SQL, function/trigger, API Contract lengkap) |

**Dokumen ini mencakup proses bisnis, keberfungsian fitur, UI/UX, keamanan, data, testing, dan kesiapan operasional. Detail teknis (SQL, JSON API, kode) sengaja tidak diulang di sini — semua ada di File 2.**

# 1. Ringkasan Eksekutif

TEFA Automotive membutuhkan sistem yang bukan hanya mampu menyimpan data servis, tetapi menghubungkan proses kerja bengkel dari customer datang sampai transaksi selesai. Sistem diarahkan untuk menggantikan pencatatan yang bergantung pada Google Form/Google Sheets dengan alur yang terintegrasi, mudah digunakan, dan dapat ditelusuri.

Produk terdiri dari website sebagai sistem utama dan aplikasi Android sebagai pendamping operasional. Website mencakup company profile dan area internal untuk Admin/CS, Mekanik, Kasir, Head of TEFA, serta Super Admin. Android difokuskan pada pekerjaan yang membutuhkan mobilitas, terutama pekerjaan mekanik dan dokumentasi foto — Android tidak menyalin seluruh fitur website.

Kualitas produk tidak diukur dari database saja. Keberhasilan V1.0 ditentukan oleh tiga lapisan yang harus berjalan bersama: proses bisnis yang benar, fitur yang benar-benar berfungsi sampai selesai, dan UI/UX yang mudah dipahami oleh pengguna non-IT (Admin/CS di sini berlatar automotive, bukan IT).

Prinsip desain: sistem terasa sederhana bagi pengguna, sementara validasi, transaksi, audit, keamanan, dan struktur data di belakangnya tetap kuat.

Karena dikerjakan sebagai proyek individu dalam waktu 2 minggu untuk client nyata, scope V1.0 sengaja dijaga tetap **realistis untuk dikerjakan sendiri** — modul yang berisiko memakan waktu besar tanpa menambah nilai inti (pengadaan formal ke vendor) sudah disederhanakan sejak dokumen ini disusun, bukan dipotong di tengah jalan.

Pengembangan dimulai dengan **data jasa & sparepart contoh (dummy)** mengikuti struktur yang sudah dirancang di File 2 — data asli dari client (harga, daftar sparepart, item mana yang wajib difoto, dsb) diinput menyusul lewat halaman master data begitu diterima, **tanpa mengubah struktur sistem sama sekali**. Ini dipilih supaya pengumpulan data dari client tidak menghambat mulai coding.

# 2. Latar Belakang dan Masalah

Sistem lama memiliki keterbatasan ketika data customer, kendaraan, servis, sparepart, pembayaran, dan riwayat harus dikelola berulang menggunakan Google Form/Sheet. Sistem baru memperjelas perpindahan pekerjaan antar-role dan mengurangi pencatatan manual.

| **Masalah** | **Dampak** | **Arah Solusi** |
| --- | --- | --- |
| Data customer/kendaraan berulang | Duplikasi dan riwayat sulit ditelusuri | Master customer-kendaraan terhubung |
| Stok manual | Risiko stok tidak sesuai | Stok berbasis pemakaian aktual + kartu stok (stock movement) |
| Status pekerjaan kurang jelas | Miskomunikasi | Status proses yang sederhana dan bertahap |
| Bukti pekerjaan tidak terstruktur | Customer sulit melihat bukti | Dokumentasi foto per item yang wajib (selektif) |
| Struk manual | Lambat dan rawan salah | Generate struk digital/cetak otomatis |
| Tidak ada reminder | Customer dapat lupa servis | Reminder 6 bulan otomatis terjadwal |
| Hak akses tidak terpisah | Risiko human error | RBAC + audit trail |

# 3. Tujuan Produk

- Menyatukan administrasi, pekerjaan mekanik, dokumentasi, stok, pembayaran, riwayat, dan reminder dalam satu sistem.
- Membuat alur **Customer → Admin/CS → Mekanik → Kasir → Riwayat & Reminder** yang jelas dan bertahap.
- Memastikan fitur penting dapat digunakan sampai selesai (end-to-end), bukan hanya tampil di UI.
- Menyediakan UI/UX sederhana dan konsisten untuk pengguna berlatar otomotif, bukan latar belakang IT.
- Menyediakan dokumentasi pekerjaan yang dapat ditampilkan pada struk/digital receipt — sekaligus jadi bukti portofolio kerja siswa TEFA.
- Menjaga integritas stok berdasarkan pemakaian sparepart aktual, tanpa proses pengadaan formal yang memberatkan.
- Menyediakan company profile dan informasi layanan publik (tanpa harga).
- Menyediakan Android sebagai pendamping operasional tanpa menggandakan seluruh kompleksitas website.
- Dapat diselesaikan dan dikuasai penuh oleh satu pengembang dalam waktu 2 minggu dengan bantuan AI, tanpa biaya langganan apa pun.

# 4. Prinsip Perancangan

| **Prinsip** | **Implementasi** |
| --- | --- |
| User first | Bahasa dan alur mengikuti pekerjaan pengguna (Admin automotive), bukan istilah database/DO/WO/PI. |
| Simple front, serious back | Form ringkas satu layar; validasi, transaksi, audit, dan relasi data tetap kuat di belakang. |
| End-to-end | Fitur utama memiliki awal, proses, hasil, dan status akhir yang jelas. |
| No dead end | Stok kurang, foto belum lengkap, dan pembayaran gagal memiliki penanganan, bukan jalan buntu. |
| Progressive disclosure | Informasi teknis hanya muncul ketika dibutuhkan. |
| Mobile ready | Website responsif; mekanik dapat memakai Android untuk sebagian kerjanya. |
| Traceable | Aksi penting dapat ditelusuri berdasarkan user dan waktu (audit trail + status history). |
| Scope controlled | Fitur yang berisiko memperlambat penyelesaian (PO/vendor formal) sudah disederhanakan sejak awal, bukan dipotong darurat. |
| Solo-dev feasible | Setiap keputusan scope mempertimbangkan kemampuan satu pengembang menyelesaikannya dalam 2 minggu. |

# 5. Ruang Lingkup Produk V1.0

| **Area** | **Fitur** | **Prioritas** |
| --- | --- | --- |
| Public Website | Beranda, Tentang, Layanan (tanpa harga), Galeri, Kontak | Wajib |
| Authentication | Login, logout, RBAC (Sanctum, satu strategi web+Android) | Wajib |
| Customer & Vehicle | Cari, tambah, edit sesuai hak akses, riwayat per kendaraan | Wajib |
| Servis Baru | Keluhan, pemeriksaan, jasa, sparepart, estimasi — 1 form oleh Admin automotive | Wajib |
| PKB | Pembuatan, assign/reassign mekanik, status bertahap, cancel/force-cancel, **cetak PKB (kertas kerja untuk mekanik)** | Wajib |
| Progres Pekerjaan | Dicatat oleh **Admin/Kasir** (bukan mekanik) berdasarkan laporan mekanik: part aktual, dokumentasi foto selektif, validasi selesai | Wajib |
| Mechanic (view-only) | Mekanik hanya **melihat** tugas (Aktif) dan riwayat pekerjaannya sendiri (Riwayat) — tidak melakukan input apa pun | Wajib |
| Cashier | Checklist kelengkapan dokumentasi, pembayaran, struk cetak/PDF | Wajib |
| Stock | Stok, minimum stock, indikator stok menipis, kartu stok (stock movement) | Wajib |
| History | Riwayat servis kendaraan/customer | Wajib |
| Reminder | Jadwal 6 bulan otomatis + kirim manual via wa.me | Wajib |
| User Management | Kelola user, reset password manual (Super Admin) | Wajib |
| Reports | Rekap servis, pembayaran, stok, dokumentasi | Wajib |
| Android | Login, lihat tugas (Aktif) & riwayat pekerjaan (Riwayat), detail PKB — **seluruhnya view-only**, khusus Mekanik | Wajib |
| Vendor & PO formal | Proses pengadaan ke vendor dengan dokumen terpisah | **Dihapus dari V1.0** — cukup indikator stok menipis + restock manual |
| WhatsApp Business API | Integrasi API resmi otomatis penuh | Lanjutan (di luar V1.0) |
| Customer App | Akun customer dengan login | Di luar V1.0 |

> **Perubahan penting dari draf awal:** modul Vendor & PO formal (tabel pengadaan, penerimaan barang bertahap) dihapus sepenuhnya dari V1.0. Kekurangan stok cukup ditandai sebagai status PKB `WAITING_PARTS` dan indikator "stok menipis" di dashboard; pemesanan ke pemasok tetap dilakukan di luar sistem (telepon/WA manual), sesuai kenyataan operasional TEFA saat ini. Ini memangkas satu domain penuh supaya realistis dikerjakan sendiri dalam 2 minggu.

# 6. Aktor dan Hak Akses

| **Role** | **Tanggung Jawab** | **Akses** |
| --- | --- | --- |
| Customer | Informasi publik; menerima struk, dokumentasi, reminder | Tanpa login |
| Admin / CS | Customer, kendaraan, servis (diagnosis langsung karena berlatar automotive), jasa/part, PKB, assign mekanik, **cetak PKB, restock manual, input progres pekerjaan mekanik (part aktual, dokumentasi, validasi selesai) berdasarkan laporan mekanik** | Operasional awal |
| Mekanik | **Melihat** (bukan menginput) tugas yang ditugaskan dan riwayat pekerjaannya sendiri; bekerja berpedoman pada PKB kertas yang dicetak Admin | PKB miliknya, view-only |
| Kasir | Checklist dokumentasi, pembayaran, struk, kirim reminder WhatsApp, **juga dapat menginput progres pekerjaan mekanik seperti Admin bila diperlukan** | Transaksi siap bayar (scoped) |
| Head of TEFA | Monitoring dan laporan | View/monitoring saja |
| Super Admin | User, role, master data, konfigurasi, log, force-cancel darurat | Administrasi penuh sistem |

Super Admin tidak menjadi bagian dari alur servis harian. Sistem harus tetap berjalan ketika developer/Super Admin tidak berada di lokasi. Detail permission per fitur: lihat Role & Permission Matrix di File 2 bagian 5.

# 7. Alur Bisnis Final

1. Customer datang dan menyampaikan keluhan.
2. Admin/CS mencari customer dan kendaraan atau membuat data baru.
3. Admin (berlatar automotive) langsung menentukan jasa dan sparepart yang sesuai berdasarkan keluhan + pengecekan fisik — tanpa perlu bantuan sistem saran, karena Admin sudah menguasai teknisnya.
4. Sistem menghitung estimasi biaya dan memeriksa stok tiap sparepart yang dipilih.
5. Jika stok mencukupi, PKB langsung berstatus menunggu mekanik. Jika stok kurang, PKB berstatus menunggu part — Admin melakukan restock manual (di luar sistem, ke pemasok) dan meng-update stok begitu barang datang.
6. PKB ditugaskan kepada mekanik (bisa direassign kapan saja jika perlu, progres lama tetap dipertahankan).
7. **Admin mencetak PKB sebagai kertas kerja dan menyerahkannya ke mekanik** — mekanik tidak memegang device saat bekerja, sehingga bekerja berpedoman pada kertas ini.
8. Mekanik mengerjakan servis sesuai kertas PKB, mencatat/melaporkan secara lisan atau tulisan tangan sparepart yang benar-benar digunakan (qty aktual, bisa berbeda dari rencana awal) serta progres pekerjaan.
9. **Admin atau Kasir** — berdasarkan laporan mekanik — menginput qty aktual ke sistem, mengunggah dokumentasi foto pada item yang memang diwajibkan oleh master data, dan menandai pekerjaan selesai di sistem.
10. Stok berkurang otomatis berdasarkan pemakaian aktual (bukan rencana awal) begitu progres diinput.
11. Kasir melakukan checklist kelengkapan dokumentasi wajib.
12. Jika dokumentasi wajib lengkap, pembayaran diproses normal; jika belum, kasir mendapat peringatan dan tetap bisa lanjut dengan konfirmasi eksplisit.
13. Kasir menerima pembayaran (tunai/nontunai) dan sistem menerbitkan struk (cetak/PDF).
14. Riwayat servis disimpan dan reminder 6 bulan dijadwalkan otomatis. **Riwayat pekerjaan ini juga muncul di "Riwayat PKB" milik mekanik yang bersangkutan** (dapat dilihat mekanik sendiri lewat web/Android, sifatnya view-only).
15. Kasir membuka WhatsApp dengan pesan terisi (link `wa.me`) untuk mengirim struk dan info reminder ke nomor customer.

Jika PKB dibatalkan sebelum dikerjakan, Admin/Super Admin dapat membatalkan langsung. Jika pembatalan darurat terjadi setelah pekerjaan mulai berjalan, hanya Super Admin yang bisa melakukan force-cancel, dan stok yang sudah terpakai otomatis dikembalikan.

# 8. Aturan Bisnis Penting

| **Aturan** | **Ketentuan** |
| --- | --- |
| Interaksi mekanik dengan sistem | Mekanik **tidak memegang device saat bekerja** — bekerja berpedoman pada PKB kertas. Seluruh input progres (part aktual, dokumentasi, validasi selesai) dilakukan oleh Admin/Kasir berdasarkan laporan mekanik, bukan oleh mekanik sendiri. |
| Cetak PKB | Admin mencetak PKB sebagai kertas kerja setelah PKB dibuat dan mekanik ditugaskan. |
| Riwayat mekanik | Mekanik tetap bisa **melihat** (view-only) daftar tugas aktif dan riwayat pekerjaannya sendiri lewat web maupun Android, walau tidak menginput apa pun. |
| Stok | Tidak berkurang saat Admin memilih part (rencana); berkurang saat pemakaian aktual dikonfirmasi Admin/Kasir berdasarkan laporan mekanik. |
| Dokumentasi | Tidak semua item wajib difoto; master item (jasa/sparepart) menentukan kewajibannya. |
| Kasir | Tidak menilai kualitas teknis pekerjaan; hanya memeriksa kelengkapan dokumentasi wajib. |
| Pembayaran | Tetap bisa diproses walau dokumentasi belum lengkap — kasir mendapat peringatan dan wajib konfirmasi eksplisit; konfirmasi ini tercatat di audit trail (bukan dikunci total, supaya operasional tidak macet kalau dokumentasi sulit diambil). |
| Stok kurang | Ditandai sebagai status PKB, direstock manual oleh Admin — tidak ada modul pengadaan/vendor formal. |
| Harga | Item transaksi menyimpan harga saat transaksi (snapshot) agar riwayat tidak berubah ketika master harga berubah kemudian. |
| Pajak & Diskon | Kolom disiapkan di database, tidak diaktifkan/dipakai di V1.0. |
| Pembatalan | PKB yang sudah berdampak pada stok/pembayaran tidak dihapus sembarangan — pembatalan setelah pekerjaan berjalan wajib lewat force-cancel Super Admin dengan reversal stok. |
| Reassign mekanik | Boleh dilakukan kapan saja termasuk saat pekerjaan sedang berjalan; progres yang sudah ada tetap dipertahankan. |
| Akun | Setiap user memiliki akun sendiri agar aktivitas dapat diaudit; tidak ada akun bersama. |
| Reset password | Tidak ada alur lupa password via email di V1.0 — direset manual oleh Super Admin. |

# 9. Analisis UI/UX

UI/UX merupakan bagian utama keberhasilan produk. Pengguna operasional (Admin/CS berlatar automotive, Mekanik, Kasir) tidak dipaksa memahami istilah teknis backend. Istilah teknis tetap dipakai di kode/database, sedangkan antarmuka memakai bahasa kerja sehari-hari.

## 9.1 Prinsip UI

- Navigasi konsisten dan posisi tombol utama mudah diprediksi.
- Satu halaman memiliki satu tujuan utama.
- Status menggunakan bahasa manusia: "Menunggu Part", "Menunggu Dikerjakan", "Sedang Dikerjakan", "Menunggu Pembayaran", "Selesai" — bukan nama ENUM database.
- Form panjang dibagi menjadi bagian yang mudah dipindai, tapi tetap dalam **satu layar** untuk input servis (tidak dipecah jadi banyak tahap terpisah, karena Admin sudah tahu persis apa yang perlu diisi).
- Customer, kendaraan, total, status, dan tindakan berikutnya selalu terlihat.
- Validasi muncul dekat field yang bermasalah.
- Website responsif pada desktop dan HP.
- Setiap proses memiliki loading, empty, success, error, dan confirmation state.
- Dashboard menjawab kebutuhan kerja hari itu, bukan sekadar menampilkan banyak grafik.

## 9.2 Servis Baru (Admin/CS)

| **Bagian** | **Elemen** | **Perilaku** |
| --- | --- | --- |
| Customer | Cari no. HP/nama + tombol Customer Baru | Pencarian cepat, autocomplete |
| Kendaraan | Plat, model, KM | Terhubung ke customer terpilih |
| Keluhan & Pemeriksaan | Textarea keluhan + hasil cek fisik | Diisi langsung oleh Admin |
| Jasa | Pilihan jasa + harga | Multi-item, search cepat |
| Sparepart | Part + qty rencana + sisa stok terlihat | Info stok real-time saat memilih |
| Estimasi | Total | Dihitung otomatis dari jasa+part |
| Tindakan | Simpan & Assign Mekanik | Membuat PKB langsung dengan status final |

Jika stok kurang, sistem memberi badge peringatan singkat di halaman — Admin tidak perlu mengisi form pengadaan panjang saat sedang melayani customer.

## 9.3 Halaman Mekanik (View-Only)

- Dashboard hanya menampilkan tugas milik mekanik yang login, terbagi 2 tab: **Aktif** (sedang berjalan) dan **Riwayat** (sudah selesai/lunas).
- Detail PKB menampilkan kendaraan, keluhan, pemeriksaan, jasa, part, status, dan dokumentasi foto (kalau sudah diunggah) — semuanya **hanya untuk dilihat**, tidak ada tombol input/upload/validasi.
- Mekanik tidak dapat mengubah data apa pun dari layar ini; ini murni jendela untuk melihat tugas dan riwayat kerjanya sendiri.

## 9.3a Halaman Input Progres Pekerjaan (Admin/Kasir)

Karena mekanik tidak memegang device saat bekerja, layar berikut dipakai oleh **Admin/CS atau Kasir** untuk mencatat progres berdasarkan laporan mekanik:

- Tombol "Mulai Dikerjakan" pada detail PKB, mengubah status jadi sedang dikerjakan.
- Form koreksi part aktual (qty terpakai bisa berbeda dari rencana), diisi Admin/Kasir sesuai laporan mekanik.
- Area upload foto dokumentasi per item yang wajib — foto diambil dari kamera HP Admin/Kasir sendiri, bukan HP mekanik.
- Tombol "Selesai" yang menampilkan ringkasan (jasa, part terpakai, dokumentasi) sebelum konfirmasi final, sama seperti rancangan sebelumnya, hanya berpindah aktor yang menjalankannya.

## 9.3b Cetak PKB

- Tombol "Cetak PKB" tersedia di halaman detail PKB (Admin/Super Admin) setelah mekanik ditugaskan.
- Format cetak berisi: nomor PKB, data customer & kendaraan, keluhan, hasil pemeriksaan, daftar jasa & sparepart yang direncanakan, nama mekanik yang ditugaskan, dan ruang kosong untuk catatan tulisan tangan mekanik (misalnya koreksi part yang benar-benar dipakai).

## 9.4 Halaman Kasir

- Daftar pekerjaan yang siap diperiksa (status menunggu pembayaran).
- Checklist dokumentasi wajib per item, ditampilkan sebagai centang/silang yang jelas.
- Foto dapat dibuka tanpa meninggalkan konteks transaksi.
- Tombol pembayaran tetap aktif walau dokumentasi belum lengkap — menekannya memunculkan peringatan berisi daftar item yang belum ada fotonya, dan meminta konfirmasi eksplisit sebelum transaksi benar-benar diproses (bukan dikunci total).
- Setelah pembayaran berhasil, struk dapat dilihat, disimpan, dan dicetak, serta tombol kirim WhatsApp langsung tersedia.

## 9.5 Pengalaman Customer

- Customer tidak perlu memiliki akun sama sekali.
- Company profile menjelaskan identitas, layanan (tanpa harga), galeri, dan kontak.
- Struk digital mudah dibaca melalui HP.
- Dokumentasi foto memiliki konteks item pekerjaan yang jelas, membangun kepercayaan.
- Pesan WhatsApp menggunakan template ringkas dan sopan.

# 10. Struktur Website

| **Halaman Publik** | **Tujuan** |
| --- | --- |
| Beranda | Identitas, layanan utama, ajakan kontak |
| Tentang | Profil TEFA Automotive |
| Layanan | Daftar jasa (tanpa harga) |
| Galeri | Portofolio yang layak dipublikasikan |
| Kontak | Kontak, lokasi, jam layanan |

| **Area Internal** | **Fokus** |
| --- | --- |
| Dashboard | Ringkasan pekerjaan sesuai role |
| Customer & Kendaraan | Master dan pencarian |
| Servis Baru / PKB | Intake sampai penugasan mekanik |
| Mekanik | Pekerjaan dan dokumentasi |
| Kasir | Checklist dan pembayaran |
| Stok | Master sparepart, indikator stok menipis, kartu stok |
| Riwayat / Reminder | After-service |
| Laporan | Monitoring |
| User / Log | Administrasi sistem (Super Admin) |

# 11. Android (Kotlin Native)

Android bukan salinan penuh website, dan sekarang bersifat **view-only** untuk Mekanik — sesuai keputusan bahwa mekanik tidak memegang device saat bekerja (berpedoman pada PKB kertas), tapi tetap butuh cara melihat tugas dan riwayat pekerjaannya sendiri kapan saja.

| **Layar** | **Fungsi** |
| --- | --- |
| Login | Autentikasi (Sanctum bearer token, sama dengan web) |
| Tugas Saya (Aktif) | Daftar PKB yang sedang berjalan, ditugaskan ke mekanik yang login — view-only |
| Riwayat Saya | Daftar PKB yang sudah selesai/lunas milik mekanik yang login — view-only |
| Detail PKB | Informasi kendaraan, keluhan, jasa, part, status, dan dokumentasi foto (kalau ada) — view-only, tidak ada tombol input |

Semua aksi (mulai kerja, update part aktual, upload dokumentasi, validasi selesai, cetak PKB) dipindah ke **website**, dijalankan oleh Admin/CS atau Kasir — bukan lagi bagian dari Android. Ini justru menyederhanakan scope Android dibanding rencana sebelumnya.

# 12. Database dan Logika Backend

Detail teknis penuh (skema SQL, function, trigger, transaksi, API Contract) ada di **File 2 — Database & API Spesifikasi Teknis Final**. Ringkasan kelompok entitas:

| **Kelompok** | **Entitas** |
| --- | --- |
| Identity | users, roles, permissions, role_permissions |
| Customer | customers, vehicles |
| Service | pkb, pkb_services, pkb_spareparts, pkb_status_histories |
| Inventory | spareparts, stock_movements (tanpa vendor/PO) |
| Documentation | documentations |
| Billing | invoices, payments |
| History | service_histories |
| Reminder | reminders |
| Audit | activity_logs |
| Penomoran | document_number_counters |

## 12.1 Transaction & Rollback

Pembuatan PKB dan proses kritis lain (finalisasi pekerjaan mekanik, pembayaran, force-cancel) diproses secara transaksional di level aplikasi (Laravel `DB::transaction()`). Jika salah satu langkah gagal, seluruh perubahan dalam transaksi tersebut di-rollback sehingga tidak meninggalkan data setengah jadi.

## 12.2 Trigger / Function

Perubahan status PKB dan pergerakan stok dicatat otomatis melalui trigger database (bukan ditulis manual dari kode aplikasi), sehingga tidak ada perubahan yang lolos tak tercatat. Function database dipakai untuk kalkulasi total transaksi dan pengecekan kelengkapan dokumentasi. Daftar lengkap function/trigger dan alasan pemilihannya: lihat File 2 bagian 4.

# 13. Dokumentasi Foto dan Storage

Foto disimpan sebagai file di **local disk server** (bukan cloud storage, sesuai keterbatasan anggaran), bukan binary langsung di tabel transaksi. Database menyimpan metadata: relasi item, lokasi file, ukuran, user pengunggah, dan waktu.

- Foto terkait langsung dengan item PKB (jasa atau sparepart tertentu), bukan satu foto umum per PKB.
- Hak akses foto mengikuti hak akses transaksi (role scoping yang sama).
- Backup mencakup database **dan** folder dokumentasi, dijalankan berkala (lihat bagian 16).

# 14. Pembayaran, Struk, dan WhatsApp

| **Tahap** | **Hasil** |
| --- | --- |
| Pembayaran berhasil | Payment tercatat |
| Invoice lunas | Status PKB berubah jadi lunas |
| Generate struk | Dokumen digital siap dilihat/disimpan/dicetak |
| Reminder | Jadwal +6 bulan dibuat otomatis |
| WhatsApp | Pesan terisi melalui link `wa.me`, dikirim manual oleh Kasir/Admin |

WhatsApp Business API otomatis penuh ditempatkan sebagai tahap lanjutan agar V1.0 tidak bergantung pada layanan berbayar.

# 15. Laporan dan Monitoring

| **Laporan** | **Isi** |
| --- | --- |
| Rekap Harian | PKB, transaksi, pendapatan, metode pembayaran |
| Rekap Bulanan | Jumlah servis, pendapatan, part terpakai |
| Riwayat Kendaraan | Seluruh servis kendaraan tertentu |
| Riwayat Customer | Kendaraan dan transaksi milik customer tertentu |
| Stok | Stok saat ini, minimum, riwayat pergerakan |
| Dokumentasi | Foto berdasarkan PKB/item/mekanik |
| Reminder | Terjadwal, terkirim, tertunda |

Dashboard dan laporan harus membantu pekerjaan nyata. Grafik tidak dibuat hanya untuk dekorasi.

# 16. Keamanan (Tanpa Budget Langganan Apa Pun)

Karena proyek ini tidak memiliki anggaran untuk layanan keamanan berbayar, seluruh langkah berikut dipilih karena **gratis dan sudah tersedia lewat framework/tooling yang dipakai**:

- Password di-hash bcrypt (bawaan Laravel).
- RBAC diterapkan di backend melalui Policy/Gate, bukan hanya menyembunyikan menu di frontend.
- Endpoint API memvalidasi user dan permission di setiap request (token Sanctum, bukan asumsi dari UI).
- Aksi penting dicatat dalam `activity_logs` (login, buat/batalkan PKB, pembayaran, reset password).
- Rate limiting login memakai middleware `throttle` bawaan Laravel.
- Validasi upload dokumentasi (mime type dan ukuran file dibatasi).
- `.env` tidak pernah masuk Git; `APP_DEBUG=false` di production.
- HTTPS aktif memakai SSL gratis (Let's Encrypt / bawaan hosting gratis).
- Backup database (`mysqldump`) dan folder dokumentasi terjadwal via cron, disimpan ke penyimpanan gratis.
- Token Sanctum tanpa masa berlaku otomatis, tapi bisa direvoke manual per-token (saat logout) atau oleh Super Admin.
- Session/token tidak dapat dipakai lintas role — permission diverifikasi ulang di setiap endpoint, bukan disimpan di sisi client.

# 17. Edge Case yang Wajib Ditangani

| **Kondisi** | **Perilaku Sistem** |
| --- | --- |
| Stok kurang | PKB berstatus menunggu part; Admin restock manual, tidak ada "stok fiktif" yang tetap terpotong. |
| Part tidak jadi dipakai | Qty aktual dapat dikoreksi mekanik sebelum validasi selesai. |
| Pekerjaan tambahan ditemukan saat servis | Dicatat sebagai item tambahan; idealnya dikonfirmasi ke customer sebelum dikerjakan. |
| Dokumentasi hilang/belum lengkap | Kasir melihat status belum lengkap dan mendapat peringatan; pembayaran tetap bisa diproses setelah konfirmasi eksplisit, dan konfirmasi itu tercatat di audit trail. |
| Pembayaran gagal | Invoice tetap berstatus belum lunas. |
| Sudah lunas | Edit transaksi dibatasi; koreksi tetap diaudit. |
| Stok fisik berbeda dari sistem | Koreksi wajib menyertakan alasan dan tercatat sebagai stock movement. |
| Upload foto gagal | User mendapat pesan error dan dapat mencoba ulang. |
| Akses role lain ke resource yang bukan haknya | Backend menolak (403), bukan hanya disembunyikan di UI. |
| Pembatalan setelah pekerjaan berjalan | Hanya Super Admin (force-cancel), stok yang terlanjur terpakai otomatis dikembalikan. |

# 18. Testing dan Acceptance Criteria

Fitur dianggap selesai ketika dapat digunakan oleh role yang tepat dari awal sampai menghasilkan output yang benar. Pengujian mencakup fungsi, UI/UX, validasi, permission, dan kondisi gagal — dilakukan **di akhir tiap fase pengembangan**, bukan ditumpuk di akhir proyek.

| **Area** | **Kriteria Penerimaan** |
| --- | --- |
| Login/RBAC | User hanya melihat dan menjalankan fungsi sesuai role. |
| Servis Baru | Admin dapat membuat transaksi lengkap dan PKB langsung dalam satu form. |
| Stok | Tidak berkurang saat rencana; berkurang sesuai pemakaian aktual mekanik. |
| Mechanic | Admin/Kasir dapat mencatat progres (part aktual, dokumentasi, validasi selesai) atas nama mekanik; mekanik sendiri hanya bisa melihat, tidak bisa mengubah apa pun. |
| Cetak PKB | PKB tercetak dengan data lengkap begitu mekanik ditugaskan. |
| Cashier | Kasir mendapat peringatan jelas jika dokumentasi belum lengkap, namun tetap bisa lanjut memproses pembayaran setelah konfirmasi eksplisit (tercatat di log). |
| Payment | Pembayaran sukses membuat transaksi lunas. |
| Receipt | Struk berisi data transaksi yang benar. |
| History | Servis selesai masuk riwayat kendaraan/customer. |
| Reminder | Reminder +6 bulan tercipta otomatis setelah lunas. |
| WhatsApp | Link `wa.me` berisi pesan dengan data yang benar. |
| Android | Mekanik dapat melihat tugas aktif dan riwayat pekerjaannya dari HP, tanpa bisa mengubah data apa pun. |
| Responsive | UI usable pada desktop dan mobile. |
| Error handling | Kegagalan tidak meninggalkan transaksi setengah jadi (transaction rollback bekerja). |

# 19. Quality Gate Sebelum Diserahkan ke Client

- Tidak ada fitur utama yang hanya berupa tombol tanpa proses backend.
- Tidak ada alur utama yang berhenti karena status/data tidak konsisten.
- UI/UX sudah dicoba sendiri dari sudut pandang Admin, Mekanik, dan Kasir.
- Form penting memiliki validasi dan pesan error jelas.
- Permission diuji dari frontend maupun langsung ke API (bukan cuma percaya UI).
- Stok diuji pada kondisi normal, kurang, berbeda, dan koreksi manual.
- Pembayaran diuji sukses dan gagal.
- Dokumentasi diuji upload, preview, kelengkapan, dan pembatasan akses.
- Struk diuji tampilannya di desktop dan mobile.
- Backup database dan file dokumentasi diuji proses pemulihannya minimal sekali.
- Data dummy/percobaan dipisahkan dari data operasional sebelum diserahkan ke client.

# 20. Pendekatan Pengembangan dengan Bantuan AI

Karena dikerjakan sendiri dengan tenggat 2 minggu, seluruh coding dibantu AI (gratis: mulai dari GitHub Copilot, lanjut ke tool open source seperti opencode jika limit tercapai) dengan pendekatan **generate per fase, bukan sekaligus**:

- Tiap fase disuapkan 3 bahan yang sama: **File 2 (spec teknis)**, **File 3 (Acuan Style & Desain Sistem — konsisten di semua fase)**, dan **File 4 (Prompt spesifik fase yang sedang dikerjakan)**.
- Setiap fase selesai di-generate, kode dijalankan dan dites dulu sebelum lanjut ke fase berikutnya — supaya kesalahan ketahuan lebih awal, bukan menumpuk di akhir.
- Tujuannya bukan cuma kecepatan, tapi supaya pengembang (saya) tetap menguasai alur dan bisa menjelaskan setiap bagian sistem — baik untuk sidang ujikom maupun tanggung jawab ke client yang membayar.
- Pendekatan ini bersifat tool-agnostic: kalau tool AI yang dipakai berganti di tengah jalan, tiga bahan yang sama tetap bisa dipakai tanpa kehilangan konsistensi.

# 21. Jadwal Implementasi (14 Hari)

| **Hari** | **Fokus** |
| --- | --- |
| 1-2 | Setup project, migrasi database final, seed data, Modul Auth (login/logout/me + RBAC) |
| 3-5 | Modul PKB — backend lengkap (create, assign, cancel, force-cancel) + halaman Admin "Servis Baru" |
| 6-7 | Modul Progres Pekerjaan (dijalankan Admin/Kasir: start kerja, update part aktual, upload dokumentasi, validasi selesai) + cetak PKB, dan halaman Riwayat/Tugas Mekanik (view-only) |
| 8-9 | Modul Kasir — checklist dokumentasi, generate invoice, pembayaran, cetak struk |
| 10 | Modul Stok (indikator menipis, restock manual), Riwayat Servis, Reminder 6 bulan |
| 11 | User Management (Super Admin) + halaman Company Profile publik |
| 12 | Aplikasi Android Kotlin native (scope Mekanik, view-only: login, tugas aktif, riwayat, detail)
| 13-14 | Testing end-to-end web+Android, checklist keamanan, deploy ke hosting gratis, persiapan demo ke client & sidang ujikom |

Urutan menempatkan fitur yang menghasilkan alur bisnis lengkap lebih dahulu (Auth → PKB → Mekanik → Kasir), baru fitur pendukung (Stok/Riwayat/Reminder, User Management, Android). Fitur tambahan tidak boleh mengorbankan penyelesaian alur inti ini.

# 22. Fitur Di Luar V1.0

- Modul Vendor & Purchase Order formal.
- WhatsApp Business API otomatis penuh.
- Aplikasi customer dengan login.
- Payment gateway online.
- Push notification customer.
- Multi-cabang.
- Offline-first penuh.
- AI diagnosis/rekomendasi servis otomatis.
- Cloud storage untuk dokumentasi (masih local disk di V1.0).

# 23. Kesimpulan dan Dasar Persetujuan

Analisis final ini menetapkan TEFA Automotive sebagai Service Management System yang menghubungkan customer, kendaraan, servis, mekanik, dokumentasi, stok, pembayaran, riwayat, dan reminder. Website menjadi produk utama, sedangkan Android menjadi pendamping operasional dengan scope terbatas pada kebutuhan mekanik.

Ukuran keberhasilan sistem bukan banyaknya tabel atau halaman, tetapi apakah pengguna dapat menyelesaikan pekerjaan dengan benar, cepat, dan mudah dipahami — terlebih sistem ini akan dipakai oleh client nyata yang membayar, bukan hanya untuk penilaian ujikom. Karena itu, setiap fitur utama harus memiliki proses backend yang benar, status jelas, validasi, penanganan error, dan hasil akhir yang dapat diverifikasi.

Dokumen ini menjadi dasar pembahasan bersama client untuk mengunci ruang lingkup V1.0 sebelum implementasi. Perubahan kebutuhan setelah scope dikunci sebaiknya dicatat sebagai perubahan requirement agar dampaknya terhadap waktu, UI/UX, database, dan testing dapat dihitung — mengingat waktu pengerjaan yang ketat (2 minggu).

# 24. Catatan Persetujuan Requirement

| **Pihak** | **Nama** | **Tanggal** | **Paraf/Tanda Tangan** |
| --- | --- | --- | --- |
| Client / TEFA Automotive |  |  |  |
| Project Developer |  |  |  |
| Pembimbing / Penanggung Jawab |  |  |  |

TEFA Automotive | Analisis Final Sistem V1.0 (Revisi 24 September 2026)
