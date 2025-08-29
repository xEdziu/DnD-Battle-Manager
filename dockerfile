# Dockerfile for D&D Battle Manager
FROM php:8.1-apache

# Install system dependencies including SQLite
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    sqlite3 \
    && rm -rf /var/lib/apt/lists/*

# Install PHP SQLite extension
RUN docker-php-ext-install pdo_sqlite

# Enable Apache mod_rewrite (optional, for cleaner URLs)
RUN a2enmod rewrite

# Copy all application code
COPY . /var/www/html/

# Create necessary directories and set permissions
RUN mkdir -p /var/www/html/data && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 777 /var/www/html/data

# Remove any existing empty database file to ensure proper initialization
RUN rm -f /var/www/html/data.sqlite

EXPOSE 80

# The Apache PHP image automatically runs Apache on container start