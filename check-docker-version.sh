#!/bin/bash

# Script para verificar versão do Docker Compose e ajustar configuração

echo "🔍 Verificando versão do Docker Compose..."

# Verificar se docker-compose está instalado
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose não está instalado!"
    echo "📝 Instalando Docker Compose..."
    
    # Tentar instalar docker-compose
    sudo curl -L "https://github.com/docker/compose/releases/download/v2.20.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    sudo chmod +x /usr/local/bin/docker-compose
    
    if ! command -v docker-compose &> /dev/null; then
        echo "❌ Falha ao instalar Docker Compose!"
        exit 1
    fi
fi

# Verificar versão
COMPOSE_VERSION=$(docker-compose --version | grep -oP '\d+\.\d+\.\d+' | head -1)
echo "📋 Versão do Docker Compose: $COMPOSE_VERSION"

# Extrair versão principal
MAJOR_VERSION=$(echo $COMPOSE_VERSION | cut -d. -f1)
MINOR_VERSION=$(echo $COMPOSE_VERSION | cut -d. -f2)

echo "📊 Versão principal: $MAJOR_VERSION.$MINOR_VERSION"

# Ajustar docker-compose.yml baseado na versão
if [ "$MAJOR_VERSION" -eq "1" ]; then
    echo "⚠️  Versão 1.x detectada - usando configuração compatível..."
    # Para versão 1.x, usar sintaxe mais simples
    cat > docker-compose.yml << 'EOF'
# Docker Compose para SigStore ERP - Versão 1.x
version: '2.4'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
      args:
        UID: ${UID}
        GID: ${GID}
    container_name: sigstore_app
    restart: unless-stopped
    working_dir: /var/www
    user: "${UID}:${GID}"
    volumes:
      - ./:/var/www
      - ./docker/nginx/conf.d:/etc/nginx/conf.d
    networks:
      - erp_network
    depends_on:
      - db
      - redis

  nginx:
    image: nginx:alpine
    container_name: sigstore_nginx
    restart: unless-stopped
    ports:
      - "8080:80"
      - "443:443"
    volumes:
      - ./:/var/www
      - ./docker/nginx/conf.d:/etc/nginx/conf.d
    networks:
      - erp_network
    depends_on:
      - app

  db:
    image: mysql:8.0
    container_name: sigstore_mysql
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: sigstore
      MYSQL_ROOT_PASSWORD: root123
      MYSQL_USER: sigstore
      MYSQL_PASSWORD: sigstore123
    volumes:
      - dbdata:/var/lib/mysql
      - ./docker/mysql/my.cnf:/etc/mysql/conf.d/my.cnf
    ports:
      - "3306:3306"
    networks:
      - erp_network

  redis:
    image: redis:alpine
    container_name: sigstore_redis
    restart: unless-stopped
    ports:
      - "6379:6379"
    networks:
      - erp_network

volumes:
  dbdata:

networks:
  erp_network:
    driver: bridge
EOF
    echo "✅ docker-compose.yml ajustado para versão 1.x"
    
elif [ "$MAJOR_VERSION" -eq "2" ] && [ "$MINOR_VERSION" -lt "20" ]; then
    echo "⚠️  Versão 2.x < 2.20 detectada - usando configuração compatível..."
    # Manter versão 2.4 que já está configurada
    echo "✅ docker-compose.yml já está compatível"
    
else
    echo "✅ Versão moderna detectada - usando configuração padrão..."
    # Para versões modernas, remover version
    cat > docker-compose.yml << 'EOF'
# Docker Compose para SigStore ERP - Versão Moderna
# Configurado para resolver problemas de permissão e versão obsoleta
# UID e GID são definidos no arquivo .env para evitar problemas de permissão

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
      args:
        UID: ${UID}
        GID: ${GID}
    container_name: sigstore_app
    restart: unless-stopped
    working_dir: /var/www
    user: "${UID}:${GID}"
    volumes:
      - ./:/var/www
      - ./docker/nginx/conf.d:/etc/nginx/conf.d
    networks:
      - erp_network
    depends_on:
      - db
      - redis

  nginx:
    image: nginx:alpine
    container_name: sigstore_nginx
    restart: unless-stopped
    ports:
      - "8080:80"
      - "443:443"
    volumes:
      - ./:/var/www
      - ./docker/nginx/conf.d:/etc/nginx/conf.d
    networks:
      - erp_network
    depends_on:
      - app

  db:
    image: mysql:8.0
    container_name: sigstore_mysql
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: sigstore
      MYSQL_ROOT_PASSWORD: root123
      MYSQL_USER: sigstore
      MYSQL_PASSWORD: sigstore123
    volumes:
      - dbdata:/var/lib/mysql
      - ./docker/mysql/my.cnf:/etc/mysql/conf.d/my.cnf
    ports:
      - "3306:3306"
    networks:
      - erp_network

  redis:
    image: redis:alpine
    container_name: sigstore_redis
    restart: unless-stopped
    ports:
      - "6379:6379"
    networks:
      - erp_network

volumes:
  dbdata:

networks:
  erp_network:
    driver: bridge
EOF
    echo "✅ docker-compose.yml ajustado para versão moderna"
fi

# Testar configuração
echo "🧪 Testando configuração..."
if docker-compose config > /dev/null 2>&1; then
    echo "✅ Configuração válida!"
else
    echo "❌ Erro na configuração!"
    docker-compose config
    exit 1
fi

echo "🎉 Verificação concluída com sucesso!" 