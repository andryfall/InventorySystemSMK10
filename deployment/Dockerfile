# Gunakan image PHP dengan FPM
FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    curl \
    git \
    && docker-php-ext-install pdo pdo_pgsql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy project Laravel ke dalam container
COPY . .

# Pastikan direktori penting dibuat dulu sebelum chown
RUN mkdir -p /var/www/storage /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Jalankan PHP-FPM
CMD ["php-fpm"]
