# Script PowerShell para iniciar o SigStore ERP com Docker Compose
# Resolve problemas de permissão e versão obsoleta

Write-Host "🚀 Iniciando SigStore ERP..." -ForegroundColor Green

# Verificar se o arquivo .env existe
if (-not (Test-Path .env)) {
    Write-Host "❌ Arquivo .env não encontrado!" -ForegroundColor Red
    Write-Host "📝 Copiando env.example para .env..." -ForegroundColor Yellow
    
    if (Test-Path env.example) {
        Copy-Item env.example .env
        
        # Adicionar configurações do Docker
        Add-Content .env ""
        Add-Content .env "# Docker user permissions"
        Add-Content .env "UID=1000"
        Add-Content .env "GID=1000"
        
        Write-Host "✅ Arquivo .env criado com sucesso!" -ForegroundColor Green
    } else {
        Write-Host "❌ Arquivo env.example não encontrado!" -ForegroundColor Red
        exit 1
    }
}

# Verificar se as variáveis UID e GID estão definidas
if (-not (Select-String -Path .env -Pattern "UID=" -Quiet)) {
    Write-Host "📝 Adicionando UID e GID ao .env..." -ForegroundColor Yellow
    Add-Content .env ""
    Add-Content .env "# Docker user permissions"
    Add-Content .env "UID=1000"
    Add-Content .env "GID=1000"
}

# Parar containers existentes
Write-Host "🛑 Parando containers existentes..." -ForegroundColor Yellow
docker-compose down

# Remover imagens antigas para forçar reconstrução
Write-Host "🗑️  Removendo imagens antigas..." -ForegroundColor Yellow
docker rmi sigstore_app 2>$null

# Reconstruir a imagem com os novos argumentos
Write-Host "🔨 Reconstruindo imagem Docker..." -ForegroundColor Yellow
docker-compose build --no-cache

# Iniciar os serviços
Write-Host "▶️  Iniciando serviços..." -ForegroundColor Yellow
docker-compose up -d

# Aguardar um pouco para os serviços iniciarem
Write-Host "⏳ Aguardando serviços iniciarem..." -ForegroundColor Yellow
Start-Sleep -Seconds 15

# Verificar status dos containers
Write-Host "📊 Status dos containers:" -ForegroundColor Cyan
docker-compose ps

# Verificar logs se houver problemas
Write-Host "📋 Verificando logs do container app..." -ForegroundColor Cyan
docker-compose logs app --tail=20

Write-Host "✅ SigStore ERP iniciado com sucesso!" -ForegroundColor Green
Write-Host "🌐 Acesse: http://localhost:8080" -ForegroundColor Cyan
Write-Host "🗄️  MySQL: localhost:3306" -ForegroundColor Cyan
Write-Host "🔴 Redis: localhost:6379" -ForegroundColor Cyan 