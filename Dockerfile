FROM php:8.3-cli
RUN apt-get update && apt-get install -y --no-install-recommends git unzip libssl-dev \
    && pecl install mongodb && docker-php-ext-enable mongodb \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY composer.json ./
RUN composer install --no-dev --no-interaction --optimize-autoloader
COPY . .
ENV PORT=8000
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT} -t public public/index.php"]
