FROM php:8.4-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy Composer files first
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction

# Copy Laravel application
COPY . .

# Generate Laravel cache
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# Expose Laravel's port
EXPOSE 8080

# Start Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]

.git
.github
.env
.env.example
node_modules
vendor
storage/logs/*
storage/framework/cache/*
storage/framework/sessions/*
storage/framework/views/*
Dockerfile
docker-compose.yml