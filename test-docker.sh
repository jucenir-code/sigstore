#!/bin/bash

# Script para testar se o Docker Compose está funcionando

echo "🧪 Testando Docker Compose..."

# Verificar se o arquivo .env existe e tem as variáveis necessárias
echo "🔍 Verificando .env..."
if [ ! -f .env ]; then
    echo "❌ Arquivo .env não encontrado!"
    exit 1
fi

# Verificar variáveis UID e GID
if ! grep -q "^UID=" .env; then
    echo "❌ Variável UID não encontrada no .env!"
    exit 1
fi

if ! grep -q "^GID=" .env; then
    echo "❌ Variável GID não encontrada no .env!"
    exit 1
fi

UID_VALUE=$(grep "^UID=" .env | cut -d'=' -f2)
GID_VALUE=$(grep "^GID=" .env | cut -d'=' -f2)

echo "✅ .env configurado: UID=$UID_VALUE, GID=$GID_VALUE"

# Testar sintaxe do docker-compose.yml
echo "🔍 Testando sintaxe do docker-compose.yml..."
if docker-compose config > /dev/null 2>&1; then
    echo "✅ Sintaxe do docker-compose.yml está correta!"
else
    echo "❌ Erro na sintaxe do docker-compose.yml!"
    docker-compose config
    exit 1
fi

# Testar se as variáveis estão sendo interpoladas corretamente
echo "🔍 Testando interpolação de variáveis..."
docker-compose config | grep -E "(UID|GID)" || echo "⚠️  Variáveis UID/GID não encontradas na configuração"

echo "✅ Teste concluído com sucesso!"
echo "🚀 Você pode executar: ./start-docker.sh" 