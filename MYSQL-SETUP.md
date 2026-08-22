# MySQL Setup Guide - KTA FSPMI System

## Windows Setup

### Option 1: XAMPP (Recommended for Development)

1. Download XAMPP from https://www.apachefriends.org/

2. Install XAMPP with MySQL component selected

3. Start XAMPP Control Panel

4. Start MySQL service

5. Access phpMyAdmin: http://localhost/phpmyadmin

### Option 2: MySQL Installer

1. Download MySQL Installer from https://dev.mysql.com/downloads/installer/

2. Run the installer

3. Choose "Full" installation type

4. During setup:
   - Set root password (remember this!)
   - Port: 3306 (default)

### Option 3: Laragon (Recommended)

1. Download Laragon from https://laragon.org/

2. Install Laragon

3. Start Laragon

4. MySQL will start automatically

## Creating Database

### Via MySQL Command Line

```bash
mysql -u root -p
```

Enter your password, then:

```sql
CREATE DATABASE kta_fspmi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Via phpMyAdmin

1. Open http://localhost/phpmyadmin

2. Click "Databases" tab

3. Enter `kta_fspmi` as database name

4. Select `utf8mb4_unicode_ci` as collation

5. Click "Create"

## Configuring Environment

Edit `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kta_fspmi
DB_USERNAME=root
DB_PASSWORD=your_password_here
```

If using XAMPP with no password:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kta_fspmi
DB_USERNAME=root
DB_PASSWORD=
```

## Running Migrations

After database is created:

```bash
php artisan migrate
```

With seed data:

```bash
php artisan migrate:fresh --seed
```

## Troubleshooting

### Error: Connection Refused

- Check MySQL service is running
- Verify port 3306 is correct
- Check firewall settings

### Error: Access Denied

- Verify username and password
- Check MySQL user permissions

### Error: Unknown Database

- Create the database first
- Verify database name matches .env

## Verifying Installation

```bash
php artisan tinker --execute="
try {
    DB::connection()->getPdo();
    echo 'MySQL connection successful!';
    echo PHP_EOL;
    echo 'Database: ' . DB::connection()->getDatabaseName();
} catch (Exception \$e) {
    echo 'Connection failed: ' . \$e->getMessage();
}
"
```
