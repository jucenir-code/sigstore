#!/bin/bash

# Script de entrada para corrigir permissões automaticamente
set -e

echo "🚀 Iniciando container SigStore..."

# Aguardar um pouco para os arquivos serem montados
sleep 2

# Corrigir permissões se necessário
if [ -d "/var/www" ]; then
    echo "🔧 Corrigindo permissões dos arquivos..."
    sudo chown -R appuser:appuser /var/www
    sudo chmod -R 775 /var/www
    echo "✅ Permissões corrigidas!"
fi

# Executar o comando original
exec "$@" 