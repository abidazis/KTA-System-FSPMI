# Sistem Manajemen KTA Federasi

Sistem internal organisasi federasi karyawan untuk manajemen data anggota, pembuatan KTA, dan pencetakan.

## Requirements

- PHP 8.3+
- Composer
- Node.js 18+
- SQLite atau MySQL/PostgreSQL

## Installation

```bash
# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Install frontend dependencies
npm install

# Build frontend
npm run build
```

## Default Credentials

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@fspmi.org | password |
| Operator | operator@fspmi.org | password |

## Features

### Data Anggota
- Input manual anggota
- Edit & hapus anggota
- Filter berdasarkan wilayah (Provinsi, Kabupaten/Kota, Kecamatan)
- Bulk actions

### Import Data
- Download template Excel
- Import data + foto dalam format ZIP
- Validasi sebelum import
- Preview error sebelum commit

Format ZIP:
```
import-kta.zip
├── data.xlsx
└── foto/
    ├── NIK.jpg
    ├── NIK.jpg
    └── ...
```

### Data Pengurus
- Kelola periode kepengurusan
- Tambah/edit/hapus pengurus
- Upload tanda tangan
- Period management untuk histori

### KTA Generation
- Preview KTA
- Download PDF KTA
- Data pengurus otomatis berdasarkan periode aktif

### Batch Printing
- Pilih anggota untuk dicetak
- 5 KTA per halaman A4
- Front & back support
- Download PDF batch
- Riwayat cetak

### Dashboard
- Statistik anggota
- Rekap per kecamatan
- Rekap per jenis kelamin
- Rekap per status

### User Management
- Role-based access control
- Super Admin: semua akses
- Admin: anggota & print
- Operator: input & print

## Roles & Permissions

| Permission | Super Admin | Admin | Operator |
|------------|-------------|-------|----------|
| anggota-view | Yes | Yes | Yes |
| anggota-create | Yes | Yes | Yes |
| anggota-edit | Yes | Yes | No |
| anggota-delete | Yes | Yes | No |
| import | Yes | Yes | Yes |
| cetak | Yes | Yes | Yes |
| pengurus | Yes | No | No |
| wilayah | Yes | No | No |
| user-view | Yes | No | No |
| user-create | Yes | No | No |
| user-edit | Yes | No | No |
| user-delete | Yes | No | No |

## Storage

File storage menggunakan Laravel Filesystem:
- Foto anggota: `storage/app/members/photos/`
- Tanda tangan: `storage/app/members/signatures/`
- Import temp: `storage/app/imports/`

## API Routes

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/regencies?province_id=X | Get regencies by province |
| GET | /api/districts?regency_id=X | Get districts by regency |

## Print Configuration

KTA dimensions: 540px x 340px (aspect ratio ~1.586:1)
A4 landscape with 5 KTA per page

## Development

```bash
# Start development server
php artisan serve

# Run tests
php artisan test

# Clear cache
php artisan cache:clear
php artisan config:clear
```

## Deployment Checklist

1. Set `APP_ENV=production`
2. Set `APP_DEBUG=false`
3. Configure database
4. Set up storage permissions
5. Configure queue worker (if needed)
6. Set up cron for scheduled tasks
