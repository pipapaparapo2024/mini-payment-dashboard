# Stage 1: build Vue frontend
FROM node:22-alpine AS frontend-build

WORKDIR /frontend
COPY frontend/package.json frontend/package-lock.json ./
RUN npm ci
COPY frontend/ ./
RUN npm run build

# Stage 2: Laravel runtime
FROM php:8.4-cli-alpine

RUN apk add --no-cache sqlite-dev zip unzip git \
    && docker-php-ext-install pdo pdo_sqlite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY backend/ ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Copy built SPA from frontend stage
COPY --from=frontend-build /backend/public/spa ./public/spa

RUN cp .env.example .env \
    && php artisan key:generate --force \
    && touch database/database.sqlite \
    && php artisan migrate --force \
    && php artisan db:seed --force

EXPOSE 8000

CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
