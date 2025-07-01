#!/bin/bash

# Script para iniciar o SigStore ERP com Docker Compose

echo "🚀 Iniciando SigStore ERP..."

# Verificar se o arquivo .env existe
if [ ! -f .env ]; then
    echo "❌ Arquivo .env não encontrado!"
    echo "📝 Copiando env.example para .env..."
    
    if [ -f env.example ]; then
        cp env.example .env
        echo "✅ Arquivo .env criado com sucesso!"
    else
        echo "❌ Arquivo env.example não encontrado!"
        echo "📝 Criando arquivo .env básico..."
        cat > .env << EOF
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:iopZ2Sj5XGaz/B4L9XrfSTwWx+qY8g5djpzqyCIaVQc=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack

# Database
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=username
DB_PASSWORD=userpass
EOF
        echo "✅ Arquivo .env criado com configurações básicas!"
    fi
fi

# Parar containers existentes
echo "🛑 Parando containers existentes..."
docker-compose down

# Reconstruir a imagem
echo "🔨 Reconstruindo imagem Docker..."
docker-compose build --no-cache

# Iniciar os serviços
echo "▶️  Iniciando serviços..."
docker-compose up -d

# Aguardar um pouco para os serviços iniciarem
echo "⏳ Aguardando serviços iniciarem..."
sleep 15

# Corrigir permissões após a inicialização
echo "🔧 Corrigindo permissões..."
docker-compose exec app sudo chown -R jucenir:jucenir /var/www 2>/dev/null || true
docker-compose exec app sudo chmod -R 775 /var/www 2>/dev/null || true
docker-compose exec app git config --global --add safe.directory /var/www 2>/dev/null || true

# Verificar status dos containers
echo "📊 Status dos containers:"
docker-compose ps

echo "✅ SigStore ERP iniciado com sucesso!"
echo "🌐 Aplicação: http://localhost:8989"
echo "🗄️  PHPMyAdmin: http://localhost:8080"
echo "🔴 Redis: localhost:6379"
echo "📊 MySQL: localhost:3388" 