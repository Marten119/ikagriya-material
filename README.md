# Sistem Rekapitulasi Material Masuk - PT. Ikagriya Darmapersada

Aplikasi web untuk mencatat, memantau, merekapitulasi, dan melaporkan material masuk
berdasarkan lokasi cluster, kategori material, volume, dan pemakaian.
Mendukung penelitian "Peningkatan Efisiensi Pemantauan Jumlah Material Masuk melalui
Pengembangan Sistem Rekapitulasi Berbasis Web pada PT. Ikagriya Darmapersada".

## STATUS PENTING
Seluruh kode di paket ini ditulis tanpa dapat dijalankan (lingkungan penulis tidak memiliki PHP/Composer/MySQL).
Hanya pemeriksaan statis yang dilakukan (kelengkapan rute, keseimbangan Blade dan kurung).
**Belum ada fitur yang terbukti berfungsi** sebelum diuji di Laragon. Lihat docs/CHECKLIST-UJI.md.

## Kebutuhan
- Laragon (PHP 8.3.x terdeteksi: 8.3.33), MySQL/MariaDB, Composer
- Ekstensi PHP: mbstring, openssl, pdo_mysql, xml, ctype, json, fileinfo, zip (Excel), gd (opsional)
- Laravel 13 (PHP >= 8.3). Cadangan: Laravel 12 jika paket Excel/PDF belum cocok dengan 13.
- Internet saat dipakai: Bootstrap, Bootstrap Icons, dan Chart.js dimuat dari CDN jsdelivr.

## Instalasi (Laragon, Windows)
1. Buat proyek:
       composer create-project laravel/laravel ikagriya-material
       cd ikagriya-material
       composer require maatwebsite/excel barryvdh/laravel-dompdf
   (Jika versi ditolak: composer create-project laravel/laravel:^12.0 ikagriya-material)
2. Ekstrak ZIP ini ke root proyek, pilih "timpa" (Replace) bila ditanya.
   File bawaan yang tertimpa: routes/web.php, app/Models/User.php, database/seeders/DatabaseSeeder.php.
3. Buat database kosong `ikagriya_material` (HeidiSQL/phpMyAdmin Laragon).
4. Edit `.env`:
       APP_NAME="Rekap Material Ikagriya"
       APP_TIMEZONE=Asia/Jakarta
       APP_LOCALE=id
       DB_CONNECTION=mysql
       DB_HOST=127.0.0.1
       DB_PORT=3306
       DB_DATABASE=ikagriya_material
       DB_USERNAME=root
       DB_PASSWORD=
       SEED_ADMIN_PASSWORD=isi_password_kuat_Anda
   Pastikan config/app.php membaca APP_TIMEZONE ('timezone' => env('APP_TIMEZONE', 'UTC')).
5. Jalankan:
       php artisan key:generate
       php artisan migrate
       php artisan db:seed
       php artisan serve        (atau buka http://ikagriya-material.test lewat Laragon)
6. Login: username `admin`, password sesuai SEED_ADMIN_PASSWORD. Segera buat akun pribadi dan ganti password.
7. (Opsional, uji coba) data berlabel DUMMY: php artisan db:seed --class=DemoDataSeeder
   Jangan dijalankan pada database produksi.

## Isi aplikasi
| Menu | Fungsi |
|---|---|
| Dashboard | Ringkasan periode, grafik jumlah transaksi, rekap per kategori, transaksi terbaru |
| Input Material Masuk | Form + tabel transaksi, nomor otomatis MM-YYYYMMDD-NNNN, edit/hapus sesuai izin |
| Data Material | Master material (kode unik, kategori, satuan standar, status) |
| Data Lokasi Cluster | Master cluster (kode unik, status) |
| Rekapitulasi | Filter gabungan; kelompok per material/kategori/cluster/periode |
| Laporan | Harian/mingguan/bulanan/tahunan/rentang; pratinjau, cetak, Excel, PDF |
| Manajemen Pengguna | Khusus admin; peran admin dan staff |

## Struktur kode (ringkas)
- app/Http/Controllers: Auth/LoginController, Dashboard, Recap, Report, User, Cluster, Material, MaterialReceipt
- app/Http/Requests: validasi sisi server tiap form
- app/Http/Middleware: EnsureRole, EnsureUserIsActive
- app/Policies/MaterialReceiptPolicy: izin edit/hapus transaksi
- app/Services: ReceiptService (simpan/ubah/hapus + log), RecapService (agregasi), ReportService, ActivityLogger
- app/Exports/ReportExport: ekspor Excel
- database/migrations dan seeders: skema, 13 kategori, 16 satuan, admin awal
- docs/: ERD, asumsi, checklist uji, catatan per langkah

## Keamanan yang diterapkan
CSRF (bawaan Laravel), password di-hash, login dibatasi 5 percobaan/menit, validasi server di semua form,
Eloquent/Query Builder dengan binding (tanpa input pengguna di SQL mentah), output Blade ter-escape,
middleware peran, kredensial hanya di .env, jejak audit transaksi (activity_logs).
Belum dilakukan: uji penetrasi, pembatasan IP, 2FA, backup otomatis.

## Belum dibuat
- Impor dari Excel perusahaan (file contoh belum tersedia, struktur sheet belum diketahui)
- Halaman untuk melihat activity_logs (data sudah tercatat di database)
- Alamat/logo perusahaan pada laporan
