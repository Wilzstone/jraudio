@echo off
setlocal
cd /d "%~dp0"
echo =========================================
echo  JR AUDIO LARAVEL - PERSIAPAN
 echo =========================================
where php >nul 2>nul
if errorlevel 1 (
  echo PHP belum ada di PATH. Jalankan script ini lewat Terminal Laragon.
  pause
  exit /b 1
)
where composer >nul 2>nul
if errorlevel 1 (
  echo Composer belum ada di PATH. Aktifkan melalui Laragon atau install Composer.
  pause
  exit /b 1
)
if not exist .env copy .env.example .env
composer install
if errorlevel 1 (
  echo Instalasi dependensi gagal. Cek internet dan versi PHP.
  pause
  exit /b 1
)
php artisan key:generate
if errorlevel 1 (
  echo APP_KEY gagal dibuat.
  pause
  exit /b 1
)
echo.
echo Selesai menyiapkan kode. Sekarang import database jr_audio_dev
 echo melalui HeidiSQL, sesuaikan file .env, lalu jalankan:
echo php artisan serve
echo.
pause
