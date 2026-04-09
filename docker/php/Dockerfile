FROM php:8.3-cli

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Install cURL — needed to call the Anthropic API from PHP
RUN apt-get update && apt-get install -y libcurl4-openssl-dev \
    && docker-php-ext-install curl \
    && rm -rf /var/lib/apt/lists/*

# Copy source files into the container
COPY src/ /var/www/html/

# Set working directory
WORKDIR /var/www/html

# Heroku assigns a dynamic $PORT — PHP built-in server listens on it
CMD php -S 0.0.0.0:${PORT:-8080} -t /var/www/html
