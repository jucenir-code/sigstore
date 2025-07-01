#!/bin/bash

# Script para limpar arquivos problemáticos do macOS
# Execute este script antes do deploy

echo "🧹 Limpando arquivos problemáticos do macOS..."

# Remover arquivos ._* (metadados do macOS)
echo "Removendo arquivos ._*..."
find . -name "._*" -type f -delete 2>/dev/null || true

# Remover arquivos .DS_Store
echo "Removendo arquivos .DS_Store..."
find . -name ".DS_Store" -type f -delete 2>/dev/null || true

# Remover diretórios __MACOSX
echo "Removendo diretórios __MACOSX..."
find . -name "__MACOSX" -type d -exec rm -rf {} + 2>/dev/null || true

# Limpar cache do Git
echo "Limpando cache do Git..."
git rm -r --cached . 2>/dev/null || true
git add .
git commit -m "Limpeza de arquivos do macOS" 2>/dev/null || true

echo "✅ Limpeza concluída!"
echo "Agora você pode executar o deploy." 