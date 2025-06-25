# Node.js build stage
FROM node:18 AS node_builder

WORKDIR /app

# Copy package files and install dependencies
COPY package*.json ./
RUN npm install

# Copy all frontend source files and build
COPY . .
RUN npm run build

# PHP Stage
FROM php:8.2-fpm

# Install dependencies - combined into a single layer with cleanup
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    libonig-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libwebp-dev \
    libicu-dev \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        mbstring \
        xml \
        ctype \
        bcmath \
        intl \
        opcache \
        gd \
        zip

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files and install dependencies
COPY composer.* ./
RUN composer install --prefer-dist --no-dev --optimize-autoloader --no-scripts     && composer clear-cache

# Create required directories early
RUN mkdir -p /var/run/php /var/log/supervisor

# Copy configuration files
COPY deployment/nginx.conf /etc/nginx/nginx.conf
COPY deployment/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY deployment/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy application code
COPY . .
COPY --from=node_builder /app/public/build public/build

# Set permissions as final step
RUN chown -R www-data:www-data     /var/www/html/storage     /var/www/html/bootstrap/cache

# Expose port
EXPOSE 80

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
