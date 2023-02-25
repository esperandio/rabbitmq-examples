FROM php:8.2.2-fpm

RUN apt-get update && apt-get install -y \
        libzip-dev \
        unzip \
    && docker-php-ext-install sockets \
    && docker-php-ext-install zip

COPY --from=composer /usr/bin/composer /usr/bin/composer