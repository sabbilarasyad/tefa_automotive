FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM php:8.4-apache
RUN apt-get update && apt-get install -y --no-install-recommends git unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql gd exif bcmath zip \
    && a2enmod rewrite \
    && echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts
COPY . .
COPY --from=assets /app/public/build public/build
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && composer dump-autoload --optimize \
    && (php artisan package:discover --ansi || true) \
    && chown -R www-data:www-data storage bootstrap/cache
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/ports.conf /etc/apache2/ports.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
EXPOSE 10000
CMD ["entrypoint.sh"]