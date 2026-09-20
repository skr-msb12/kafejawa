@echo off
title KafeJawa POS - Setup Database
cd /d "%~dp0"
echo ========================================================
echo         SETUP / RESET DATABASE POS KAFEJAWA
echo ========================================================
echo.
echo Pastikan MySQL di XAMPP sudah di-START!
echo Sedang membuat tabel dan mengisi data awal (menu & akun)...
echo.
php artisan migrate:fresh --seed
echo.
echo ========================================================
echo Database BERHASIL disiapkan! 
echo Sekarang kamu bisa klik 2x file "JALANKAN_KAFEJAWA.bat".
echo ========================================================
pause
