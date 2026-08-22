# KTA FSPMI System - Offline Deployment Guide

## Overview

KTA FSPMI System adalah aplikasi manajemen Kartu Tanda Anggota yang dirancang untuk berjalan sepenuhnya offline dalam jaringan lokal (LAN) tanpa ketergantungan pada koneksi internet.

## System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    CLIENT COMPUTER                          │
│  ┌─────────────────────────────────────────────────────┐   │
│  │                Web Browser (Chrome/Firefox)          │   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                          │
                          ▼ LAN
┌─────────────────────────────────────────────────────────────┐
│                      SERVER COMPUTER                        │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ Apache / Nginx / PHP Built-in Server                 │   │
│  └─────────────────────────────────────────────────────┘   │
│  ┌─────────────────────────────────────────────────────┐   │
│  │           Laravel 13 Application                     │   │
│  │  ├── DOMPDF (PDF Generator)                         │   │
│  │  ├── Maatwebsite Excel (Import/Export)             │   │
│  │  └── Spatie Permission (RBAC)                      │   │
│  └─────────────────────────────────────────────────────┘   │
│  ┌─────────────────────────────────────────────────────┐   │
│  │                MySQL 8.x Database                    │   │
│  └─────────────────────────────────────────────────────┘   │
│  ┌─────────────────────────────────────────────────────┐   │
│  │            Local Storage                            │   │
│  │  ├── Member Photos                                  │   │
│  │  ├── Signatures                                    │   │
│  │  ├── Generated PDFs                                │   │
│  │  └── Import Files                                  │   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

## Requirements

### Hardware Requirements
- **Processor:** Intel Core i3 / AMD equivalent atau lebih tinggi
- **RAM:** Minimum 4 GB (disarankan 8 GB)
- **Storage:** Minimum 20 GB свободного места
- **Network:** Ethernet 100Mbps atau lebih untuk LAN

### Software Requirements

#### Server
- **OS:** Windows Server 2016+ / Windows 10/11 Pro
- **PHP:** 8.3.x
- **Web Server:** Apache 2.4+ / Nginx 1.20+ / PHP Built-in Server
- **Database:** MySQL 8.0+ или MariaDB 10.5+
- **Composer:** 2.x (untuk instalasi awal saja)

#### Client
- **Browser:** Chrome 90+ / Firefox 90+ / Edge 90+
- **Tidak diperlukan koneksi internet**

## PHP Extensions Required

Pastikan semua extension berikut terinstall:

```bash
php -m | grep -E "ctype|curl|dom|fileinfo|filter|hash|mbstring|openssl|pdo|session|tokenizer|xml|zip|gd"
```

### Mandatory Extensions
| Extension | Purpose |
|-----------|---------|
| pdo | Database connectivity |
| pdo_mysql | MySQL driver |
| mbstring | String operations |
| xml | XML parsing |
| gd | Image processing |
| zip | ZIP file handling |
| fileinfo | File type detection |
| openssl | Encryption |
| tokenizer | Laravel framework |
| ctype | Character type checking |
| filter | Input filtering |
| hash | Password hashing |
| session | Session management |

### Optional Extensions
| Extension | Purpose |
|-----------|---------|
| imagick | Enhanced image processing |
| redis | Cache (optional) |

## Installation Steps

### Step 1: Install PHP

1. Download PHP dari https://windows.php.net/download/
2. Pilih versi **PHP 8.3.x Non-Thread Safe (NTS)**
3. Extract ke `C:\PHP`
4. Tambahkan ke PATH sistem
5. Copy `php.ini-development` ke `php.ini`

### Step 2: Enable Required Extensions

Edit `php.ini` dan uncomment:
```ini
extension=curl
extension=gd
extension=mbstring
extension=mysqli
extension=openssl
extension=pdo
extension=pdo_mysql
extension=fileinfo
extension=zip
extension=xml
extension=tokenizer
```

### Step 3: Install MySQL

Gunakan salah satu opsi:

**Option A: XAMPP (Recommended)**
1. Download dari https://www.apachefriends.org/
2. Install dengan MySQL dan PHP yang sudah termasuk
3. Start Apache dan MySQL dari XAMPP Control Panel

**Option B: MySQL Installer**
1. Download dari https://dev.mysql.com/downloads/installer/
2. Install dengan konfigurasi typical
3. Set root password

**Option C: Laragon (Recommended)**
1. Download dari https://laragon.org/
2. Install dan pilih PHP 8.3 dan MySQL
3. Start semua services

### Step 4: Create Database

```sql
-- Via MySQL Command Line
mysql -u root -p
CREATE DATABASE kta_fspmi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

Atau via phpMyAdmin:
1. Buka http://localhost/phpmyadmin
2. Klik "Databases"
3. Buat database baru dengan nama `kta_fspmi`

### Step 5: Deploy Application

1. Copy seluruh folder project ke server
2. Buat file `.env`:
```env
APP_NAME=KTA-FSPMI
APP_ENV=local
APP_KEY=
APP_DEBUG=false
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kta_fspmi
DB_USERNAME=root
DB_PASSWORD=your_password

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync

FILESYSTEM_DISK=local
```

3. Generate application key:
```bash
php artisan key:generate
```

4. Run migrations:
```bash
php artisan migrate
```

5. Seed database:
```bash
php artisan db:seed
```

6. Create storage link:
```bash
php artisan storage:link
```

### Step 6: Configure Web Server

**Apache (httpd.conf):**
```apache
<VirtualHost *:80>
    DocumentRoot "C:\path\to\kta-fspmi\public"
    <Directory "C:\path\to\kta-fspmi\public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Nginx (nginx.conf):**
```nginx
server {
    listen 80;
    root C:/path/to/kta-fspmi/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

**PHP Built-in Server (Development):**
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

## Offline Asset Preparation

### Font untuk DOMPDF

DOMPDF membutuhkan font lokal untuk generate PDF. Font Arial sudah include default. Untuk font lain:

1. Download font .ttf
2. Convert ke format DOMPDF:
```bash
php vendor/dompdf/dompdf/load_font.php fontname /path/to/font.ttf
```

### Logo Organization

Letakkan logo di:
```
public/images/logo-kta.png
```

Format yang didukung: PNG, JPG
Ukuran rekomendasi: 200x200 pixels

### Static Assets

Seluruh static assets sudah di-bundle dalam `public/build/`:
- CSS: `public/build/assets/*.css`
- JS: `public/build/assets/*.js`
- Images: `public/images/`

## Default Login Credentials

Setelah seeding, gunakan credentials berikut:

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@fspmi.org | password |
| Operator | operator@fspmi.org | password |

**WAJIB: Ganti password setelah pertama login!**

## LAN Network Access

### Konfigurasi Server

1. Pastikan firewall mengizinkan koneksi pada port 80/8000
2. Disable antivirus scanning untuk folder application

### Konfigurasi Client

1. Buka browser
2. Akses: `http://[SERVER_IP]:8000`
   Contoh: `http://192.168.1.100:8000`

### Troubleshooting Network Access

1. **Connection Refused:**
   - Pastikan web server running
   - Cek firewall rules

2. **Timeout:**
   - Cek network cable
   - Verify IP address

3. **CORS Error:**
   - Pastikan APP_URL sesuai dengan akses network

## Data Backup

### Manual Backup
```bash
mysqldump -u root -p kta_fspmi > backup_$(date +%Y%m%d).sql
```

### Restore Backup
```bash
mysql -u root -p kta_fspmi < backup_20260820.sql
```

### Automated Backup (Windows Task Scheduler)
```batch
@echo off
set BACKUP_DIR=C:\KTABackups
set DB_NAME=kta_fspmi
set DATE=%DATE:~-4%%DATE:~3,2%%DATE:~0,2%
mysqldump -u root -p[password] %DB_NAME% > "%BACKUP_DIR%\backup_%DATE%.sql"
```

## Troubleshooting

### Error: Database Connection Failed
1. Pastikan MySQL service running
2. Verifikasi credentials di `.env`
3. Test connection: `php artisan tinker --execute="DB::connection()->getPdo();"`

### Error: Permission Denied (Storage)
```bash
icacls storage /inheritance:r /grant:r "IIS_IUSRS:(OI)(CI)F"
icacls storage/app /inheritance:r /grant:r "IIS_IUSRS:(OI)(CI)F"
```

### Error: Class 'DOMPDF' not found
```bash
composer install --no-dev
composer dump-autoload
```

### Error: Memory Exhausted
Tambah memory limit di `php.ini`:
```ini
memory_limit = 512M
```

## Security Considerations

### Production Deployment
1. Set `APP_DEBUG=false`
2. Set `APP_ENV=production`
3. Use strong session encryption
4. Regular database backups
5. Strong passwords untuk semua users

### Network Security
1. Gunakan firewall untuk membatasi akses
2. Isolasi server dari internet jika memungkinkan
3. Monitor access logs secara berkala

## System Maintenance

### Daily
- Backup database
- Monitor error logs

### Weekly
- Clear old logs
- Check disk space
- Review user activity

### Monthly
- Full system backup
- Update password users
- Review and cleanup old data

## File Structure (Offline Package)

```
KTA-FSPMI/
├── app/
│   ├── Console/
│   ├── Exceptions/
│   ├── Http/
│   ├── Models/
│   └── Services/
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── public/
│   ├── build/           # Pre-built assets
│   ├── images/          # Logo, static images
│   └── index.php
├── resources/
│   └── views/
├── routes/
├── storage/
│   ├── app/             # Member photos, signatures
│   └── framework/
├── vendor/               # Composer dependencies
├── .env
├── .env.example
├── artisan
├── composer.json
├── composer.lock
└── OFFLINE-INSTALL.md
```

## Contact & Support

Untuk bantuan teknis:
- Email: admin@fspmi.org
- Documentation: Lihat README.md

---

**Versi:** 1.0.0
**Terakhir Diperbarui:** 2026-08-20
