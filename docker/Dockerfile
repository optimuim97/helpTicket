# ============================================================================
# Stage 1 — build des assets front (Vite/Vue/Tailwind)
# ============================================================================
FROM node:20-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci --no-audit --no-fund

COPY resources/ ./resources/
COPY vite.config.js tailwind.config.js postcss.config.js jsconfig.json ./
COPY public/ ./public/

RUN npm run build


# ============================================================================
# Stage 2 — install des dépendances PHP (composer, sans dev)
# ============================================================================
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
# Copier ce dont composer a besoin pour les autoload-discover
COPY artisan ./
COPY app/ ./app/
COPY bootstrap/ ./bootstrap/
COPY config/ ./config/
COPY database/ ./database/
COPY routes/ ./routes/

RUN composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --prefer-dist \
        --optimize-autoloader


# ============================================================================
# Stage 3 — image runtime PHP-FPM
# ============================================================================
FROM php:8.2-fpm-alpine AS runtime

# Dépendances système + extensions PHP
RUN apk add --no-cache \
        bash \
        nginx \
        supervisor \
        icu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        oniguruma-dev \
        mysql-client \
        $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        intl \
        bcmath \
        gd \
        zip \
        opcache \
        exif \
        pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis opcache \
    && apk del $PHPIZE_DEPS \
    && rm -rf /tmp/* /var/cache/apk/*

# Config PHP / OPcache / Nginx / Supervisor
COPY docker/php.ini       /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/opcache.ini   /usr/local/etc/php/conf.d/zz-opcache.ini
COPY docker/nginx.conf    /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html

# Code applicatif
COPY . .

# Vendor PHP (sans dev) + assets compilés
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

# Permissions Laravel
RUN mkdir -p storage/framework/{cache,sessions,views,testing} \
              storage/logs \
              bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
