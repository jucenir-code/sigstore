#!/bin/bash

# Script de Deploy para VPS - Laravel ERP
# Execute este script na sua VPS

set -e

echo "🚀 Iniciando deploy do Laravel ERP..."

# Configurações
PROJECT_NAME="sigstore"
PROJECT_PATH="/var/www/$PROJECT_NAME"
BACKUP_PATH="/var/backups/$PROJECT_NAME"
GIT_REPO="SEU_REPOSITORIO_GIT_AQUI"
BRANCH="main"

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Função para log colorido
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
if [[ $EUID -eq 0 ]]; then
   error "Este script não deve ser executado como root"
fi

# 1. Atualizar sistema
log "Atualizando sistema..."
sudo apt update && sudo apt upgrade -y

# 2. Instalar dependências do sistema
log "Instalando dependências do sistema..."
sudo apt install -y \
    nginx \
    mysql-server \
    php8.2-fpm \
    php8.2-mysql \
    php8.2-xml \
    php8.2-curl \
    php8.2-gd \
    php8.2-mbstring \
    php8.2-zip \
    php8.2-bcmath \
    php8.2-soap \
    php8.2-intl \
    php8.2-redis \
    redis-server \
    git \
    unzip \
    curl \
    supervisor \
    certbot \
    python3-certbot-nginx

# 3. Configurar MySQL
log "Configurando MySQL..."
sudo mysql_secure_installation

# 4. Criar diretório do projeto
log "Criando diretório do projeto..."
sudo mkdir -p $PROJECT_PATH
sudo mkdir -p $BACKUP_PATH
sudo chown -R $USER:$USER $PROJECT_PATH
sudo chown -R $USER:$USER $BACKUP_PATH

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

# 6. Configurar permissões
log "Configurando permissões..."
sudo chown -R www-data:www-data $PROJECT_PATH
sudo chmod -R 755 $PROJECT_PATH
sudo chmod -R 775 $PROJECT_PATH/storage
sudo chmod -R 775 $PROJECT_PATH/bootstrap/cache

# 7. Instalar dependências PHP
log "Instalando dependências PHP..."
cd $PROJECT_PATH
composer install --no-dev --optimize-autoloader

# 8. Configurar arquivo .env
if [ ! -f "$PROJECT_PATH/.env" ]; then
    log "Criando arquivo .env..."
    cp $PROJECT_PATH/.env.example $PROJECT_PATH/.env
    php artisan key:generate
fi

# 9. Configurar banco de dados
log "Configurando banco de dados..."
php artisan migrate --force
php artisan db:seed --force

# 10. Otimizar Laravel
log "Otimizando Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 11. Configurar Nginx
log "Configurando Nginx..."
sudo tee /etc/nginx/sites-available/$PROJECT_NAME << EOF
server {
    listen 80;
    server_name SEU_DOMINIO_AQUI;
    root $PROJECT_PATH/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# 12. Ativar site
sudo ln -sf /etc/nginx/sites-available/$PROJECT_NAME /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl restart nginx

# 13. Configurar PHP-FPM
log "Configurando PHP-FPM..."
sudo sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 100M/' /etc/php/8.2/fpm/php.ini
sudo sed -i 's/post_max_size = 8M/post_max_size = 100M/' /etc/php/8.2/fpm/php.ini
sudo sed -i 's/memory_limit = 128M/memory_limit = 512M/' /etc/php/8.2/fpm/php.ini
sudo systemctl restart php8.2-fpm

# 14. Configurar Supervisor para filas
log "Configurando Supervisor..."
sudo tee /etc/supervisor/conf.d/$PROJECT_NAME-worker.conf << EOF
[program:$PROJECT_NAME-worker]
process_name=%(program_name)s_%(process_num)02d
command=php $PROJECT_PATH/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=$PROJECT_PATH/storage/logs/worker.log
stopwaitsecs=3600
EOF

sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start $PROJECT_NAME-worker:*

# 15. Configurar SSL (opcional)
read -p "Deseja configurar SSL com Let's Encrypt? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    log "Configurando SSL..."
    sudo certbot --nginx -d SEU_DOMINIO_AQUI
fi

# 16. Configurar firewall
log "Configurando firewall..."
sudo ufw allow 'Nginx Full'
sudo ufw allow OpenSSH
sudo ufw --force enable

# 17. Configurar backup automático
log "Configurando backup automático..."
sudo tee /etc/cron.daily/$PROJECT_NAME-backup << EOF
#!/bin/bash
DATE=\$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_PATH/backup_\$DATE.sql"
mysqldump -u root -p SEU_DATABASE_NAME > \$BACKUP_FILE
gzip \$BACKUP_FILE
find $BACKUP_PATH -name "*.sql.gz" -mtime +7 -delete
EOF

sudo chmod +x /etc/cron.daily/$PROJECT_NAME-backup

log "✅ Deploy concluído com sucesso!"
log "🌐 Acesse: http://SEU_DOMINIO_AQUI"
log "📁 Projeto em: $PROJECT_PATH"
log "💾 Backups em: $BACKUP_PATH"

echo ""
echo "📋 Próximos passos:"
echo "1. Configure o arquivo .env com suas credenciais de banco"
echo "2. Configure o domínio no arquivo do Nginx"
echo "3. Configure as variáveis de ambiente necessárias"
echo "4. Teste o sistema" 