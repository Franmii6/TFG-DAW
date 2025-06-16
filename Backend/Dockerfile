FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

RUN apk update && apk add \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip

RUN docker-php-ext-install pdo pdo_mysql \
    && apk --no-cache add nodejs npm


COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer


USER root

RUN chmod 777 -R /var/www/html


#RUN composer require laravel/sanctum
#RUN php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
#RUN php artisan migrate
