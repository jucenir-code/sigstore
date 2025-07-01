#!/bin/bash

# Script para iniciar o SigStore ERP com Docker Compose
# Resolve problemas de permissão e versão obsoleta

echo "🚀 Iniciando SigStore ERP..."

# Verificar versão do Docker Compose e ajustar configuração
echo "🔍 Verificando versão do Docker Compose..."
if [ -f "check-docker-version.sh" ]; then
    chmod +x check-docker-version.sh
    ./check-docker-version.sh
else
    echo "⚠️  Script check-docker-version.sh não encontrado, continuando..."
fi

# Verificar e configurar o arquivo .env
echo "🔍 Verificando arquivo .env..."
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

# Docker user permissions
UID=1000
GID=1000
EOF
        echo "✅ Arquivo .env criado com configurações básicas!"
    fi
fi

# Verificar se as variáveis UID e GID estão definidas
if ! grep -q "UID=" .env; then
    echo "📝 Adicionando UID e GID ao .env..."
    echo "" >> .env
    echo "# Docker user permissions" >> .env
    echo "UID=1000" >> .env
    echo "GID=1000" >> .env
fi

# Verificar se as variáveis têm valores
UID_VALUE=$(grep "^UID=" .env | cut -d'=' -f2)
GID_VALUE=$(grep "^GID=" .env | cut -d'=' -f2)

if [ -z "$UID_VALUE" ] || [ "$UID_VALUE" = "" ]; then
    echo "⚠️  UID não definido, definindo como 1000..."
    sed -i 's/^UID=.*/UID=1000/' .env
fi

if [ -z "$GID_VALUE" ] || [ "$GID_VALUE" = "" ]; then
    echo "⚠️  GID não definido, definindo como 1000..."
    sed -i 's/^GID=.*/GID=1000/' .env
fi

echo "✅ Verificação do .env concluída!"
echo "📋 Valores: UID=$(grep "^UID=" .env | cut -d'=' -f2), GID=$(grep "^GID=" .env | cut -d'=' -f2)"

# Testar configuração do Docker Compose
echo "🧪 Testando configuração do Docker Compose..."
if ! docker-compose config > /dev/null 2>&1; then
    echo "❌ Erro na configuração do Docker Compose!"
    docker-compose config
    exit 1
fi
echo "✅ Configuração válida!"

# Parar containers existentes
echo "🛑 Parando containers existentes..."
docker-compose down

# Reconstruir a imagem com os novos argumentos
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
docker-compose exec app sudo chown -R appuser:appuser /var/www 2>/dev/null || true
docker-compose exec app sudo chmod -R 775 /var/www 2>/dev/null || true

# Verificar status dos containers
echo "📊 Status dos containers:"
docker-compose ps

echo "✅ SigStore ERP iniciado com sucesso!"
echo "🌐 Acesse: http://localhost:8080"
echo "🗄️  MySQL: localhost:3306"
echo "🔴 Redis: localhost:6379" 