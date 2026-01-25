FROM php:8.3-apache

# Install needed PHP extensions
RUN apt-get update && apt-get install -y \
    libpq-dev libjpeg-dev libpng-dev libfreetype6-dev libicu-dev \
    git unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql gd intl \
    && a2enmod rewrite

# Copy Apache vhost config
COPY ./docker/sahl-cms/vhost.conf /etc/apache2/sites-available/000-default.conf

# Set working directory
WORKDIR /var/www/html

# Composer install
#COPY ./composer.json ./composer.lock /var/www/html/
COPY ./composer.json /var/www/html/
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

# Install PHP dependencies
# RUN composer update --no-interaction --no-progress --prefer-dist \
#   && composer install --no-interaction --no-progress --prefer-dist
RUN composer install --no-interaction --no-progress --prefer-dist

# Create and set permissions for writable directories (Nette)
RUN mkdir -p /var/www/html/log /var/www/html/temp \
    && chmod -R 775 /var/www/html/log /var/www/html/temp

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Copying config.local.neon
COPY docker/sahl-cms/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]

