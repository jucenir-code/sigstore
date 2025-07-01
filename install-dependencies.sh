#!/bin/bash

# Script para instalar dependências do Composer

echo "📦 Instalando dependências do Composer..."

# Verificar se o container está rodando
if ! docker-compose ps | grep -q "app.*Up"; then
    echo "❌ Container app não está rodando!"
    echo "🚀 Iniciando containers primeiro..."
    docker-compose up -d
    sleep 15
fi

# Corrigir permissões primeiro
echo "🔧 Corrigindo permissões..."
docker-compose exec app sudo chown -R jucenir:jucenir /var/www
docker-compose exec app sudo chmod -R 775 /var/www
docker-compose exec app git config --global --add safe.directory /var/www

# Instalar dependências do Composer
echo "📦 Instalando dependências..."
docker-compose exec app composer install --no-interaction --prefer-dist --optimize-autoloader

# Verificar se a instalação foi bem-sucedida
if [ $? -eq 0 ]; then
    echo "✅ Dependências instaladas com sucesso!"
    
    # Gerar chave da aplicação se necessário
    echo "🔑 Gerando chave da aplicação..."
    docker-compose exec app php artisan key:generate --force
    
    # Limpar cache
    echo "🧹 Limpando cache..."
    docker-compose exec app php artisan config:clear
    docker-compose exec app php artisan cache:clear
    docker-compose exec app php artisan view:clear
    
    echo "🎉 Instalação concluída com sucesso!"
    echo "🌐 Acesse: http://localhost:8989"
else
    echo "❌ Erro ao instalar dependências!"
    echo "🔧 Verifique os logs:"
    docker-compose logs app
    exit 1
fi 