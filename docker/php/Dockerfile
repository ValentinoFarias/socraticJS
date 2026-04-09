FROM php:8.3-fpm

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Install cURL — needed to call the Anthropic API from PHP
RUN apt-get update && apt-get install -y libcurl4-openssl-dev \
    && docker-php-ext-install curl

# Set working directory
WORKDIR /var/www/html

# Use PHP's built-in server for Heroku (simpler than Apache)
CMD php -S 0.0.0.0:${PORT:-8000}
