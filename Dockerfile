FROM php:8.2-apache

# Install required system dependencies & PostgreSQL dev libraries for PDO PgSQL / Supabase
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-install mysqli pdo pdo_mysql pdo_pgsql zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy project source code
COPY . /var/www/html/

# Set correct working directory and permissions
WORKDIR /var/www/html
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]
