FROM php:8.3-fpm

# set your user name, ex: user=jucenir
ARG user=jucenir
ARG uid=1000

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    libxml2-dev \
    sudo

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl soap

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user && \
    echo "$user ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers

# Create composer directory and set permissions
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Install redis
RUN pecl install -o -f redis \
    &&  rm -rf /tmp/pear \
    &&  docker-php-ext-enable redis

# Set working directory
WORKDIR /var/www

# Copy custom configurations PHP
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

# Configure Git to accept the directory and set permissions
RUN git config --global --add safe.directory /var/www && \
    git config --global user.name "Docker User" && \
    git config --global user.email "docker@example.com"

# Change to user
USER $user

# Create entrypoint script to fix permissions
RUN echo '#!/bin/bash' > /usr/local/bin/docker-entrypoint.sh && \
    echo 'set -e' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'echo "🚀 Iniciando container..."' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'sleep 2' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'if [ -d "/var/www" ]; then' >> /usr/local/bin/docker-entrypoint.sh && \
    echo '    echo "🔧 Corrigindo permissões..."' >> /usr/local/bin/docker-entrypoint.sh && \
    echo '    sudo chown -R $user:$user /var/www' >> /usr/local/bin/docker-entrypoint.sh && \
    echo '    sudo chmod -R 775 /var/www' >> /usr/local/bin/docker-entrypoint.sh && \
    echo '    echo "✅ Permissões corrigidas!"' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'fi' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'exec "$@"' >> /usr/local/bin/docker-entrypoint.sh && \
    chmod +x /usr/local/bin/docker-entrypoint.sh

# Set entrypoint
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

# Default command
CMD ["php-fpm"]