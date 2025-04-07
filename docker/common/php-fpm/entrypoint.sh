#!/bin/bash
set -e # Exit immediately if a command exits with a non-zero status.

# Ensure storage and bootstrap/cache directories are writable
# The USER variable should ideally be passed as a build arg or defined
# USER=${APP_USER:-www-data} # Example if you pass APP_USER env var
USER=www-data # Assuming default www-data user/group from Dockerfile

# Check if /var/www/storage exists before attempting to chown/chmod
if [ -d "/var/www/storage" ]; then
    echo "Setting permissions for /var/www/storage..."
    # Change ownership to the user/group FPM runs as. Adjust if you use a different user.
    chown -R ${USER}:${USER} /var/www/storage /var/www/bootstrap/cache
    # Set correct permissions (Laravel recommendations)
    chmod -R 775 /var/www/storage
    chmod -R 775 /var/www/bootstrap/cache
else
    echo "WARNING: /var/www/storage directory not found. Skipping permissions."
fi

echo "Running Laravel optimizations..."
# Go to the application directory
cd /var/www

# Clear previous caches (optional but often good practice before caching)
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Generate optimized production caches
# Note: 'optimize' is generally deprecated in favor of specific commands
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optional: Run database migrations
# Consider adding a flag/env variable to control this (e.g., RUN_MIGRATIONS=true)
# echo "Running database migrations..."
# php artisan migrate --force --no-interaction

echo "Laravel initialization complete. Starting PHP-FPM..."

# Execute the command passed into the container (e.g., "php-fpm")
exec "$@"
