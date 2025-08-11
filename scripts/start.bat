@echo off
echo.
echo ========================================
echo  AMBIENTE DE DESENVOLVIMENTO DOCKER
echo ========================================
echo.

echo Verificando se o Docker esta rodando...
docker --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERRO: Docker nao encontrado ou nao esta rodando!
    echo Por favor, inicie o Docker Desktop primeiro.
    pause
    exit /b 1
)

echo Docker encontrado! Iniciando ambiente...
echo.

echo Verificando se as pastas necessarias existem...
if not exist "src" mkdir src
if not exist "logs" mkdir logs
if not exist "logs\apache" mkdir logs\apache
if not exist "logs\mysql" mkdir logs\mysql
if not exist "logs\python" mkdir logs\python

echo.
echo Parando containers existentes (se houver)...
docker-compose down

echo.
echo Construindo e iniciando containers...
docker-compose up -d --build

echo.
echo Aguardando servicos iniciarem (30 segundos)...
timeout /t 30 /nobreak >nul

echo.
echo Verificando status dos containers...
docker-compose ps

echo.
echo Testando conectividade dos servicos...
echo.

echo Testando PHP (localhost:80)...
curl -s -o nul -w "Status: %%{http_code}" http://localhost 2>nul
if %errorlevel% equ 0 (
    echo  - OK: PHP respondendo
) else (
    echo  - ERRO: PHP nao responde
    echo  - Verificando logs do Apache...
    docker-compose logs --tail=5 web
)

echo.
echo Testando phpMyAdmin (localhost:8080)...
curl -s -o nul -w "Status: %%{http_code}" http://localhost:8080 2>nul
if %errorlevel% equ 0 (
    echo  - OK: phpMyAdmin respondendo
) else (
    echo  - ERRO: phpMyAdmin nao responde
)

echo.
echo ========================================
echo  AMBIENTE INICIADO!
echo ========================================
echo.
echo Acessos disponiveis:
echo.
echo  PHP Application:    http://localhost
echo  phpMyAdmin:         http://localhost:8080
echo  Python Flask:       http://localhost:5000
echo  Frontend Node.js:   http://localhost:3000
echo  MailHog:           http://localhost:8025
echo.
echo Credenciais MySQL:
echo  Usuario: dev_user
echo  Senha:   dev_pass
echo  Banco:   desenvolvimento
echo.
echo Comandos uteis:
echo  Parar ambiente:     docker-compose down
echo  Ver logs:           docker-compose logs
echo  Diagnostico:        scripts\diagnostico.bat
echo.
echo Deseja abrir o navegador? (S/N)
set /p resposta=
if /i "%resposta%"=="S" (
    start http://localhost
    start http://localhost:8080
)

echo.
echo Ambiente rodando! Pressione qualquer tecla para sair...
pause >nul
