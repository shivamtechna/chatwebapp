FROM php:8.1-apache

# Enable apache modules
RUN a2enmod rewrite

# Install dependencies for gd including libwebp-dev for WebP support
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install gd mysqli pdo pdo_mysql

# Set PHP timezone to Asia/Kolkata
RUN echo "date.timezone=Asia/Kolkata" > /usr/local/etc/php/conf.d/timezone.ini

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port 80
EXPOSE 80