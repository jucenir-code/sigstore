FROM php:8.2-fpm

# Instalar dependências do sistema
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
    libxml2-dev

# Limpar cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensões PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl soap

# Obter Composer mais recente
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Definir diretório de trabalho
WORKDIR /var/www

# Ajustar permissões
RUN chown -R www-data:www-data /var/www
RUN chmod -R 775 /var/www

# Configurar Git para aceitar o diretório
RUN git config --global --add safe.directory /var/www

USER www-data

# Expor porta 9000
EXPOSE 9000

# Comando padrão
CMD ["php-fpm"] 