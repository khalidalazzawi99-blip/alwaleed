FROM php:8.3-cli

WORKDIR /app

COPY . .

RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip \
    git \
    libpq-dev \
    libsqlite3-dev \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd zip pdo_pgsql pdo_sqlite opcache \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install --no-dev --optimize-autoloader

RUN php artisan storage:link || true

RUN php artisan config:clear || true

EXPOSE 10000

CMD php artisan migrate --force \
    && php artisan db:seed --class=AdminUserSeeder --force \
    && exec php artisan serve --host=0.0.0.0 --port=10000
