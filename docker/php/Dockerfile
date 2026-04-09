FROM php:8.3-apache

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Install cURL — needed to call the Anthropic API from PHP
RUN apt-get update && apt-get install -y libcurl4-openssl-dev \
    && docker-php-ext-install curl

# Completely reset Apache MPM configuration
# Remove all loaded mpm modules and their symlinks
RUN a2dismod mpm_event mpm_worker mpm_prefork 2>/dev/null || true && \
    rm -rf /etc/apache2/mods-enabled/mpm_*

# Re-enable only mpm_prefork (the safe single-threaded MPM)
RUN a2enmod mpm_prefork

# Enable mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Heroku assigns a dynamic port at runtime — rewrite Apache's port to match
CMD sed -i "s/80/$PORT/g" /etc/apache2/ports.conf && apache2-foreground
