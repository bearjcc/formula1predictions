FROM php:8.4-cli-alpine

WORKDIR /var/www/html

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
        bash \
        git \
        curl \
        nodejs \
        npm \
        mysql-client \
    && docker-php-ext-install \
        bcmath \
        pdo_mysql

# Copy dependency manifests
COPY composer.json composer.lock package.json ./

# Install PHP and Node dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev \
    && npm install --no-progress --no-fund

# Copy application code
COPY . .

# Build frontend assets and cache configuration
RUN npm run build \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Environment defaults for production container
ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_URL=http://localhost:8000

# Expose the application port
EXPOSE 8000

# Run the Laravel application using the built-in server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

