@echo off
title Kopi Kita
cd /d "%~dp0"

if not exist vendor (
  echo [1/3] Mengunduh library Laravel ^(composer install^)... pertama kali butuh beberapa menit
  call composer install
  if errorlevel 1 goto gagal
)

echo [2/3] Membuat tabel dan data awal ^(migrate --seed^)...
php artisan migrate --seed
if errorlevel 1 goto gagal

echo [3/3] Menjalankan server di http://127.0.0.1:8000  ^(tekan Ctrl+C untuk berhenti^)
php artisan serve
goto selesai

:gagal
echo.
echo Ada langkah yang gagal. Baca pesan error di atas, lalu cek README.md bagian "Jika ada masalah".
pause

:selesai
