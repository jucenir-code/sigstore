#!/bin/bash

# Script de Deploy Docker para VPS - Laravel ERP
# Execute este script na sua VPS

set -e

echo "🐳 Iniciando deploy Docker do Laravel ERP..."

# Configurações
PROJECT_NAME="sigstore"
PROJECT_PATH="/opt/$PROJECT_NAME"
GIT_REPO="https://github.com/jucenir-code/sigstore.git"
BRANCH="master"

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

error() {
    echo -e "${RED}[ERRO] $1${NC}"
    exit 1
}

warning() {
    echo -e "${YELLOW}[AVISO] $1${NC}"
}

# Verificar se está rodando como root
# if [[ $EUID -eq 0 ]]; then
#    error "Este script não deve ser executado como root"
# fi

# 1. Atualizar sistema
log "Atualizando sistema..."
sudo apt update && sudo apt upgrade -y

# 2. Instalar Docker e Docker Compose
log "Instalando Docker e Docker Compose..."
sudo apt install -y \
    apt-transport-https \
    ca-certificates \
    curl \
    gnupg \
    lsb-release

# Adicionar repositório oficial do Docker
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

echo \
  "deb [arch=amd64 signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu \
  $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# Adicionar usuário ao grupo docker
sudo usermod -aG docker $USER

# 3. Instalar Nginx como proxy reverso
log "Instalando Nginx..."
sudo apt install -y nginx certbot python3-certbot-nginx

# 4. Criar diretório do projeto
log "Criando diretório do projeto..."
sudo mkdir -p $PROJECT_PATH
sudo chown -R $USER:$USER $PROJECT_PATH

# 5. Clonar/atualizar repositório
if [ -d "$PROJECT_PATH/.git" ]; then
    log "Atualizando repositório..."
    cd $PROJECT_PATH
    git fetch origin
    git reset --hard origin/$BRANCH
else
    log "Clonando repositório..."
    git clone -b $BRANCH $GIT_REPO $PROJECT_PATH
fi

# 6. Configurar arquivo .env para produção
log "Configurando arquivo .env..."
if [ ! -f "$PROJECT_PATH/.env" ]; then
    cp $PROJECT_PATH/.env.example $PROJECT_PATH/.env
fi

# Atualizar configurações do .env para produção
sed -i 's/APP_ENV=local/APP_ENV=production/' $PROJECT_PATH/.env
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' $PROJECT_PATH/.env
sed -i 's/DB_HOST=127.0.0.1/DB_HOST=db/' $PROJECT_PATH/.env
sed -i 's/REDIS_HOST=127.0.0.1/REDIS_HOST=redis/' $PROJECT_PATH/.env

# 7. Configurar docker-compose para produção
log "Configurando docker-compose para produção..."
cat > $PROJECT_PATH/docker-compose.prod.yml << 'EOF'
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: erp_app_prod
    restart: unless-stopped
    working_dir: /var/www/
    volumes:
      - ./:/var/www
      - ./storage:/var/www/storage
    networks:
      - erp_network
    depends_on:
      - db
      - redis
    environment:
      - APP_ENV=production
      - APP_DEBUG=false

  nginx:
    image: nginx:alpine
    container_name: erp_nginx_prod
    restart: unless-stopped
    ports:
      - "8080:80"
    volumes:
      - ./:/var/www
      - ./docker/nginx/conf.d:/etc/nginx/conf.d/
    networks:
      - erp_network
    depends_on:
      - app

  db:
    image: mysql:8.0
    container_name: erp_db_prod
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_ROOT_PASSWORD: ${DB_PASSWORD}
      MYSQL_PASSWORD: ${DB_PASSWORD}
      MYSQL_USER: ${DB_USERNAME}
    volumes:
      - dbdata:/var/lib/mysql
      - ./docker/mysql/my.cnf:/etc/mysql/my.cnf
    networks:
      - erp_network
    command: --default-authentication-plugin=mysql_native_password

  redis:
    image: redis:alpine
    container_name: erp_redis_prod
    restart: unless-stopped
    networks:
      - erp_network

  queue:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: erp_queue_prod
    restart: unless-stopped
    working_dir: /var/www/
    volumes:
      - ./:/var/www
    networks:
      - erp_network
    depends_on:
      - db
      - redis
    command: php artisan queue:work --sleep=3 --tries=3 --max-time=3600
    environment:
      - APP_ENV=production
      - APP_DEBUG=false

networks:
  erp_network:
    driver: bridge

volumes:
  dbdata:
    driver: local
EOF

# 8. Configurar Nginx como proxy reverso
log "Configurando Nginx como proxy reverso..."
sudo tee /etc/nginx/sites-available/$PROJECT_NAME << EOF
server {
    listen 80;
    server_name SEU_DOMINIO_AQUI;

    location / {
        proxy_pass http://localhost:8080;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }

    # Configurações para uploads grandes
    client_max_body_size 100M;
    proxy_read_timeout 300;
    proxy_connect_timeout 300;
    proxy_send_timeout 300;
}
EOF

# 9. Ativar site
sudo ln -sf /etc/nginx/sites-available/$PROJECT_NAME /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl restart nginx

# 10. Construir e iniciar containers
log "Construindo e iniciando containers..."
cd $PROJECT_PATH
docker compose -f docker-compose.prod.yml build
docker compose -f docker-compose.prod.yml up -d

# 11. Aguardar containers iniciarem
log "Aguardando containers iniciarem..."
sleep 30

# 12. Executar comandos Laravel
log "Executando comandos Laravel..."
docker compose -f docker-compose.prod.yml exec app git config --global --add safe.directory /var/www
docker compose -f docker-compose.prod.yml exec app chown -R www-data:www-data /var/www
docker compose -f docker-compose.prod.yml exec app chmod -R 775 /var/www
docker compose -f docker-compose.prod.yml exec app composer install --no-dev --optimize-autoloader
docker compose -f docker-compose.prod.yml exec app php artisan key:generate --force
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec app php artisan db:seed --force
docker compose -f docker-compose.prod.yml exec app php artisan config:cache
docker compose -f docker-compose.prod.yml exec app php artisan route:cache
docker compose -f docker-compose.prod.yml exec app php artisan view:cache

# 13. Configurar permissões
log "Configurando permissões..."
docker compose -f docker-compose.prod.yml exec app chown -R www-data:www-data /var/www
docker compose -f docker-compose.prod.yml exec app chmod -R 775 /var/www/storage
docker compose -f docker-compose.prod.yml exec app chmod -R 775 /var/www/bootstrap/cache

# 14. Configurar SSL (opcional)
read -p "Deseja configurar SSL com Let's Encrypt? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    log "Configurando SSL..."
    sudo certbot --nginx -d SEU_DOMINIO_AQUI
fi

# 15. Configurar firewall
log "Configurando firewall..."
sudo ufw allow 'Nginx Full'
sudo ufw allow OpenSSH
sudo ufw --force enable

# 16. Configurar backup automático
log "Configurando backup automático..."
sudo tee /etc/cron.daily/$PROJECT_NAME-docker-backup << EOF
#!/bin/bash
DATE=\$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/$PROJECT_NAME"
mkdir -p \$BACKUP_DIR

# Backup do banco
docker exec erp_db_prod mysqldump -u root -p\${DB_PASSWORD} \${DB_DATABASE} > \$BACKUP_DIR/db_backup_\$DATE.sql
gzip \$BACKUP_DIR/db_backup_\$DATE.sql

# Backup dos arquivos
tar -czf \$BACKUP_DIR/files_backup_\$DATE.tar.gz -C $PROJECT_PATH .

# Limpar backups antigos (manter 7 dias)
find \$BACKUP_DIR -name "*.sql.gz" -mtime +7 -delete
find \$BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
EOF

sudo chmod +x /etc/cron.daily/$PROJECT_NAME-docker-backup

# 17. Script de atualização
log "Criando script de atualização..."
cat > $PROJECT_PATH/update.sh << 'EOF'
#!/bin/bash
cd /opt/sigstore
git pull origin master
docker compose -f docker-compose.prod.yml down
docker compose -f docker-compose.prod.yml build
docker compose -f docker-compose.prod.yml up -d
docker compose -f docker-compose.prod.yml exec app composer install --no-dev --optimize-autoloader
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec app php artisan config:cache
docker compose -f docker-compose.prod.yml exec app php artisan route:cache
docker compose -f docker-compose.prod.yml exec app php artisan view:cache
echo "Atualização concluída!"
EOF

chmod +x $PROJECT_PATH/update.sh

log "✅ Deploy Docker concluído com sucesso!"
log "🌐 Acesse: http://SEU_DOMINIO_AQUI"
log "📁 Projeto em: $PROJECT_PATH"
log "🐳 Containers ativos:"
docker compose -f docker-compose.prod.yml ps

echo ""
echo "📋 Comandos úteis:"
echo "• Ver logs: docker compose -f docker-compose.prod.yml logs -f"
echo "• Parar: docker compose -f docker-compose.prod.yml down"
echo "• Atualizar: ./update.sh"
echo "• Backup manual: sudo /etc/cron.daily/$PROJECT_NAME-docker-backup" 