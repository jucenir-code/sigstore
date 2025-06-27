#!/bin/bash

# Script para verificar o status do deploy
# Execute este script após o deploy para verificar se tudo está funcionando

set -e

echo "🔍 Verificando status do deploy..."

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para log colorido
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

error() {
    echo -e "${RED}[ERRO] $1${NC}"
}

warning() {
    echo -e "${YELLOW}[AVISO] $1${NC}"
}

info() {
    echo -e "${BLUE}[INFO] $1${NC}"
}

# Configurações
PROJECT_NAME="sigstore"
PROJECT_PATH="/var/www/$PROJECT_NAME"
DOCKER_PATH="/opt/$PROJECT_NAME"

# Verificar se é deploy tradicional ou Docker
if [ -d "$PROJECT_PATH" ]; then
    DEPLOY_TYPE="traditional"
    CURRENT_PATH=$PROJECT_PATH
elif [ -d "$DOCKER_PATH" ]; then
    DEPLOY_TYPE="docker"
    CURRENT_PATH=$DOCKER_PATH
else
    error "Projeto não encontrado. Execute o deploy primeiro."
    exit 1
fi

log "Tipo de deploy detectado: $DEPLOY_TYPE"
log "Caminho do projeto: $CURRENT_PATH"

# 1. Verificar estrutura do projeto
log "Verificando estrutura do projeto..."
if [ ! -f "$CURRENT_PATH/artisan" ]; then
    error "Arquivo artisan não encontrado"
else
    log "✅ Laravel detectado"
fi

if [ ! -f "$CURRENT_PATH/.env" ]; then
    error "Arquivo .env não encontrado"
else
    log "✅ Arquivo .env encontrado"
fi

# 2. Verificar permissões
log "Verificando permissões..."
if [ "$DEPLOY_TYPE" = "traditional" ]; then
    if [ -w "$CURRENT_PATH/storage" ]; then
        log "✅ Permissões de storage OK"
    else
        error "Permissões de storage incorretas"
    fi
    
    if [ -w "$CURRENT_PATH/bootstrap/cache" ]; then
        log "✅ Permissões de cache OK"
    else
        error "Permissões de cache incorretas"
    fi
fi

# 3. Verificar serviços
log "Verificando serviços..."

if [ "$DEPLOY_TYPE" = "traditional" ]; then
    # Verificar Nginx
    if systemctl is-active --quiet nginx; then
        log "✅ Nginx está rodando"
    else
        error "Nginx não está rodando"
    fi
    
    # Verificar PHP-FPM
    if systemctl is-active --quiet php8.2-fpm; then
        log "✅ PHP-FPM está rodando"
    else
        error "PHP-FPM não está rodando"
    fi
    
    # Verificar MySQL
    if systemctl is-active --quiet mysql; then
        log "✅ MySQL está rodando"
    else
        error "MySQL não está rodando"
    fi
    
    # Verificar Redis
    if systemctl is-active --quiet redis; then
        log "✅ Redis está rodando"
    else
        error "Redis não está rodando"
    fi
else
    # Verificar containers Docker
    cd $CURRENT_PATH
    if docker-compose -f docker-compose.prod.yml ps | grep -q "Up"; then
        log "✅ Containers Docker estão rodando"
        docker-compose -f docker-compose.prod.yml ps
    else
        error "Containers Docker não estão rodando"
    fi
fi

# 4. Verificar conectividade do banco
log "Verificando conectividade do banco..."
if [ "$DEPLOY_TYPE" = "traditional" ]; then
    if mysql -u root -p -e "USE sigstore; SELECT 1;" > /dev/null 2>&1; then
        log "✅ Conexão com banco OK"
    else
        error "Erro na conexão com banco"
    fi
else
    if docker-compose -f docker-compose.prod.yml exec -T db mysql -u root -p -e "USE sigstore; SELECT 1;" > /dev/null 2>&1; then
        log "✅ Conexão com banco OK"
    else
        error "Erro na conexão com banco"
    fi
fi

# 5. Verificar comandos Laravel
log "Verificando comandos Laravel..."
if [ "$DEPLOY_TYPE" = "traditional" ]; then
    cd $CURRENT_PATH
    if php artisan --version > /dev/null 2>&1; then
        log "✅ Laravel Artisan funcionando"
    else
        error "Laravel Artisan com erro"
    fi
else
    cd $CURRENT_PATH
    if docker-compose -f docker-compose.prod.yml exec -T app php artisan --version > /dev/null 2>&1; then
        log "✅ Laravel Artisan funcionando"
    else
        error "Laravel Artisan com erro"
    fi
fi

# 6. Verificar logs
log "Verificando logs..."
if [ -f "$CURRENT_PATH/storage/logs/laravel.log" ]; then
    log "✅ Log do Laravel encontrado"
    # Mostrar últimas 5 linhas de erro
    if grep -i "error\|exception" "$CURRENT_PATH/storage/logs/laravel.log" | tail -5; then
        warning "Encontrados erros nos logs"
    else
        log "✅ Nenhum erro recente nos logs"
    fi
else
    error "Log do Laravel não encontrado"
fi

# 7. Verificar configuração do Nginx
log "Verificando configuração do Nginx..."
if nginx -t > /dev/null 2>&1; then
    log "✅ Configuração do Nginx OK"
else
    error "Erro na configuração do Nginx"
fi

# 8. Verificar firewall
log "Verificando firewall..."
if ufw status | grep -q "Status: active"; then
    log "✅ Firewall ativo"
else
    warning "Firewall não está ativo"
fi

# 9. Verificar SSL (se configurado)
log "Verificando SSL..."
if [ -f "/etc/letsencrypt/live/$(hostname)/fullchain.pem" ]; then
    log "✅ Certificado SSL encontrado"
else
    info "Certificado SSL não encontrado (opcional)"
fi

# 10. Verificar backup
log "Verificando backup..."
if [ -f "/etc/cron.daily/${PROJECT_NAME}-backup" ] || [ -f "/etc/cron.daily/${PROJECT_NAME}-docker-backup" ]; then
    log "✅ Script de backup configurado"
else
    warning "Script de backup não encontrado"
fi

# 11. Teste de conectividade web
log "Testando conectividade web..."
if curl -s -o /dev/null -w "%{http_code}" http://localhost | grep -q "200\|301\|302"; then
    log "✅ Servidor web respondendo"
else
    error "Servidor web não está respondendo"
fi

# 12. Verificar uso de recursos
log "Verificando uso de recursos..."
echo "📊 Uso de CPU e Memória:"
top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d'%' -f1
free -h | grep Mem | awk '{print "Memória: " $3 "/" $2}'
df -h / | tail -1 | awk '{print "Disco: " $3 "/" $2 " (" $5 ")"}'

# 13. Verificar processos
log "Verificando processos..."
if [ "$DEPLOY_TYPE" = "traditional" ]; then
    echo "🔍 Processos PHP-FPM:"
    ps aux | grep php-fpm | grep -v grep | wc -l
    echo "🔍 Processos Nginx:"
    ps aux | grep nginx | grep -v grep | wc -l
else
    echo "🔍 Containers ativos:"
    docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
fi

# 14. Verificar cron jobs
log "Verificando cron jobs..."
if crontab -l 2>/dev/null | grep -q "$PROJECT_NAME"; then
    log "✅ Cron jobs configurados"
else
    info "Nenhum cron job específico encontrado"
fi

# Resumo final
echo ""
echo "🎯 RESUMO DO CHECKUP:"
echo "====================="
echo "Tipo de deploy: $DEPLOY_TYPE"
echo "Caminho: $CURRENT_PATH"
echo "Data/hora: $(date)"
echo ""

if [ "$DEPLOY_TYPE" = "traditional" ]; then
    echo "📋 Comandos úteis:"
    echo "• Ver logs: tail -f $CURRENT_PATH/storage/logs/laravel.log"
    echo "• Status serviços: sudo systemctl status nginx php8.2-fpm mysql redis"
    echo "• Reiniciar: sudo systemctl restart nginx php8.2-fpm"
    echo "• Backup manual: sudo /etc/cron.daily/${PROJECT_NAME}-backup"
else
    echo "📋 Comandos úteis:"
    echo "• Ver logs: docker-compose -f docker-compose.prod.yml logs -f"
    echo "• Status containers: docker-compose -f docker-compose.prod.yml ps"
    echo "• Reiniciar: docker-compose -f docker-compose.prod.yml restart"
    echo "• Atualizar: cd $CURRENT_PATH && ./update.sh"
fi

echo ""
log "✅ Verificação concluída!" 