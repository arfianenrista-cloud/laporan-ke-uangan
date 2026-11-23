#!/bin/sh
set -e

# Ensure SQLite file exists if using sqlite
if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DB_CONNECTION" ]; then
    mkdir -p /var/www/html/database
    touch /var/www/html/database/database.sqlite
    chmod 777 /var/www/html/database/database.sqlite
    chmod 777 /var/www/html/database
fi

# Ensure storage directories exist with write permissions
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

# Generate key if not set
if [ -z "$APP_KEY" ]; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Seed default demo user if table is empty
echo "Checking database seed..."
php artisan db:seed --force || true

# Cache routes and views for production performance
echo "Optimizing application..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

PORT=${PORT:-8000}
echo "Starting KeuanganKu application on port $PORT..."
exec php artisan serve --host=0.0.0.0 --port="$PORT"
