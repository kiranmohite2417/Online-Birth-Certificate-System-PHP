FROM php:8.0-apache

RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip \
 && docker-php-ext-install mysqli pdo pdo_mysql

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

