FROM php:8.2-apache

# Install required PHP extensions for database connection
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite for routing/clean URLs if needed
RUN a2enmod rewrite
