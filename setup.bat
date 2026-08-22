@echo off
REM KTA FSPMI System - Quick Setup Script
REM Jalankan script ini setelah MySQL terinstall dan database dibuat

echo ========================================
echo KTA FSPMI System - Setup Script
echo ========================================
echo.

REM Check if PHP is available
php --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] PHP tidak ditemukan!
    echo Pastikan PHP sudah terinstall dan ada di PATH sistem.
    echo Download dari: https://windows.php.net/download/
    pause
    exit /b 1
)

echo [OK] PHP ditemukan
php --version
echo.

REM Check if MySQL is available
mysql --version >nul 2>&1
if errorlevel 1 (
    echo [WARNING] MySQL CLI tidak ditemukan di PATH
    echo Pastikan MySQL sudah terinstall.
    echo Anda bisa menggunakan phpMyAdmin untuk membuat database.
) else (
    echo [OK] MySQL ditemukan
)
echo.

REM Generate application key
echo [1/4] Generating application key...
php artisan key:generate --force
echo.

REM Clear cache
echo [2/4] Clearing cache...
php artisan optimize:clear
echo.

REM Run migrations
echo [3/4] Running database migrations...
php artisan migrate --force
echo.

REM Seed database
echo [4/4] Seeding database...
php artisan db:seed --force
echo.

REM Create storage link
echo.
echo [DONE] Creating storage link...
php artisan storage:link
echo.

echo ========================================
echo Setup Complete!
echo ========================================
echo.
echo Database: kta_fspmi
echo Default Login:
echo   Admin: admin@fspmi.org / password
echo   Operator: operator@fspmi.org / password
echo.
echo Untuk menjalankan server:
echo   php artisan serve --host=0.0.0.0 --port=8000
echo.
echo IMPORTANT: Ganti password setelah login!
echo ========================================
echo.

pause
