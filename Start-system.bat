
@echo off
setlocal EnableExtensions
title KTA FSPMI - Local LAN Launcher

cd /d "%~dp0"
set "PROJECT=%CD%"
set "PORT=8000"
set "PHP="

echo.
echo ==========================================
echo       KTA FSPMI - SYSTEM LAUNCHER
echo ==========================================
echo.

if not exist "%PROJECT%\artisan" (
    echo ERROR: File artisan tidak ditemukan.
    echo Simpan BAT di folder utama proyek Laravel.
    pause
    exit /b 1
)

if not exist "%PROJECT%\.env" (
    echo ERROR: File .env tidak ditemukan.
    pause
    exit /b 1
)

if not exist "%PROJECT%\vendor\autoload.php" (
    echo ERROR: Dependensi Laravel belum terpasang.
    echo Jalankan composer install saat setup awal.
    pause
    exit /b 1
)


rem Deteksi PHP Laragon atau XAMPP
set "PHP="

rem Cari PHP XAMPP
for %%P in (
    "C:\xampp\php\php.exe"
    "D:\xampp\php\php.exe"
) do (
    if not defined PHP if exist "%%~P" set "PHP=%%~P"
)

rem Jika XAMPP tidak ditemukan, cari PHP Laragon
if not defined PHP (
    for /d %%D in ("C:\laragon\bin\php\php-*") do (
        if exist "%%~D\php.exe" set "PHP=%%~D\php.exe"
    )
)

if not defined PHP (
    echo ERROR: PHP Laragon atau XAMPP tidak ditemukan.
    pause
    exit /b 1
)

echo PHP ditemukan: %PHP%
"%PHP%" -v

echo Proyek : %PROJECT%
echo PHP    : %PHP%
echo.
echo Pilih mode penggunaan:
echo.
echo [1] Lokal - komputer ini saja
echo [2] LAN   - akses dari komputer lain
echo.
choice /c 12 /n /m "Pilihan Anda (1/2): "

if errorlevel 2 goto LAN
if errorlevel 1 goto LOCAL

:LOCAL
set "HOST=127.0.0.1"
set "URL=http://127.0.0.1:%PORT%"
goto CHECK

:LAN
set "HOST=0.0.0.0"
set "URL=http://127.0.0.1:%PORT%"
echo.
echo Alamat IPv4 komputer server:
powershell -NoProfile -Command "Get-NetIPAddress -AddressFamily IPv4 -ErrorAction SilentlyContinue | Where-Object {$_.IPAddress -ne '127.0.0.1' -and $_.IPAddress -notlike '169.254.*' -and $_.AddressState -eq 'Preferred'} | ForEach-Object {Write-Host ('http://' + $_.IPAddress + ':%PORT%')}"
echo.
echo Pastikan client memakai jaringan yang sama.
echo Jika diminta, izinkan akses melalui firewall
echo pada jaringan Private untuk port %PORT%.
echo.

:CHECK
echo Memeriksa MySQL pada 127.0.0.1:3306...
powershell -NoProfile -Command "$c=New-Object Net.Sockets.TcpClient; try {$c.Connect('127.0.0.1',3306); exit 0} catch {exit 1} finally {$c.Dispose()}" >nul 2>&1

if errorlevel 1 (
    echo.
    echo MySQL belum terdeteksi pada port 3306.
    echo Buka XAMPP Control Panel, lalu Start MySQL.
    pause
    exit /b 1
)

echo Memeriksa port aplikasi %PORT%...
powershell -NoProfile -Command "$c=New-Object Net.Sockets.TcpClient; try {$c.Connect('127.0.0.1',%PORT%); exit 0} catch {exit 1} finally {$c.Dispose()}" >nul 2>&1

if not errorlevel 1 (
    echo ERROR: Port %PORT% sudah digunakan.
    echo Hentikan server lain atau pilih port berbeda.
    pause
    exit /b 1
)

echo.
echo Menjalankan Laravel...
echo URL lokal: %URL%
echo Biarkan jendela ini tetap terbuka.
echo Tekan Ctrl+C jika ingin menghentikan server.
echo.


echo.
echo Menjalankan server Laravel...

start "KTA Laravel Server" cmd /k ""%PHP%" artisan serve --host=%HOST% --port=%PORT%"

echo Menunggu server siap...

for /l %%i in (1,1,30) do (
    powershell -NoProfile -Command "$c=New-Object Net.Sockets.TcpClient; try {$c.Connect('127.0.0.1',%PORT%); exit 0} catch {exit 1} finally {$c.Dispose()}" >nul 2>&1
    if not errorlevel 1 goto SERVER_READY
    timeout /t 1 /nobreak >nul
)

echo.
echo ERROR: Server Laravel belum siap.
echo Periksa jendela KTA Laravel Server untuk melihat error.
pause
exit /b 1

:SERVER_READY
echo Server berhasil berjalan!
start "" "%URL%"
echo.
echo Aplikasi siap digunakan.
echo Biarkan jendela KTA Laravel Server tetap terbuka.
pause


echo.
echo Server Laravel telah berhenti.
pause
