# Website MPK Herbattaubah (Laravel)

Website baru MPK (Management Pengembangan Karir): landing page publik + area admin untuk mengelola member, omzet, dan bonus. Dibangun ulang dengan Laravel 12 dari versi PHP biasa sebelumnya.

## Isi

**Website publik** (`/`)
- Profil MPK, keunggulan, daftar produk (filter kategori + pencarian, tombol pesan via WhatsApp)
- Pelatihan penyembuhan ala sehat islami, bisnis plan / ajakan jadi mitra
- Kontak (alamat, WA, telepon, email, peta), tombol WhatsApp melayang

**Admin** (`/admin`)
- Dashboard: member aktif, member baru, omzet bulan ini, peringkat bonus
- Member: cari, filter, tambah/ubah/hapus, sponsor (upline), pohon jaringan, unduh CSV
- Omzet / belanja: catat belanja per member, rekap per bulan
- Bonus bulanan: personal, group, bonus, voucher, royalty (rumus dari *Simulasi MPK Januari.xlsx*), unduh CSV
- Produk: tambah/ubah, harga member/jual/luar Jabodetabek, unggah gambar, sembunyikan
- Impor data dari CSV (member & belanja), pengaturan profil/kontak/aturan bonus, akun admin

## Struktur penting

| Bagian | Lokasi |
| --- | --- |
| Rute | `routes/web.php` |
| Rumus bonus | `app/Services/BonusCalculator.php` |
| Impor CSV | `app/Services/CsvImporter.php` |
| Controller admin | `app/Http/Controllers/Admin/` |
| Tampilan (Blade) | `resources/views/` |
| CSS (tanpa proses build) | `public/assets/site.css`, `public/assets/admin.css` |
| Tabel database | `database/migrations/` |
| Produk awal & pengaturan default | `database/seeders/` |
| Tes | `tests/Feature/` |

## Uji di komputer sendiri

Butuh PHP 8.2+ dan Composer.

```bash
composer install
cp .env.example .env            # bawaan memakai SQLite
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed      # tabel + pengaturan default + 29 produk awal
php artisan db:seed --class=DemoSeeder   # opsional: 60 member & transaksi fiktif
php artisan mpk:admin admin@contoh.com   # buat akun admin (password ditanya)
php artisan serve
```

Buka http://127.0.0.1:8000 dan http://127.0.0.1:8000/admin.

Menjalankan tes: `php artisan test`.

## Pasang di hosting (Hostinger / cPanel)

1. Buat database MySQL dan user-nya di panel hosting.
2. Unggah seluruh folder proyek (atau `git clone` lewat SSH), lalu jalankan `composer install --no-dev --optimize-autoloader`.
3. Arahkan *document root* domain ke folder `public/`.
   Bila tidak bisa (mis. harus di `public_html/`), file `.htaccess` di root proyek akan meneruskan semua permintaan ke `public/`.
4. Salin `.env.example` menjadi `.env`, lalu ubah:
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://www.mpk-herbattaubah.com
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=nama_database
   DB_USERNAME=user_database
   DB_PASSWORD=password_database
   ```
5. Jalankan sekali lewat SSH/Terminal hosting:
   ```bash
   php artisan key:generate
   php artisan migrate --seed --force
   php artisan mpk:admin EMAIL_ANDA
   php artisan config:cache && php artisan route:cache && php artisan view:cache
   ```
6. Pastikan folder `storage/`, `bootstrap/cache/`, dan `public/uploads/` bisa ditulis server.
7. Login di `/admin`, lengkapi Pengaturan dan harga produk.

## Memindahkan data dari Excel

Simpan sheet member dan belanja sebagai CSV, lalu unggah di Admin → Impor Data.
- Member: `kode, nama, hp, email, alamat, kode_sponsor, status, tgl_gabung`
- Belanja: `kode, tanggal, nominal, keterangan` (tanggal `2026-01-15` atau `15/01/2026`)

Pemisah koma atau titik koma sama-sama bisa.

## Catatan

- Gambar produk awal masih diambil dari website lama. Unggah ulang di Admin → Produk sebelum website lama dimatikan.
- Harga produk awal sengaja kosong (tampil "Tanya harga") sampai diisi admin.
- Gambar yang diunggah disimpan di `public/uploads/` agar jalan di hosting tanpa symlink `storage`.
