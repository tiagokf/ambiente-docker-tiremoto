@echo off
echo.
echo ========================================
echo  PARANDO AMBIENTE DE DESENVOLVIMENTO
echo ========================================
echo.

echo Parando todos os containers...
docker-compose down

echo.
echo Removendo containers orfaos...
docker-compose down --remove-orphans

echo.
echo Verificando containers restantes...
docker-compose ps

echo.
echo ========================================
echo  AMBIENTE PARADO COM SUCESSO!
echo ========================================
echo.
echo Para iniciar novamente: scripts\start.bat
echo ou execute: docker-compose up -d
echo.
pause
