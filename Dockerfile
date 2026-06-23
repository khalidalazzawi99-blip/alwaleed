FROM php:8.3-cli

WORKDIR /app

COPY . .

RUN apt-get update && apt-get install -y unzip git libsqlite3-dev

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install --no-dev --optimize-autoloader

RUN php artisan key:generate --force || true

EXPOSE 10000

CMD php artisan serve --host=0.0.0.0 --port=10000