FROM php:8.3-cli

WORKDIR /app

COPY . .

RUN apt-get update && apt-get install -y unzip git libsqlite3-dev

RUN docker-php-ext-install pdo pdo_sqlite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install --no-dev --optimize-autoloader

RUN touch database/database.sqlite

RUN php artisan config:clear || true

EXPOSE 10000

CMD php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=10000