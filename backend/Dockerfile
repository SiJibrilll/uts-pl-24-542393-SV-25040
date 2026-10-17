# =========================================================================
# BACKEND LARAVEL DENGAN FRANKENPHP SERVER (SOAL 5 & 7 BONUS +10)
# =========================================================================
FROM dunglas/frankenphp:1-php8.4-alpine AS base

# Pasang ekstensi PHP yang dibutuhkan untuk PostgreSQL, MySQL, dan Laravel
RUN install-php-extensions pdo_pgsql pgsql pdo_mysql bcmath intl zip

# Pasang Composer resmi dengan versi semver terkunci
COPY --from=composer:2.7.7 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Salin manifes dependensi terlebih dahulu demi optimasi layer caching (Soal 5a)
COPY composer.json composer.lock ./

# Pasang dependensi PHP mode produksi tanpa dev packages
RUN composer install --no-dev --no-scripts --prefer-dist --no-autoloader --no-interaction

# Salin seluruh source code backend aplikasi
COPY . .

# Generate autoloader teroptimasi
RUN composer dump-autoload --optimize --no-dev

# Siapkan entrypoint dan perizinan direktori
RUN chmod +x /app/docker-entrypoint.sh && \
    mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache && \
    chmod -R 777 storage bootstrap/cache

EXPOSE 8000

ENTRYPOINT ["/app/docker-entrypoint.sh"]
