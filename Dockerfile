# Stage 1: Install PHP Dependencies
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
# We install all dependencies (including dev for wayfinder/boost if needed)
RUN composer install --no-interaction --prefer-dist --no-scripts
COPY . .
RUN composer dump-autoload --optimize

# Stage 2: Build Frontend Assets
FROM php:8.4-cli AS frontend
WORKDIR /app

# Install Node.js 20 and dependencies for Wayfinder
RUN apt-get update && apt-get install -y \
    curl \
    gnupg \
    libxml2-dev \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# Copy vendor from vendor stage so Wayfinder can run artisan commands
COPY --from=vendor /app/vendor /app/vendor
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 3: Final App Server
FROM php:8.4-apache
WORKDIR /var/www/html

# Install required system packages and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd zip

# Enable Apache mod_rewrite for Laravel routing
RUN a2enmod rewrite

# Setup Apache DocumentRoot to point to Laravel's public directory
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copy application from vendor stage
COPY --from=vendor /app /var/www/html

# Copy compiled frontend assets from frontend stage
COPY --from=frontend /app/public/build /var/www/html/public/build

# Copy entrypoint script and make it executable
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set permissions for Laravel to be able to write to storage and bootstrap/cache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Use our specialized entrypoint
ENTRYPOINT ["entrypoint.sh"]

# Expose port 80
EXPOSE 80
