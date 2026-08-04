# syntax=docker/dockerfile:1

##########################
# Stage: composer (vendor install)
##########################
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --no-progress --prefer-dist --optimize-autoloader
COPY . .
RUN composer dump-autoload --optimize --no-dev

##########################
# Stage: frontend build
##########################
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm ci
COPY . .
RUN npm run build

##########################
# Stage: php base (shared by dev & prod)
##########################
FROM php:8.4-fpm-alpine AS php-base

RUN apk add --no-cache \
        icu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        oniguruma-dev \
        curl-dev \
        libxml2-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        intl \
        zip \
        gd \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-demera.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/98-opcache.ini

WORKDIR /var/www/html

##########################
# Stage: dev (source mounted as volume, used by docker-compose)
##########################
FROM php-base AS dev

RUN apk add --no-cache bash git
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

CMD ["php-fpm"]

##########################
# Stage: production
##########################
FROM php-base AS production

RUN apk add --no-cache supervisor
COPY --chown=www-data:www-data . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN php artisan storage:link --force || true \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

USER www-data

CMD ["php-fpm"]
