# Etapa de build para assets
FROM node:20-alpine AS node-builder

WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build

# Imagen principal PHP
FROM php:8.2-cli-alpine

# Instalar dependencias del sistema
RUN apk update && apk add --no-cache \
    curl \
    zip \
    unzip \
    git \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    linux-headers \
    $PHPIZE_DEPS

# Instalar extensiones PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    opcache

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY --chown=www-data:www-data . .

# Copiar assets compilados desde node-builder
COPY --from=node-builder --chown=www-data:www-data /app/public/build ./public/build

# Instalar dependencias PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Crear directorio para SQLite si es necesario
RUN mkdir -p /var/www/html/database && touch /var/www/html/database/database.sqlite \
    && chown -R www-data:www-data /var/www/html/database

EXPOSE 8000

# Ejecutar migraciones y luego iniciar el servidor
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000
