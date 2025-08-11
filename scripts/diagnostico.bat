@echo off
echo.
echo ========================================
echo  DIAGNOSTICO DO AMBIENTE DOCKER
echo ========================================
echo.

echo 1. Verificando Docker...
docker --version
if %errorlevel% neq 0 (
    echo ERRO: Docker nao encontrado!
    goto :fim
)

echo.
echo 2. Verificando Docker Compose...
docker-compose --version
if %errorlevel% neq 0 (
    echo ERRO: Docker Compose nao encontrado!
    goto :fim
)

echo.
echo 3. Status dos containers...
docker-compose ps

echo.
echo 4. Verificando portas em uso...
netstat -an | findstr ":80 "
netstat -an | findstr ":3306 "
netstat -an | findstr ":8080 "

echo.
echo 5. Logs do container web (ultimas 20 linhas)...
docker-compose logs --tail=20 web

echo.
echo 6. Testando conectividade...
echo Testando localhost:80...
curl -I http://localhost 2>nul
if %errorlevel% neq 0 (
    echo ERRO: Nao foi possivel conectar em localhost:80
) else (
    echo OK: localhost:80 respondendo
)

echo.
echo 7. Verificando se o container web esta rodando...
docker-compose exec web ps aux 2>nul
if %errorlevel% neq 0 (
    echo ERRO: Container web nao esta rodando ou nao responde
)

echo.
echo 8. Verificando arquivos PHP...
if exist "src\index.php" (
    echo OK: src\index.php existe
) else (
    echo ERRO: src\index.php nao encontrado
)

echo.
echo ========================================
echo  DIAGNOSTICO CONCLUIDO
echo ========================================

:fim
echo.
pause
