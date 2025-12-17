FROM php:8.2-apache

# Install dependencies and extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    dos2unix \
    && docker-php-ext-install pdo pdo_mysql zip

# Enable mod_rewrite
RUN a2enmod rewrite

# Set timezone
ENV TZ=America/Sao_Paulo
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone


# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80

# Move scripts to a safe location (outside volume mount) and fix line endings
COPY scripts/start.sh /usr/local/bin/start.sh
COPY scripts/scheduler.sh /usr/local/bin/scheduler.sh
RUN dos2unix /usr/local/bin/start.sh /usr/local/bin/scheduler.sh \
    && chmod +x /usr/local/bin/start.sh /usr/local/bin/scheduler.sh

# Start application
CMD ["/usr/local/bin/start.sh"]

