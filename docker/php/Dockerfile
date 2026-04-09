FROM php:8.3-apache

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Install cURL — needed to call the Anthropic API from PHP
RUN apt-get update && apt-get install -y libcurl4-openssl-dev \
    && docker-php-ext-install curl

# Disable conflicting MPM modules (keep prefork which is the default)
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Heroku assigns a dynamic port at runtime — rewrite Apache's port to match
CMD sed -i "s/80/$PORT/g" /etc/apache2/ports.conf && apache2-foreground
