#!/bin/bash

# Script para corrigir permissões do SigStore ERP

echo "🔧 Corrigindo permissões do SigStore ERP..."

# Verificar se o container está rodando
if ! docker-compose ps | grep -q "app.*Up"; then
    echo "❌ Container app não está rodando!"
    echo "🚀 Iniciando containers primeiro..."
    docker-compose up -d
    sleep 10
fi

# Corrigir permissões dentro do container
echo "📝 Ajustando permissões dos arquivos..."
docker-compose exec app sudo chown -R jucenir:jucenir /var/www
docker-compose exec app sudo chmod -R 775 /var/www

# Configurar Git para aceitar o diretório
echo "🔧 Configurando Git..."
docker-compose exec app git config --global --add safe.directory /var/www

# Verificar se as permissões foram corrigidas
echo "✅ Verificando permissões..."
docker-compose exec app ls -la /var/www | head -5

echo "🎉 Permissões corrigidas com sucesso!"
echo "🌐 Acesse: http://localhost:8989"
echo "🗄️  PHPMyAdmin: http://localhost:8080"
echo "🔴 Redis: localhost:6379" 