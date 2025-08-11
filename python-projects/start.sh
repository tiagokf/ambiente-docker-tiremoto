#!/bin/bash

echo "🐍 Iniciando aplicação Flask..."

# Aguardar outros serviços estarem prontos
sleep 10

# Instalar dependências se necessário
if [ -f "requirements.txt" ]; then
    echo "📦 Instalando dependências Python..."
    pip install -r requirements.txt
fi

# Iniciar aplicação Flask
echo "🚀 Iniciando Flask na porta 5000..."
python exemplo_flask.py &

# Manter container vivo
tail -f /dev/null
