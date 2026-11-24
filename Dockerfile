FROM php:8.3-cli-alpine

# Install system dependencies & PHP extensions required by Laravel
RUN apk add --no-cache \
    curl \
    git \
    nodejs \
    npm \
    sqlite-dev \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    bash \
    icu-dev \
    oniguruma-dev \
    linux-headers \
    && docker-php-ext-install pdo pdo_sqlite mbstring bcmath intl pcntl opcache

# Copy latest Composer from official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application source
COPY . .

# Set storage and cache permissions
RUN chmod -R 775 storage bootstrap/cache

# Install PHP dependencies without dev packages and build production assets
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && (npm ci --omit=dev || npm install --omit=dev) \
    && npm run build \
    && rm -rf node_modules

# Ensure SQLite file exists and is writable
RUN mkdir -p database && touch database/database.sqlite && chmod 777 database/database.sqlite && chmod 777 database

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENV PORT=8000
EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

