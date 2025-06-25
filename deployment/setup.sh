#!/bin/bash

# Set working directory to /var/www/html
cd /var/www/html || { echo "Directory /var/www/html not found."; exit 1; }

# Create necessary directories for storage
mkdir -p storage/framework/{sessions,views,cache}

# Set ownership and permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Run Laravel commands
php artisan migrate
php artisan cache:clear
php artisan optimize
php artisan storage:link

# Print completion message
echo "Deployment tasks completed successfully."