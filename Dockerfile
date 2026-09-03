FROM composer:2.10.2 AS composer

FROM php:8.5.8-cli

LABEL org.opencontainers.image.title="Tech-Challenge-Oficina" \
    org.opencontainers.image.description="API da oficina mecanica da Fase 2" \
    org.opencontainers.image.source="https://github.com/saranbruno/Tech-Challenge-Oficina"

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
    && groupadd --gid 1000 app \
    && useradd --uid 1000 --gid 1000 --create-home --shell /usr/sbin/nologin app \
    && chown -R app:app storage bootstrap/cache

EXPOSE 8000

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 CMD php -r '$response = @file_get_contents("http://127.0.0.1:8000/up"); exit($response === false ? 1 : 0);'

USER app

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
