FROM composer:2.10.2 AS composer

FROM php:8.5.8-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev unzip $PHPIZE_DEPS \
    && docker-php-ext-install pdo_pgsql \
    && pecl install pcov-1.0.12 \
    && docker-php-ext-enable pcov \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN composer install --no-interaction --prefer-dist --no-scripts

COPY . .

RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs \
    && composer dump-autoload --optimize \
    && chmod -R ug+rwX storage bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
