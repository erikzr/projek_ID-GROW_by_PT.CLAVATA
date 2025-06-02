# Menggunakan PHP 8.2 dengan FPM
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy seluruh aplikasi dulu
COPY --chown=www-data:www-data . /var/www/html

# Install dependencies jika composer.json ada
RUN if [ -f "composer.json" ]; then \
        composer install --no-dev --optimize-autoloader --no-scripts; \
    else \
        echo "Warning: composer.json not found, skipping composer install"; \
    fi

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Create storage dan bootstrap cache directories jika belum ada
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views \
    && mkdir -p bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Change current user to www
USER www-data

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]