#!/bin/bash

# Script para iniciar o SigStore ERP com Docker Compose versão antiga
# Usa docker-compose-v1.yml que é compatível com versões antigas

echo "🚀 Iniciando SigStore ERP (Modo Legacy)..."

# Verificar e configurar o arquivo .env
echo "🔍 Verificando arquivo .env..."
if [ ! -f .env ]; then
    echo "❌ Arquivo .env não encontrado!"
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

# Verificar se as variáveis UID e GID estão definidas
if ! grep -q "UID=" .env; then
    echo "📝 Adicionando UID e GID ao .env..."
    echo "" >> .env
    echo "# Docker user permissions" >> .env
    echo "UID=1000" >> .env
    echo "GID=1000" >> .env
fi

echo "✅ Verificação do .env concluída!"
echo "📋 Valores: UID=$(grep "^UID=" .env | cut -d'=' -f2), GID=$(grep "^GID=" .env | cut -d'=' -f2)"

# Usar arquivo de configuração legacy
echo "📝 Usando configuração legacy..."
cp docker-compose-v1.yml docker-compose.yml

# Testar configuração
echo "🧪 Testando configuração..."
if docker-compose config > /dev/null 2>&1; then
    echo "✅ Configuração válida!"
else
    echo "❌ Erro na configuração!"
    docker-compose config
    exit 1
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
docker-compose exec app sudo chown -R appuser:appuser /var/www 2>/dev/null || true
docker-compose exec app sudo chmod -R 775 /var/www 2>/dev/null || true

# Verificar status dos containers
echo "📊 Status dos containers:"
docker-compose ps

echo "✅ SigStore ERP iniciado com sucesso (Modo Legacy)!"
echo "🌐 Acesse: http://localhost:8080"
echo "🗄️  MySQL: localhost:3306"
echo "🔴 Redis: localhost:6379" 