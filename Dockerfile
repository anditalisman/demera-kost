# syntax=docker/dockerfile:1

##########################
# Stage: composer (vendor install)
##########################
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --no-progress --prefer-dist --optimize-autoloader --ignore-platform-reqs
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
COPY --from=vendor /app/vendor/tightenco/ziggy ./vendor/tightenco/ziggy
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
        libwebp-dev \
        freetype-dev \
        oniguruma-dev \
        curl-dev \
        libxml2-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
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

ARG UID=1000
ARG GID=1000

RUN apk add --no-cache bash git \
    && deluser www-data 2>/dev/null || true \
    && delgroup www-data 2>/dev/null || true \
    && addgroup -g ${GID} www-data \
    && adduser -D -u ${UID} -G www-data -s /bin/bash www-data

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

USER www-data

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
    && php artisan l5-swagger:generate || true \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

USER www-data

CMD ["php-fpm"]

##########################
# Stage: nginx (static assets baked in, used by docker-compose in production)
##########################
FROM nginx:1.27-alpine AS nginx-prod

COPY --from=production /var/www/html/public /var/www/html/public
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
