#!/bin/bash

# Script para verificar e configurar o arquivo .env

echo "🔍 Verificando arquivo .env..."

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
echo "📋 Valores atuais:"
echo "   UID: $(grep "^UID=" .env | cut -d'=' -f2)"
echo "   GID: $(grep "^GID=" .env | cut -d'=' -f2)" 