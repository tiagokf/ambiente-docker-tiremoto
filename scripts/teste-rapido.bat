@echo off
echo.
echo ========================================
echo  TESTE RAPIDO DO AMBIENTE
echo ========================================
echo.

echo 1. Verificando se os containers estao rodando...
docker-compose ps

echo.
echo 2. Verificando logs do container web...
docker-compose logs --tail=10 web

echo.
echo 3. Testando se o Apache esta respondendo dentro do container...
docker-compose exec web curl -I http://localhost 2>nul

echo.
echo 4. Verificando arquivos na pasta src...
dir src

echo.
echo 5. Testando conectividade externa...
curl -I http://localhost 2>nul
if %errorlevel% neq 0 (
    echo ERRO: Nao conseguiu conectar em localhost:80
    echo.
    echo Verificando se a porta 80 esta em uso por outro processo...
    netstat -an | findstr ":80"
    echo.
    echo Tentando reiniciar apenas o container web...
    docker-compose restart web
    echo Aguardando 10 segundos...
    timeout /t 10 /nobreak >nul
    echo Testando novamente...
    curl -I http://localhost 2>nul
)

echo.
echo ========================================
echo  TESTE CONCLUIDO
echo ========================================
pause
