FROM php:8.2-apache

# Install mysqli + pdo_mysql
RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli pdo_mysql

# Enable Apache modules
RUN a2enmod rewrite headers

WORKDIR /var/www/html
