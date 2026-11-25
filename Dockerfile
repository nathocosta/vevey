FROM php:8.4-cli

# System dependencies + PHP extensions for Laravel 13, Filament 5 & PostgreSQL
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libicu-dev libzip-dev libpng-dev libjpeg-dev libwebp-dev curl \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install pdo_pgsql pgsql intl zip gd exif bcmath \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && npm ci --ignore-scripts \
    && npm run build \
    && chmod +x docker/start-render.sh

EXPOSE 10000

CMD ["sh", "docker/start-render.sh"]
