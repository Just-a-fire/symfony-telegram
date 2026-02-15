FROM php:8.3-fpm-alpine

# Установка системных зависимостей и расширений PHP
RUN apk add --no-cache libpq-dev \
    && docker-php-ext-install pdo_pgsql

# RUN pecl install xdebug --output --force xdebug \ 
#     && docker-php-ext-enable xdebug

# Установка зависимостей для сборки расширений PECL
RUN apk add --no-cache $PHPIZE_DEPS linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

# Рекомендуемые базовые настройки для Xdebug 3.x
RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
