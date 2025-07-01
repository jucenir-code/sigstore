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

## Arquivos Modificados

### docker-compose.yml
- ❌ Removida linha `version: '3.7'`
- ✅ Adicionado `user: "${UID:-1000}:${GID:-1000}"` no serviço app
- ✅ Adicionados argumentos `UID` e `GID` no build do serviço app

### Dockerfile
- ✅ Adicionados argumentos `ARG UID=1000` e `ARG GID=1000`
- ✅ Criado usuário dinâmico `appuser` com UID/GID específicos
- ✅ Configurado Git para evitar problemas de permissão
- ✅ Mudança de usuário de `www-data` para `appuser`

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

## Notas Importantes

- As variáveis UID e GID devem corresponder ao usuário do seu sistema para evitar problemas de permissão
- O usuário padrão é 1000:1000, mas pode ser alterado no arquivo `.env`
- A reconstrução da imagem é necessária após as mudanças no Dockerfile 