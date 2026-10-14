# ☕ Kopi Kita — Pemesanan Cafe (Laravel 12)

Project ini **sudah lengkap**. Kamu tidak perlu `composer create-project` dan tidak perlu menyalin file.
Yang perlu kamu lakukan hanya: ekstrak → `composer install` → buat database → jalankan.

| Akun | Bisa apa |
|---|---|
| **Pelanggan** | Lihat menu (foto, harga, stok), pilih jumlah, total bayar terhitung otomatis, kirim pesanan atas nama sendiri, pantau status |
| **Kasir** | Terima / proses / selesaikan / batalkan pesanan, CRUD produk + upload foto + stok, ringkasan pendapatan |

---

## Langkah-langkah

### 1. Siapkan alat (sekali saja)
- **Laragon** (https://laragon.org) — sudah membawa PHP, MySQL, dan phpMyAdmin. (XAMPP juga bisa.)
- **Composer** (https://getcomposer.org). Laragon Full biasanya sudah menyertakannya.
- PHP **8.2 atau lebih baru**. Cek di terminal: `php -v`

### 2. Ekstrak
Ekstrak `kopi-kita.zip` ke **`C:\laragon\www\`** sehingga terbentuk `C:\laragon\www\kopi-kita`
(di dalamnya ada `artisan`, `composer.json`, `.env`, folder `app`, dst.).

> Pastikan tidak ada folder ganda seperti `kopi-kita\kopi-kita`.

### 3. Buka terminal di folder project
Laragon → klik **Terminal**, lalu:
```powershell
cd C:\laragon\www\kopi-kita
composer install
```
Tunggu sampai selesai (beberapa menit, butuh internet). Ini mengunduh folder `vendor`.

### 4. Buat database `kopi_kita` ← satu-satunya langkah manual
1. Klik **Start All** di Laragon (MySQL harus menyala).
2. Buka phpMyAdmin (Laragon → **Database** atau http://localhost/phpmyadmin).
3. Klik **New / Baru** → nama database **`kopi_kita`** → collation **`utf8mb4_unicode_ci`** → **Create**.

*(atau tempel isi file `database/buat-database.sql` di tab SQL)*

File **`.env`** sudah terisi: user `root`, password kosong (bawaan Laragon/XAMPP).
Kalau MySQL kamu memakai password, isi di baris `DB_PASSWORD=`.

### 5. Buat tabel & data awal
```powershell
php artisan migrate --seed
```
Terbentuk 4 tabel aplikasi (`users`, `products`, `orders`, `order_items`) + 2 akun + 10 menu contoh.

### 6. Jalankan
```powershell
php artisan serve
```
Buka **http://127.0.0.1:8000**

| Peran | Email | Password |
|---|---|---|
| Kasir | `kasir@kopikita.test` | `kasir123` |
| Pelanggan | `pelanggan@kopikita.test` | `pelanggan123` |

Pelanggan lain bisa mendaftar sendiri lewat tombol **Daftar**.

**Cara coba:** login sebagai pelanggan di satu browser dan sebagai kasir di browser lain (atau jendela *incognito*).
Pesanan pelanggan muncul di halaman kasir (halaman kasir menyegarkan diri tiap 10 detik).

> **Jalan pintas Windows:** setelah database dibuat (langkah 4), klik dua kali **`jalankan.bat`** —
> ia menjalankan `composer install` (bila perlu), `migrate --seed`, lalu `serve` sekaligus.

Tidak perlu `php artisan key:generate` (APP_KEY sudah ada) dan tidak perlu `storage:link`
(foto menu disimpan di `public/uploads`).

---

## Aturan stok
Semua berjalan di dalam transaksi database, jadi aman walau dua pelanggan memesan bersamaan.
- Pesanan dikirim → stok berkurang. Jumlah melebihi stok ditolak.
- Pesanan **dibatalkan** / **dihapus** (saat masih aktif) → stok kembali.
- Pesanan batal **dibuka lagi** → stok dipotong lagi (dicek cukup atau tidak).
- Harga selalu dibaca ulang dari database, jadi tidak bisa dimanipulasi dari browser.

## Jika ada masalah

| Gejala | Solusi |
|---|---|
| `composer` / `php` tidak dikenali | Buka lewat **Terminal Laragon** (bukan PowerShell biasa), atau tambahkan PHP ke PATH |
| `composer install` error soal ekstensi (`ext-mbstring`, `ext-fileinfo`, dst.) | Laragon → Menu → PHP → Extensions → centang ekstensi tersebut, lalu ulangi |
| `Your PHP version ... does not satisfy` | Butuh PHP 8.2+. Laragon → Menu → PHP → pilih versi lebih baru |
| `could not find driver` | Aktifkan ekstensi `pdo_mysql` (Laragon → Menu → PHP → Extensions) |
| `Access denied for user 'root'` | `DB_PASSWORD` di `.env` belum sesuai password MySQL kamu |
| `Unknown database 'kopi_kita'` | Database belum dibuat (langkah 4) |
| `Connection refused` | MySQL belum menyala (**Start All** di Laragon) |
| `No application encryption key` | Jalankan `php artisan key:generate` |
| Halaman `419 Page Expired` | Muat ulang halaman login lalu coba lagi |
| Setelah mengubah `.env` tidak berpengaruh | `php artisan config:clear` |
| Ingin mengulang dari nol | `php artisan migrate:fresh --seed` (**menghapus semua data**) |

## Struktur penting
```
app/Http/Controllers/   AuthController, CustomerController, Cashier/{Order,Product,Summary}Controller
app/Http/Middleware/    EnsureRole (pembatas akses kasir / pelanggan)
app/Models/             User, Product, Order, OrderItem
database/migrations/    struktur tabel
database/seeders/       akun & menu contoh
resources/views/        semua halaman (Blade)
routes/web.php          daftar URL
public/css/cafe.css     tampilan
public/uploads/         foto menu yang diunggah kasir
```
