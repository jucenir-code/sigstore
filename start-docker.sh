#!/bin/bash

# Script para iniciar o SigStore ERP com Docker Compose
# Resolve problemas de permissão e versão obsoleta

echo "🚀 Iniciando SigStore ERP..."

# Verificar se o arquivo .env existe
if [ ! -f .env ]; then
    echo "❌ Arquivo .env não encontrado!"
    echo "📝 Copiando env.example para .env..."
    cp env.example .env
    
    # Adicionar configurações do Docker
    echo "" >> .env
    echo "# Docker user permissions" >> .env
    echo "UID=1000" >> .env
    echo "GID=1000" >> .env
    
    echo "✅ Arquivo .env criado com sucesso!"
fi

# Verificar se as variáveis UID e GID estão definidas
if ! grep -q "UID=" .env; then
    echo "📝 Adicionando UID e GID ao .env..."
    echo "" >> .env
    echo "# Docker user permissions" >> .env
    echo "UID=1000" >> .env
    echo "GID=1000" >> .env
fi

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
sleep 10

# Verificar status dos containers
echo "📊 Status dos containers:"
docker-compose ps

echo "✅ SigStore ERP iniciado com sucesso!"
echo "🌐 Acesse: http://localhost:8080"
echo "🗄️  MySQL: localhost:3306"
echo "🔴 Redis: localhost:6379" 