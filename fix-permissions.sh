#!/bin/bash

# Script para corrigir permissões do SigStore ERP
# Deve ser executado após o container estar rodando

echo "🔧 Corrigindo permissões do SigStore ERP..."

# Verificar se o container está rodando
if ! docker-compose ps | grep -q "sigstore_app.*Up"; then
    echo "❌ Container sigstore_app não está rodando!"
    echo "🚀 Iniciando containers primeiro..."
    docker-compose up -d
    sleep 10
fi

# Corrigir permissões dentro do container
echo "📝 Ajustando permissões dos arquivos..."
docker-compose exec app sudo chown -R appuser:appuser /var/www
docker-compose exec app sudo chmod -R 775 /var/www

# Verificar se as permissões foram corrigidas
echo "✅ Verificando permissões..."
docker-compose exec app ls -la /var/www | head -5

echo "🎉 Permissões corrigidas com sucesso!"
echo "🌐 Acesse: http://localhost:8080" 