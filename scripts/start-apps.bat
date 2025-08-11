@echo off
echo.
echo ========================================
echo  INICIANDO APLICACOES WEB
echo ========================================
echo.

echo Verificando se os containers estao rodando...
docker-compose ps | findstr "Up" >nul
if %errorlevel% neq 0 (
    echo ERRO: Containers nao estao rodando!
    echo Execute primeiro: docker-compose up -d
    pause
    exit /b 1
)

echo.
echo Iniciando aplicacao Flask (Python)...
docker exec -d dev_python python /app/exemplo_flask.py
if %errorlevel% equ 0 (
    echo ✓ Flask iniciado com sucesso!
) else (
    echo ✗ Erro ao iniciar Flask
)

echo.
echo Verificando dependencias Node.js...
docker exec dev_node npm list express >nul 2>&1
if %errorlevel% neq 0 (
    echo Instalando dependencias Node.js...
    docker exec dev_node npm install
)

echo.
echo Iniciando aplicacao Node.js...
docker exec -d dev_node node /app/server.js
if %errorlevel% equ 0 (
    echo ✓ Node.js iniciado com sucesso!
) else (
    echo ✗ Erro ao iniciar Node.js
)

echo.
echo Aguardando servicos iniciarem (5 segundos)...
timeout /t 5 /nobreak >nul

echo.
echo Testando conectividade...
echo.

echo Testando Flask (localhost:5000)...
curl -s -o nul -w "Status: %%{http_code}" http://localhost:5000 2>nul
if %errorlevel% equ 0 (
    echo  ✓ Flask respondendo
) else (
    echo  ✗ Flask nao responde
)

echo.
echo Testando Node.js (localhost:3000)...
curl -s -o nul -w "Status: %%{http_code}" http://localhost:3000 2>nul
if %errorlevel% equ 0 (
    echo  ✓ Node.js respondendo
) else (
    echo  ✗ Node.js nao responde
)

echo.
echo ========================================
echo  APLICACOES INICIADAS!
echo ========================================
echo.
echo Acessos disponiveis:
echo.
echo  PHP Application:    http://localhost
echo  Python Flask:       http://localhost:5000
echo  Node.js Express:    http://localhost:3000
echo  phpMyAdmin:         http://localhost:8080
echo  MailHog:           http://localhost:8025
echo.
echo Deseja abrir as aplicacoes no navegador? (S/N)
set /p resposta=
if /i "%resposta%"=="S" (
    start http://localhost:5000
    start http://localhost:3000
)

echo.
echo Pressione qualquer tecla para sair...
pause >nul
