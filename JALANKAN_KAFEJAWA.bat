@echo off
title KafeJawa POS - Server Lokal
cd /d "%~dp0"
echo ========================================================
echo          MENJALANKAN SISTEM POS KAFEJAWA
echo           SMK MUHAMMADIYAH 1 BANTUL
echo ========================================================
echo.
echo 1. Pastikan XAMPP (Apache dan MySQL) sudah berstatus START!
echo 2. Browser akan terbuka otomatis di http://127.0.0.1:8000
echo 3. JANGAN TUTUP jendela hitam ini selama menggunakan aplikasi.
echo.
echo Membuka web browser...
start http://127.0.0.1:8000
echo.
php artisan serve
pause
