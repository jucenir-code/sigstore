# 🚀 Guia de Deploy para VPS - Laravel ERP

Este guia irá ajudá-lo a fazer o deploy do seu projeto Laravel ERP em uma VPS (Virtual Private Server).

## 📋 Pré-requisitos

- Uma VPS com Ubuntu 20.04+ ou Debian 11+
- Acesso SSH à VPS
- Domínio configurado (opcional, mas recomendado)
- Repositório Git do projeto

## 🎯 Opções de Deploy

### Opção 1: Deploy Tradicional (Recomendado para iniciantes)

Use o script `deploy.sh` para uma instalação tradicional com Nginx + PHP-FPM + MySQL.

### Opção 2: Deploy com Docker (Recomendado para produção)

Use o script `deploy-docker.sh` para uma instalação containerizada.

## 🔧 Preparação da VPS

### 1. Conectar via SSH
```bash
ssh usuario@ip-da-sua-vps
```

### 2. Criar usuário não-root (se necessário)
```bash
sudo adduser deploy
sudo usermod -aG sudo deploy
su - deploy
```

### 3. Configurar SSH (opcional)
```bash
# Editar /etc/ssh/sshd_config
sudo nano /etc/ssh/sshd_config

# Desabilitar login root
PermitRootLogin no

# Reiniciar SSH
sudo systemctl restart ssh
```

## 🚀 Deploy Tradicional

### 1. Baixar o script
```bash
wget https://raw.githubusercontent.com/seu-usuario/seu-repo/main/deploy.sh
chmod +x deploy.sh
```

### 2. Editar configurações
```bash
nano deploy.sh
```

Altere as seguintes variáveis:
- `GIT_REPO`: URL do seu repositório Git
- `SEU_DOMINIO_AQUI`: Seu domínio
- `SEU_DATABASE_NAME`: Nome do banco de dados

### 3. Executar o deploy
```bash
./deploy.sh
```

### 4. Configurar arquivo .env
```bash
nano /var/www/sigstore/.env
```

Configure as seguintes variáveis:
```env
APP_NAME="SigStore ERP"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-dominio.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sigstore
DB_USERNAME=sigstore_user
DB_PASSWORD=sua_senha_forte

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=seu-smtp.com
MAIL_PORT=587
MAIL_USERNAME=seu-email@dominio.com
MAIL_PASSWORD=sua_senha_email
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=seu-email@dominio.com
MAIL_FROM_NAME="${APP_NAME}"
```

## 🐳 Deploy com Docker

### 1. Baixar o script
```bash
wget https://raw.githubusercontent.com/seu-usuario/seu-repo/main/deploy-docker.sh
chmod +x deploy-docker.sh
```

### 2. Editar configurações
```bash
nano deploy-docker.sh
```

Altere as seguintes variáveis:
- `GIT_REPO`: URL do seu repositório Git
- `SEU_DOMINIO_AQUI`: Seu domínio

### 3. Executar o deploy
```bash
./deploy-docker.sh
```

## 🔒 Configurações de Segurança

### 1. Firewall
```bash
# Verificar status
sudo ufw status

# Regras básicas
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

### 2. Fail2ban (proteção contra ataques)
```bash
sudo apt install fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

### 3. Configurar fail2ban para Nginx
```bash
sudo nano /etc/fail2ban/jail.local
```

Adicione:
```ini
[nginx-http-auth]
enabled = true
port = http,https
filter = nginx-http-auth
logpath = /var/log/nginx/error.log
maxretry = 3
bantime = 600
```

### 4. Reiniciar fail2ban
```bash
sudo systemctl restart fail2ban
```

## 📊 Monitoramento

### 1. Instalar ferramentas de monitoramento
```bash
sudo apt install htop iotop nethogs
```

### 2. Configurar logrotate
```bash
sudo nano /etc/logrotate.d/laravel
```

Adicione:
```
/var/www/sigstore/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    notifempty
    create 644 www-data www-data
}
```

## 🔄 Atualizações

### Deploy Tradicional
```bash
cd /var/www/sigstore
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl restart php8.2-fpm
```

### Deploy Docker
```bash
cd /opt/sigstore
./update.sh
```

## 💾 Backups

### Backup Manual
```bash
# Backup do banco
mysqldump -u root -p sigstore > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup dos arquivos
tar -czf files_backup_$(date +%Y%m%d_%H%M%S).tar.gz /var/www/sigstore
```

### Backup Automático
Os scripts já configuram backup automático diário em `/etc/cron.daily/`.

## 🐛 Troubleshooting

### 1. Verificar logs
```bash
# Logs do Laravel
tail -f /var/www/sigstore/storage/logs/laravel.log

# Logs do Nginx
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log

# Logs do PHP-FPM
sudo tail -f /var/log/php8.2-fpm.log
```

### 2. Verificar permissões
```bash
sudo chown -R www-data:www-data /var/www/sigstore
sudo chmod -R 755 /var/www/sigstore
sudo chmod -R 775 /var/www/sigstore/storage
sudo chmod -R 775 /var/www/sigstore/bootstrap/cache
```

### 3. Verificar serviços
```bash
# Status dos serviços
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status mysql
sudo systemctl status redis

# Reiniciar serviços
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
sudo systemctl restart mysql
sudo systemctl restart redis
```

### 4. Verificar configuração do Nginx
```bash
sudo nginx -t
```

## 🔧 Otimizações

### 1. PHP-FPM
```bash
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

Ajustar:
```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
```

### 2. MySQL
```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

Ajustar:
```ini
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
max_connections = 200
```

### 3. Redis
```bash
sudo nano /etc/redis/redis.conf
```

Ajustar:
```ini
maxmemory 256mb
maxmemory-policy allkeys-lru
```

## 📞 Suporte

Se encontrar problemas:

1. Verifique os logs primeiro
2. Teste em ambiente local
3. Consulte a documentação do Laravel
4. Verifique as configurações de rede da VPS

## 🔗 Links Úteis

- [Documentação Laravel](https://laravel.com/docs)
- [Nginx Documentation](https://nginx.org/en/docs/)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Docker Documentation](https://docs.docker.com/)

---

**⚠️ Importante:** Sempre faça backup antes de atualizações e mantenha suas credenciais seguras! 