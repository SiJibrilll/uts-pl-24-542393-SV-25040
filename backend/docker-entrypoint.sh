#!/bin/sh
set -e

# Siapkan direktori storage & cache
mkdir -p /app/storage/framework/cache/data \
         /app/storage/framework/sessions \
         /app/storage/framework/views \
         /app/storage/logs \
         /app/bootstrap/cache

chmod -R 777 /app/storage /app/bootstrap/cache

# Tunggu database siap jika DB_HOST dikonfigurasi
if [ -n "$DB_HOST" ]; then
    echo "Menunggu koneksi basis data di $DB_HOST:${DB_PORT:-5432}..."
    while ! nc -z "$DB_HOST" "${DB_PORT:-5432}"; do
        sleep 1
    done
    echo "Koneksi basis data berhasil terhubung!"

    # Jalankan migrasi dan seeder
    echo "Menjalankan php artisan migrate --force..."
    php artisan migrate --force

    echo "Menjalankan php artisan db:seed --force..."
    php artisan db:seed --force
fi

echo "FrankenPHP Application Server siap melayani request di port 8000..."
exec frankenphp php-server --listen :8000 --root /app/public --access-log
