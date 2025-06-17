# Etapa 1: sólo dependencias (vendor)
FROM composer:2 AS vendor

WORKDIR /var/www/html

# Copiamos sólo los archivos de Composer y ejecutamos la instalación
COPY src/composer.json src/composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Etapa 2: imagen de aplicación final
FROM php:8.2-fpm-alpine AS app

WORKDIR /var/www/html

# Instalamos librerías del sistema + extensiones PHP
RUN apk update && apk add --no-cache \
      curl \
      libpng-dev \
      libxml2-dev \
      zip \
      unzip \
    && docker-php-ext-install pdo pdo_mysql

# Copiamos el vendor ya instalado desde la etapa anterior
COPY --from=vendor /var/www/html/vendor ./vendor

# Copiamos el resto del código
COPY src/ ./

RUN php artisan package:discover --ansi

# Ajuste de permisos
RUN chown -R www-data:www-data /var/www/html \
  && chmod -R 755 /var/www/html

EXPOSE 9000
CMD ["php-fpm"]
