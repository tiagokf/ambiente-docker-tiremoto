# 🐳 Ambiente de Desenvolvimento Docker

Ambiente completo de desenvolvimento com PHP, MySQL, Python, Node.js, Redis e outras ferramentas essenciais.

## 🚀 Serviços Incluídos

- **PHP 8.2** com Apache
- **MySQL 8.0** com phpMyAdmin
- **Python 3.11** com Flask
- **Node.js 18** com ferramentas frontend
- **Redis** para cache
- **MailHog** para testes de email

## 📋 Pré-requisitos

- Docker Desktop instalado
- Docker Compose
- Git (opcional)

## 🛠️ Instalação e Uso

### 1. Clonar o projeto

```bash
git clone git@github.com:tiagokf/ambiente-docker-tiremoto.git
cd ambiente-docker-tiremoto
```

**OU via HTTPS:**

```bash
git clone https://github.com/tiagokf/ambiente-docker-tiremoto.git
cd ambiente-docker-tiremoto
```

### 2. Configurar aliases PowerShell (Windows - Recomendado)

Para usar comandos como `python`, `node`, `php` diretamente no terminal:

```powershell
# Carregar aliases para a sessão atual (temporário)
. .\Docker-Aliases.ps1

# OU instalar permanentemente no perfil PowerShell (recomendado)
Copy-Item "Docker-Aliases.ps1" $PROFILE -Force
```

> **Nota**: Os aliases são carregados silenciosamente. Para ver comandos disponíveis: `Get-Command *docker*`

### 3. Subir os containers

```bash
docker-compose up -d
```

### 4. Iniciar aplicações web

```bash
# Executar script automatizado (Windows)
.\scripts\start-apps.bat

# OU manualmente
docker exec -d dev_python python /app/exemplo_flask.py
docker exec dev_node npm install
docker exec -d dev_node node /app/server.js
```

### 5. Verificar se os serviços estão rodando

```bash
docker-compose ps
```

## 🌐 Acessos

| Serviço              | URL                   | Porta |
| -------------------- | --------------------- | ----- |
| **Aplicação PHP**    | http://localhost      | 80    |
| **phpMyAdmin**       | http://localhost:8080 | 8080  |
| **Python Flask**     | http://localhost:5000 | 5000  |
| **Frontend Node.js** | http://localhost:3000 | 3000  |
| **MailHog**          | http://localhost:8025 | 8025  |

## 🗄️ Banco de Dados

### Credenciais MySQL

- **Host:** mysql (dentro do Docker) / localhost (externo)
- **Porta:** 3306
- **Usuário:** root
- **Senha:** root
- **Banco:** desenvolvimento

## 🐍 Python

### Executar aplicação Flask

```bash
docker-compose exec python python exemplo_flask.py
```

### Instalar dependências Python

```bash
docker-compose exec python pip install -r requirements.txt
```

### Jupyter Notebook

```bash
docker-compose exec python jupyter notebook --ip=0.0.0.0 --port=8888 --no-browser --allow-root
```

## 🟢 Node.js

### Instalar dependências

```bash
docker-compose exec node npm install
```

### Executar em modo desenvolvimento

```bash
docker-compose exec node npm run dev
```

## 📁 Estrutura de Diretórios

```
ambientedocker/
├── docker/                 # Configurações Docker
│   ├── php/                # Dockerfile e configs PHP
│   ├── python/             # Dockerfile Python
│   ├── mysql/              # Configurações MySQL
│   ├── redis/              # Configurações Redis
│   └── apache/             # Configurações Apache
├── scripts/                # Scripts de automação
│   ├── start.bat           # Inicia ambiente completo
│   ├── start-apps.bat      # Inicia aplicações web
│   └── diagnostico.bat     # Diagnóstico do ambiente
├── src/                    # Código PHP
├── python-projects/        # Projetos Python
├── frontend/               # Projetos Frontend
├── logs/                   # Logs dos serviços
├── docker-compose.yml      # Configuração principal
├── Docker-Aliases.ps1      # Aliases PowerShell
└── README.md              # Este arquivo
```

## 🔧 Comandos Úteis

### 🪟 Aliases PowerShell (Windows)

Com o arquivo `Docker-Aliases.ps1` carregado, você pode usar os comandos como se fossem instalações locais:

```powershell
# Python
python --version          # Python 3.11.13
pip install flask         # Instala no container
python script.py          # Executa no container

# Node.js
node --version            # v18.20.8
npm install express       # Instala no container
npm start                 # Executa no container

# PHP
php --version             # PHP 8.2.29
composer install         # Instala dependências

# Banco de dados
mysql -u root -p          # Conecta ao MySQL
redis-cli                 # Conecta ao Redis

# Utilitários
docker-status             # Status dos containers
docker-logs python        # Logs do serviço Python
docker-shell python       # Shell do container Python
```

### Docker

```bash
# Subir todos os serviços
docker-compose up -d

# Parar todos os serviços
docker-compose down

# Ver logs
docker-compose logs

# Reconstruir containers
docker-compose build --no-cache

# Acessar container
docker-compose exec web bash
docker-compose exec python bash
docker-compose exec node sh
```

### Desenvolvimento

```bash
# Acessar MySQL
docker-compose exec mysql mysql -u root -proot

# Acessar Redis
docker-compose exec redis redis-cli

# Ver logs específicos
docker-compose logs web
docker-compose logs mysql
docker-compose logs python
```

## 🐛 Solução de Problemas

### Portas em uso

Se alguma porta estiver em uso, edite o `docker-compose.yml` e altere as portas:

```yaml
ports:
  - "8080:80" # Muda porta 80 para 8080
```

### Permissões de arquivo

No Windows, certifique-se de que o Docker tem acesso à pasta:

```bash
# Dar permissões (se necessário)
chmod -R 755 src/
```

### Logs de erro

```bash
# Ver logs detalhados
docker-compose logs --tail=50 web
docker-compose logs --tail=50 mysql
```

## 📝 Desenvolvimento

### 🚀 Início Rápido com Aliases

Após carregar o `Docker-Aliases.ps1`, você pode trabalhar como se as ferramentas estivessem instaladas localmente:

```powershell
# 1. Subir o ambiente
docker-compose up -d

# 2. Carregar aliases (se não estiver no perfil)
. .\Docker-Aliases.ps1

# 3. Verificar se aliases estão funcionando
python --version    # Deve mostrar: Python 3.11.13
node --version      # Deve mostrar: v18.20.8
php --version       # Deve mostrar: PHP 8.2.29

# 4. Desenvolver normalmente
cd python-projects
python exemplo_flask.py

cd frontend
npm install
npm start
```

### PHP

- Arquivos em `src/`
- Configurações em `docker/php/`
- Logs em `logs/apache/`

### Python

- Projetos em `python-projects/`
- Requirements em `python-projects/requirements.txt`
- Logs em `logs/python/`

### Frontend

- Projetos em `frontend/`
- Package.json configurado
- Suporte a React, Vue.js, Vite

## 🔒 Segurança

⚠️ **IMPORTANTE:** Este ambiente é para desenvolvimento local apenas!

- Senhas padrão são simples
- Debug está habilitado
- Não usar em produção

## 📞 Suporte

Para problemas ou dúvidas:

1. Verifique os logs: `docker-compose logs`
2. Reinicie os serviços: `docker-compose restart`
3. Reconstrua se necessário: `docker-compose build --no-cache`

## 📄 Licença

Este projeto é livre para uso em desenvolvimento.
