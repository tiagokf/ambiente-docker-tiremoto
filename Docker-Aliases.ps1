# ========================================
# PERFIL POWERSHELL - AMBIENTE DOCKER
# ========================================

Write-Host "🐳 Carregando aliases Docker..." -ForegroundColor Cyan

# Função para verificar se os containers estão rodando
function Test-DockerContainers {
    $containers = docker ps --format "table {{.Names}}" | Select-String -Pattern "dev_"
    return $containers.Count -gt 0
}

# ========================================
# ALIASES PYTHON
# ========================================

function python {
    if (-not (Test-DockerContainers)) {
        Write-Host "⚠️  Containers Docker não estão rodando!" -ForegroundColor Yellow
        Write-Host "Execute: docker-compose up -d" -ForegroundColor Green
        return
    }
    
    if ($args.Count -eq 0) {
        docker exec -it dev_python python
    } else {
        docker exec -it dev_python python @args
    }
}

function pip {
    if (-not (Test-DockerContainers)) {
        Write-Host "⚠️  Containers Docker não estão rodando!" -ForegroundColor Yellow
        Write-Host "Execute: docker-compose up -d" -ForegroundColor Green
        return
    }
    
    docker exec -it dev_python pip @args
}

function python3 {
    python @args
}

function pip3 {
    pip @args
}

# ========================================
# ALIASES NODE.JS
# ========================================

function node {
    if (-not (Test-DockerContainers)) {
        Write-Host "⚠️  Containers Docker não estão rodando!" -ForegroundColor Yellow
        Write-Host "Execute: docker-compose up -d" -ForegroundColor Green
        return
    }
    
    if ($args.Count -eq 0) {
        docker exec -it dev_node node
    } else {
        docker exec -it dev_node node @args
    }
}

function npm {
    if (-not (Test-DockerContainers)) {
        Write-Host "⚠️  Containers Docker não estão rodando!" -ForegroundColor Yellow
        Write-Host "Execute: docker-compose up -d" -ForegroundColor Green
        return
    }
    
    docker exec -it dev_node npm @args
}

function npx {
    if (-not (Test-DockerContainers)) {
        Write-Host "⚠️  Containers Docker não estão rodando!" -ForegroundColor Yellow
        Write-Host "Execute: docker-compose up -d" -ForegroundColor Green
        return
    }
    
    docker exec -it dev_node npx @args
}

# ========================================
# ALIASES PHP
# ========================================

function php {
    if (-not (Test-DockerContainers)) {
        Write-Host "⚠️  Containers Docker não estão rodando!" -ForegroundColor Yellow
        Write-Host "Execute: docker-compose up -d" -ForegroundColor Green
        return
    }
    
    if ($args.Count -eq 0) {
        docker exec -it dev_web php
    } else {
        docker exec -it dev_web php @args
    }
}

function composer {
    if (-not (Test-DockerContainers)) {
        Write-Host "⚠️  Containers Docker não estão rodando!" -ForegroundColor Yellow
        Write-Host "Execute: docker-compose up -d" -ForegroundColor Green
        return
    }
    
    docker exec -it dev_web composer @args
}

# ========================================
# ALIASES MYSQL
# ========================================

function mysql {
    if (-not (Test-DockerContainers)) {
        Write-Host "⚠️  Containers Docker não estão rodando!" -ForegroundColor Yellow
        Write-Host "Execute: docker-compose up -d" -ForegroundColor Green
        return
    }
    
    docker exec -it dev_mysql mysql @args
}

function mysqldump {
    if (-not (Test-DockerContainers)) {
        Write-Host "⚠️  Containers Docker não estão rodando!" -ForegroundColor Yellow
        Write-Host "Execute: docker-compose up -d" -ForegroundColor Green
        return
    }
    
    docker exec -it dev_mysql mysqldump @args
}

# ========================================
# ALIASES REDIS
# ========================================

function redis-cli {
    if (-not (Test-DockerContainers)) {
        Write-Host "⚠️  Containers Docker não estão rodando!" -ForegroundColor Yellow
        Write-Host "Execute: docker-compose up -d" -ForegroundColor Green
        return
    }
    
    docker exec -it dev_redis redis-cli @args
}

# ========================================
# FUNÇÕES AUXILIARES
# ========================================

function docker-status {
    Write-Host "🐳 Status dos Containers Docker:" -ForegroundColor Cyan
    docker-compose ps
}

function docker-logs {
    param([string]$service)
    if ($service) {
        docker-compose logs -f $service
    } else {
        Write-Host "Uso: docker-logs <serviço>" -ForegroundColor Yellow
        Write-Host "Serviços disponíveis: web, mysql, python, node, redis, phpmyadmin, mailhog" -ForegroundColor Green
    }
}

function docker-shell {
    param([string]$service)
    switch ($service) {
        "python" { docker exec -it dev_python bash }
        "node" { docker exec -it dev_node sh }
        "php" { docker exec -it dev_web bash }
        "mysql" { docker exec -it dev_mysql bash }
        "redis" { docker exec -it dev_redis sh }
        default {
            Write-Host "Uso: docker-shell <serviço>" -ForegroundColor Yellow
            Write-Host "Serviços disponíveis: python, node, php, mysql, redis" -ForegroundColor Green
        }
    }
}

# ========================================
# MENSAGEM DE BOAS-VINDAS
# ========================================

Write-Host ""
Write-Host "✅ Aliases Docker carregados com sucesso!" -ForegroundColor Green
Write-Host "📝 Comandos disponíveis:" -ForegroundColor White
Write-Host "   • python, pip, python3, pip3" -ForegroundColor Yellow
Write-Host "   • node, npm, npx" -ForegroundColor Yellow  
Write-Host "   • php, composer" -ForegroundColor Yellow
Write-Host "   • mysql, mysqldump" -ForegroundColor Yellow
Write-Host "   • redis-cli" -ForegroundColor Yellow
Write-Host "   • docker-status, docker-logs, docker-shell" -ForegroundColor Yellow
Write-Host ""
