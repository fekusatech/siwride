#!/bin/bash

# Ensure .env is available
if [ ! -f .env ]; then
    echo "Creating .env from .env.example"
    cp .env.example .env
fi

# Set proper permissions for storage and bootstrap/cache at runtime
# (This ensures volumes mounted from host are writable)
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Generate APP_KEY if it doesn't exist
if grep -q "APP_KEY=$" .env || ! grep -q "APP_KEY=" .env; then
    echo "Generating Application Key..."
    php artisan key:generate --no-interaction --force
fi

# Run migrations (only if DB is ready - handled by docker-compose depends_on healthcheck)
echo "Running database migrations..."
php artisan migrate --force --no-interaction

# Create storage symlink
echo "Linking storage..."
php artisan storage:link --no-interaction

# Optimize for production
echo "Caching configuration and routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Hand off to the original CMD (apache2-foreground)
echo "Starting Apache..."
exec apache2-foreground
