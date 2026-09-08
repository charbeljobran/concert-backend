FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libsqlite3-dev \
    && docker-php-ext-install pdo_mysql pdo_sqlite zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction

RUN chmod +x docker-entrypoint.sh

EXPOSE 8080

# Ensures the sqlite db exists, an app key is set, migrations are applied,
# and initial data is seeded (once) before the server starts.
CMD ["./docker-entrypoint.sh"]