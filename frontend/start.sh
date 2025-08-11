#!/bin/bash

echo "🚀 Iniciando aplicação Node.js..."

# Aguardar outros serviços estarem prontos
sleep 5

# Instalar dependências se necessário
if [ -f "package.json" ]; then
    echo "📦 Instalando dependências Node.js..."
    npm install
fi

# Iniciar aplicação Node.js
echo "🌐 Iniciando Node.js na porta 3000..."
node server.js &

# Manter container vivo
tail -f /dev/null
