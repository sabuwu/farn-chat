FROM php:8.4-fpm-alpine

RUN apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
        libzip-dev \
        oniguruma-dev \
        zlib-dev \
    && apk add --no-cache \
        bash \
        curl \
        git \
        icu-libs \
        libzip \
        unzip \
    && docker-php-ext-install \
        intl \
        mbstring \
        mysqli \
        pdo \
        pdo_mysql \
        zip \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-progress --prefer-dist --no-scripts

COPY . .

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
