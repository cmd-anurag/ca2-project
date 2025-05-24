FROM php:8.1-apache

#  PHP extensions 
RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN a2enmod rewrite

# Set working directory (optional)
WORKDIR /var/www/html

COPY backend/ /var/www/html/backend/

COPY config/ /var/www/html/config/

COPY public/ /var/www/html/public/

COPY vendor/ /var/www/html/vendor/

COPY composer.json composer.lock /var/www/html/

COPY backend/.env /var/www/html/backend/.env


# just some precautions
RUN chown -R www-data:www-data /var/www/html

# Expose port 80 for HTTP
EXPOSE 80

# lets go
CMD ["apache2-foreground"]