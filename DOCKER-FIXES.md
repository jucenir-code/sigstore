# Correções do Docker Compose - SigStore ERP

## Problemas Resolvidos

### 1. Aviso de Versão Obsoleta
**Problema:** `WARN[0000] /opt/sigstore/docker-compose.prod.yml: the attribute 'version' is obsolete`

**Solução:** Removida a linha `version: '3.7'` do arquivo `docker-compose.yml`, pois é obsoleta nas versões mais recentes do Docker Compose.

### 2. Erro de Permissão do .gitconfig
**Problema:** `error: could not lock config file /var/www/.gitconfig: Permission denied`

**Solução:** 
- Adicionadas variáveis de ambiente `UID` e `GID` no arquivo `.env`
- Atualizado o `Dockerfile` para criar um usuário dinâmico com os UID/GID especificados
- Configurado o Git para evitar problemas de permissão

### 3. Erro de Permissão do chown
**Problema:** `chown: changing ownership of '/var/www/...': Operation not permitted`

**Solução:**
- Instalado `sudo` no container
- Criado usuário `appuser` com privilégios sudo
- Criado script de entrada (`docker-entrypoint.sh`) que corrige permissões automaticamente
- Removido `chown` do Dockerfile para evitar problemas durante o build
- Permissões são corrigidas após os arquivos serem montados no container

## Arquivos Modificados

### docker-compose.yml
- ❌ Removida linha `version: '3.7'`
- ✅ Adicionado `user: "${UID:-1000}:${GID:-1000}"` no serviço app
- ✅ Adicionados argumentos `UID` e `GID` no build do serviço app

### Dockerfile
- ✅ Adicionados argumentos `ARG UID=1000` e `ARG GID=1000`
- ✅ Criado usuário dinâmico `appuser` com UID/GID específicos
- ✅ Instalado `sudo` e configurado privilégios para `appuser`
- ✅ Configurado Git para evitar problemas de permissão
- ✅ Adicionado script de entrada para corrigir permissões automaticamente
- ✅ Mudança de usuário de `www-data` para `appuser`

### docker-entrypoint.sh (NOVO)
- ✅ Script que corrige permissões automaticamente quando o container inicia
- ✅ Executa `chown` e `chmod` após os arquivos serem montados
- ✅ Usa `sudo` para executar comandos que requerem privilégios

### .env
- ✅ Adicionadas variáveis:
  ```
  # Docker user permissions
  UID=1000
  GID=1000
  ```

## Scripts de Inicialização

### Para Linux/macOS
```bash
chmod +x start-docker.sh
./start-docker.sh
```

### Para Windows (PowerShell)
```powershell
.\start-docker.ps1
```

### Script de Correção de Permissões (Manual)
```bash
chmod +x fix-permissions.sh
./fix-permissions.sh
```

## Como Usar

1. **Certifique-se de que o arquivo `.env` existe** e contém as variáveis UID e GID
2. **Execute um dos scripts de inicialização** ou use diretamente:
   ```bash
   docker-compose down
   docker-compose build --no-cache
   docker-compose up -d
   ```

## Verificação

Após a inicialização, verifique se os containers estão rodando:
```bash
docker-compose ps
```

Você deve ver todos os serviços (app, nginx, db, redis) com status "Up".

## Acesso aos Serviços

- **Aplicação Web:** http://localhost:8080
- **MySQL:** localhost:3306
- **Redis:** localhost:6379

## Solução de Problemas

### Se ainda houver problemas de permissão:
1. **Execute o script de correção manual:**
   ```bash
   ./fix-permissions.sh
   ```

2. **Ou execute manualmente:**
   ```bash
   docker-compose exec app sudo chown -R appuser:appuser /var/www
   docker-compose exec app sudo chmod -R 775 /var/www
   ```

3. **Remova completamente as imagens antigas:**
   ```bash
   docker-compose down
   docker rmi $(docker images -q sigstore_app)
   docker system prune -f
   docker-compose build --no-cache
   docker-compose up -d
   ```

4. **Verifique os logs:**
   ```bash
   docker-compose logs app
   ```

### Verificar permissões dentro do container:
```bash
docker-compose exec app ls -la /var/www
docker-compose exec app whoami
```

## Notas Importantes

- As variáveis UID e GID devem corresponder ao usuário do seu sistema para evitar problemas de permissão
- O usuário padrão é 1000:1000, mas pode ser alterado no arquivo `.env`
- A reconstrução da imagem é necessária após as mudanças no Dockerfile
- O usuário `appuser` tem privilégios sudo para executar comandos que requerem root
- O script de entrada corrige automaticamente as permissões quando o container inicia
- Todos os arquivos são propriedade do usuário `appuser` dentro do container 