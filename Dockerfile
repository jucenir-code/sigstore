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

# Criar usuário com UID/GID dinâmicos
ARG UID=1000
ARG GID=1000
RUN groupadd -g $GID appuser && \
    useradd -u $UID -g $GID -m -s /bin/bash appuser

# Ajustar permissões
RUN chown -R appuser:appuser /var/www
RUN chmod -R 775 /var/www

# Configurar Git para aceitar o diretório e evitar problemas de permissão
RUN git config --global --add safe.directory /var/www && \
    git config --global user.name "Docker User" && \
    git config --global user.email "docker@example.com"

# Mudar para o usuário appuser
USER appuser

# Expor porta 9000
EXPOSE 9000

# Comando padrão
CMD ["php-fpm"] 