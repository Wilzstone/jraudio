# JR Audio Laravel + Smart Recommendation (prototipe tahap awal)

**Penting:** Project ini adalah **salinan pengembangan**, BUKAN file untuk langsung menimpa `C:\laragon\www\jraudiolocal`. Simpan website lama dan database `jr_audio` sebagai cadangan. Aplikasi Laravel ini memakai `jr_audio_dev`.

## Status migrasi

- Framework: Laravel 12 (PHP minimal 8.2), route di `routes/web.php`, controller di `app/Http/Controllers`, Eloquent di `app/Models`, dan tampilan Blade di `resources/views`.
- Web publik: Home, Tentang, Barang, Paket, Galeri, Partner, Lokasi, Kontak memakai tampilan & aset dari ZIP asli.
- Internal: login Laravel dengan role Owner/Karyawan, CRUD barang, pelanggan, karyawan; transaksi, pembayaran, pengembalian, invoice, laporan. Untuk mempertahankan SQL dan aturan stok lama, sejumlah query yang kompleks masih berjalan lewat repository PDO yang **koneksinya dikelola Laravel** di `app/Repositories/Legacy`. Ini tahap migrasi; repository ini bisa di-refactor ke Query Builder/Eloquent kemudian.
- Smart Recommendation: route `GET/POST /rekomendasi`; **rule-based** (bukan model machine learning / AI generatif). Menggunakan kategori barang, stok saat ini, harga satuan, dan jenis acara. Hanya menampilkan saran + estimasi, **tidak membuat booking, tidak menyimpan permintaan, dan tidak mengubah stok**.
- Pengamanan dasar: autentikasi Laravel, middleware role, CSRF POST, validasi form, dan delete sebagai POST.
- **Batas verifikasi:** Semua berkas PHP diperiksa sintaksnya secara statis, tetapi aplikasi **belum diuji di Laragon/MySQL secara nyata**, karena Laravel Composer `vendor/` tidak tersedia di lingkungan penyusunan. Jalankan uji fungsi di komputer kalian, jangan langsung dipasang pada situs publik.

## Cara instalasi di Windows / Laragon

1. Backup `C:\laragon\www\jraudiolocal` dan export database `jr_audio` dari HeidiSQL.
2. Extract ZIP. Pastikan struktur akhir **`C:\laragon\www\jraudio-laravel\artisan`** dan **`C:\laragon\www\jraudio-laravel\composer.json`**. Jangan membuat struktur folder ganda.
3. Buka **Laragon → Terminal**, lalu jalankan:

   ```powershell
   cd C:\laragon\www\jraudio-laravel
   php -v
   composer -V
   composer install
   copy .env.example .env
   php artisan key:generate
   ```

   Koneksi internet diperlukan untuk `composer install` karena ZIP **tidak menyertakan folder `vendor`**. Pastikan ekstensi PHP untuk Laravel aktif (mis. `mbstring`, `openssl`, `pdo_mysql`, `xml`, `ctype`, `fileinfo`).
4. **HeidiSQL:** Buka koneksi Laragon; buat database baru bernama **`jr_audio_dev`** dengan charset `utf8mb4`; klik database `jr_audio_dev` lalu import/jalankan file `database\jr_audio_dev_import.sql` ke database baru. **Jangan import ke `jr_audio` lama.** File SQL adalah dump dari ZIP lama, bukan otomatis database Laragon terkini. Kalau ingin menyalin data terkini, ekspor `jr_audio` terkini lalu import ke `jr_audio_dev` yang kosong (jangan import dua dump sekaligus).
5. Buka file `.env`, cocokkan DB_HOST, DB_PORT, DB_USERNAME, DB_PASSWORD dengan koneksi HeidiSQL. Awalnya disetel `DB_DATABASE=jr_audio_dev`. Jika kredensial berbeda, ganti sendiri tanpa membagikan password.
6. Kembali ke Terminal:

   ```powershell
   php artisan optimize:clear
   php artisan serve
   ```

7. Buka **http://127.0.0.1:8000**; rekomendasi di **http://127.0.0.1:8000/rekomendasi**; panel admin di **http://127.0.0.1:8000/login**. Login memakai user di tabel `users` dalam dump lama dan password yang kalian miliki; ZIP tidak memuat plaintext password.
8. Bila ingin menggunakan domain Laragon `.test`, virtual host harus diarahkan ke folder `public` Laravel (jangan ke root project). Untuk pengujian awal, `php artisan serve` paling aman.

**Jangan jalankan `php artisan migrate:fresh` atau `db:wipe` pada database lama.** Paket ini mengandalkan struktur database 11 tabel dari file SQL sebelumnya dan belum membuat migration untuk tabel lama. Tidak ada penambahan tabel rekomendasi karena pada tahap ini hasil hanya ditampilkan.

## Letak perubahan

- `routes/web.php` — routing Laravel termasuk Smart Recommendation.
- `app/Http/Controllers/RekomendasiController.php` — validasi form rekomendasi.
- `app/Services/RecommendationService.php` — mesin aturan rekomendasi yang bisa disesuaikan bersama teknisi JR Audio.
- `resources/views/rekomendasi/{form,hasil}.blade.php` — form dan hasil rekomendasi.
- `app/Models/*.php` — Eloquent mapping 11 tabel lama dengan primary key kustom (`id_barang`, dll).
- `app/Repositories/Legacy` — migrasi logika SQL operasional lama untuk menjaga perilaku transaksi, stok, denda.
- `resources/views/public/header.blade.php` — menu Smart Recommendation.
- `resources/views/public/index.blade.php` — tombol "Cari Kebutuhan Acara".
- `public/assets` — CSS, gambar, video lama, ditambah CSS rekomendasi.

## Daftar pengujian yang WAJIB dijalankan sebelum penggunaan nyata

1. Beranda, galeri, paket, katalog, kontak, logo/foto/video dapat dibuka.
2. Login Owner dan Karyawan; akses Owner terlarang bagi Karyawan; logout aman.
3. CRUD barang & upload foto; hapus barang yang pernah dipakai transaksi harus ditolak.
4. CRUD pelanggan dan karyawan; edit password karyawan dan role.
5. Transaksi barang satuan dan paket; uji stok cukup/tidak cukup, tanggal, dan ketersediaan saat transaksi ganda.
6. Pembayaran DP/pelunasan/konfirmasi, invoice & laporan.
7. Pengembalian baik/rusak, maintenance, dan denda keterlambatan.
8. Rekomendasi 30 / 100 / 500 peserta, indoor/outdoor, musik live/pidato, tanpa stok, anggaran kecil; pastikan **tidak ada transaksi atau stok berubah** setelah submit.
9. Audit perilaku pembatalan transaksi dan status `Batal`: alur kode lama tidak sepenuhnya mengembalikan stok sehingga perlu peninjauan bisnis sebelum go-live.

## Batas rekomendasi yang perlu diketahui

- Hasil hanya heuristik dari katalog: bukan analisis akustik, pembuktian perangkat cocok, atau jaminan ketersediaan pada tanggal tertentu.
- Saat ini database belum menyimpan daya/performance speaker, luas venue, SPL, tipe pasif/aktif, dan kebutuhan daya. Reviewer JR Audio sebaiknya menentukan spesifikasi dan aturan tambahan.
- Estimasi memakai **harga sewa satuan**. Tidak mengklaim harga paket aktual, ongkir, teknisi, listrik, pajak, atau instalasi sudah masuk.
- `Paket 2500`/`Paket 5000` hanya disarankan untuk **dikonsultasikan**, tidak dihitung sebagai total paket.
- Data `tanggal_acara` digunakan untuk konteks pelanggan saja, **bukan mengecek jadwal bentrok**.

## Bukan satu klik replace

Laravel memerlukan dependensi `vendor` (Composer) dan konfigurasi `.env`. Karena itu, menimpa folder PHP native lama dengan ZIP ini **tidak akan bekerja tanpa instalasi**. Pakai folder baru, uji dahulu, lalu pindahkan ke hosting setelah diverifikasi.
