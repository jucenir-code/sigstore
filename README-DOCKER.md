# SigStore ERP - Docker Setup

## Visão Geral

Este projeto usa Docker Compose para configurar um ambiente completo de desenvolvimento com:
- **PHP 8.3** com Laravel
- **Nginx** como servidor web
- **MySQL 5.7** como banco de dados
- **Redis** para cache
- **PHPMyAdmin** para gerenciamento do banco

## Portas Utilizadas

- **8989** - Aplicação Laravel
- **8080** - PHPMyAdmin
- **3388** - MySQL
- **6379** - Redis

## Início Rápido

### Linux/macOS
```bash
# Dar permissão de execução aos scripts
chmod +x start-docker.sh fix-permissions.sh install-dependencies.sh

# Iniciar o ambiente
./start-docker.sh

# Instalar dependências (após o primeiro start)
./install-dependencies.sh
```

### Windows (PowerShell)
```powershell
# Iniciar o ambiente
.\start-docker.ps1
```

### Comandos Manuais
```bash
# Parar containers
docker-compose down

# Reconstruir e iniciar
docker-compose build --no-cache
docker-compose up -d

# Corrigir permissões
docker-compose exec app sudo chown -R jucenir:jucenir /var/www
docker-compose exec app sudo chmod -R 775 /var/www
docker-compose exec app git config --global --add safe.directory /var/www

# Instalar dependências
docker-compose exec app composer install
```

## Solução de Problemas

### Erro de Permissão do Git
```
fatal: detected dubious ownership in repository at '/var/www'
```

**Solução:**
```bash
./fix-permissions.sh
```

### Erro de Dependências do Composer
```
/var/www/vendor does not exist and could not be created
```

**Solução:**
```bash
./install-dependencies.sh
```

### Verificar Status dos Containers
```bash
docker-compose ps
```

### Ver Logs
```bash
# Logs do app
docker-compose logs app

# Logs de todos os serviços
docker-compose logs
```

## Acesso aos Serviços

- **Aplicação Laravel:** http://localhost:8989
- **PHPMyAdmin:** http://localhost:8080
  - Usuário: `username`
  - Senha: `userpass`
- **MySQL:** localhost:3388
- **Redis:** localhost:6379

## Estrutura do Projeto

```
sigstore/
├── docker-compose.yml          # Configuração do Docker Compose
├── Dockerfile                  # Imagem PHP personalizada
├── start-docker.sh            # Script de inicialização (Linux/macOS)
├── start-docker.ps1           # Script de inicialização (Windows)
├── fix-permissions.sh         # Script para corrigir permissões
├── install-dependencies.sh    # Script para instalar dependências
├── docker/
│   ├── nginx/                 # Configurações do Nginx
│   └── php/                   # Configurações do PHP
└── .docker/
    └── mysql/
        └── dbdata/            # Dados do MySQL
```

## Configuração do Ambiente

### Variáveis de Ambiente (.env)
```env
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
```

## Comandos Úteis

### Laravel Artisan
```bash
# Gerar chave da aplicação
docker-compose exec app php artisan key:generate

# Limpar cache
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan view:clear

# Executar migrações
docker-compose exec app php artisan migrate

# Executar seeders
docker-compose exec app php artisan db:seed
```

### Composer
```bash
# Instalar dependências
docker-compose exec app composer install

# Atualizar dependências
docker-compose exec app composer update

# Instalar pacote específico
docker-compose exec app composer require package-name
```

### Acesso ao Container
```bash
# Acessar container do app
docker-compose exec app bash

# Verificar usuário atual
docker-compose exec app whoami

# Verificar permissões
docker-compose exec app ls -la /var/www
```

## Limpeza

### Parar e Remover Containers
```bash
docker-compose down
```

### Remover Volumes (CUIDADO: perde dados do banco)
```bash
docker-compose down -v
```

### Limpeza Completa
```bash
docker-compose down -v
docker system prune -f
docker volume prune -f
```

## Troubleshooting

### Container não inicia
1. Verificar se as portas estão disponíveis
2. Verificar logs: `docker-compose logs app`
3. Verificar se o arquivo `.env` existe

### Problemas de Permissão
1. Executar: `./fix-permissions.sh`
2. Verificar se o usuário `jucenir` tem permissões corretas

### Problemas de Rede
1. Verificar se o Docker está rodando
2. Verificar se as portas não estão em uso
3. Reiniciar o Docker se necessário

### Problemas de Banco de Dados
1. Verificar se o MySQL está rodando: `docker-compose ps db`
2. Verificar logs do MySQL: `docker-compose logs db`
3. Verificar configurações no `.env`

## Suporte

Para problemas específicos, verifique:
1. Logs dos containers
2. Configuração do `.env`
3. Permissões dos arquivos
4. Versão do Docker e Docker Compose 