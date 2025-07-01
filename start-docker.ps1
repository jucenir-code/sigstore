# Script PowerShell para iniciar o SigStore ERP com Docker Compose

Write-Host "🚀 Iniciando SigStore ERP..." -ForegroundColor Green

# Verificar se o arquivo .env existe
if (-not (Test-Path .env)) {
    Write-Host "❌ Arquivo .env não encontrado!" -ForegroundColor Red
    Write-Host "📝 Copiando env.example para .env..." -ForegroundColor Yellow
    
    if (Test-Path env.example) {
        Copy-Item env.example .env
        Write-Host "✅ Arquivo .env criado com sucesso!" -ForegroundColor Green
    } else {
        Write-Host "❌ Arquivo env.example não encontrado!" -ForegroundColor Red
        Write-Host "📝 Criando arquivo .env básico..." -ForegroundColor Yellow
        
        $envContent = @"
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:iopZ2Sj5XGaz/B4L9XrfSTwWx+qY8g5djpzqyCIaVQc=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack

# Database
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=username
DB_PASSWORD=userpass
"@
        
        $envContent | Out-File -FilePath .env -Encoding UTF8
        Write-Host "✅ Arquivo .env criado com configurações básicas!" -ForegroundColor Green
    }
}

# Parar containers existentes
Write-Host "🛑 Parando containers existentes..." -ForegroundColor Yellow
docker-compose down

# Reconstruir a imagem
Write-Host "🔨 Reconstruindo imagem Docker..." -ForegroundColor Yellow
docker-compose build --no-cache

# Iniciar os serviços
Write-Host "▶️  Iniciando serviços..." -ForegroundColor Yellow
docker-compose up -d

# Aguardar um pouco para os serviços iniciarem
Write-Host "⏳ Aguardando serviços iniciarem..." -ForegroundColor Yellow
Start-Sleep -Seconds 15

# Corrigir permissões após a inicialização
Write-Host "🔧 Corrigindo permissões..." -ForegroundColor Yellow
docker-compose exec app sudo chown -R jucenir:jucenir /var/www 2>$null
docker-compose exec app sudo chmod -R 775 /var/www 2>$null
docker-compose exec app git config --global --add safe.directory /var/www 2>$null

# Verificar status dos containers
Write-Host "📊 Status dos containers:" -ForegroundColor Cyan
docker-compose ps

Write-Host "✅ SigStore ERP iniciado com sucesso!" -ForegroundColor Green
Write-Host "🌐 Aplicação: http://localhost:8989" -ForegroundColor Cyan
Write-Host "🗄️  PHPMyAdmin: http://localhost:8080" -ForegroundColor Cyan
Write-Host "🔴 Redis: localhost:6379" -ForegroundColor Cyan
Write-Host "📊 MySQL: localhost:3388" -ForegroundColor Cyan 