FROM php:8.4-cli-alpine

RUN apk add --no-cache sqlite-dev zip unzip git \
    && docker-php-ext-install pdo pdo_sqlite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy full backend before composer install — post-install scripts need artisan
COPY backend/ ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

COPY --from=node:22-alpine /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-alpine /usr/local/bin/npm /usr/local/bin/npm

COPY frontend/package.json frontend/package-lock.json /frontend/
WORKDIR /frontend
RUN npm ci
COPY frontend/ ./
RUN npm run build

WORKDIR /var/www/html
RUN cp .env.example .env \
    && php artisan key:generate --force \
    && touch database/database.sqlite \
    && php artisan migrate --force \
    && php artisan db:seed --force

EXPOSE 8000

# Render sets PORT dynamically
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
