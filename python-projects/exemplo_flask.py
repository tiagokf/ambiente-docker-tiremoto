#!/usr/bin/env python3
"""
Exemplo de aplicação Flask para o ambiente de desenvolvimento
"""

from flask import Flask, jsonify, render_template_string
import mysql.connector
import redis
import os
from datetime import datetime

app = Flask(__name__)

# Configurações do banco de dados
DB_CONFIG = {
    'host': 'mysql',
    'user': 'root',
    'password': 'root',
    'database': 'desenvolvimento',
    'charset': 'utf8mb4'
}

# Template HTML simples
HTML_TEMPLATE = """
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Python Flask - Ambiente de Desenvolvimento</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; text-align: center; }
        .status { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .data { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🐍 Python Flask Application</h1>
        
        <div class="info">
            <strong>Servidor:</strong> Flask rodando na porta 5000<br>
            <strong>Hora atual:</strong> {{ current_time }}<br>
            <strong>Status:</strong> Aplicação funcionando corretamente!
        </div>

        <h3>📊 Teste de Conexões:</h3>
        
        <div class="status {{ mysql_status }}">
            <strong>MySQL:</strong> {{ mysql_message }}
        </div>
        
        <div class="status {{ redis_status }}">
            <strong>Redis:</strong> {{ redis_message }}
        </div>

        {% if usuarios %}
        <h3>👥 Usuários do Banco de Dados:</h3>
        <div class="data">
            {% for usuario in usuarios %}
            <p><strong>{{ usuario[1] }}</strong> - {{ usuario[2] }}</p>
            {% endfor %}
        </div>
        {% endif %}

        <h3>🔗 Endpoints Disponíveis:</h3>
        <div class="data">
            <p><strong>GET /</strong> - Esta página</p>
            <p><strong>GET /api/status</strong> - Status da aplicação em JSON</p>
            <p><strong>GET /api/usuarios</strong> - Lista de usuários em JSON</p>
            <p><strong>GET /api/test-redis</strong> - Teste do Redis</p>
        </div>
    </div>
</body>
</html>
"""

def test_mysql():
    """Testa a conexão com MySQL"""
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        cursor.execute("SELECT COUNT(*) FROM usuarios")
        count = cursor.fetchone()[0]
        cursor.close()
        conn.close()
        return {'status': 'success', 'message': f'Conectado! {count} usuários encontrados.'}
    except Exception as e:
        return {'status': 'error', 'message': f'Erro: {str(e)}'}

def test_redis():
    """Testa a conexão com Redis"""
    try:
        r = redis.Redis(host='redis', port=6379, decode_responses=True)
        r.set('python_test', f'Teste Python - {datetime.now()}')
        result = r.get('python_test')
        return {'status': 'success', 'message': f'Conectado! Valor: {result}'}
    except Exception as e:
        return {'status': 'error', 'message': f'Erro: {str(e)}'}

def get_usuarios():
    """Busca usuários do banco de dados"""
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        cursor.execute("SELECT id, nome, email FROM usuarios LIMIT 10")
        usuarios = cursor.fetchall()
        cursor.close()
        conn.close()
        return usuarios
    except Exception as e:
        print(f"Erro ao buscar usuários: {e}")
        return []

@app.route('/')
def index():
    """Página principal"""
    mysql_test = test_mysql()
    redis_test = test_redis()
    usuarios = get_usuarios()
    
    return render_template_string(HTML_TEMPLATE,
        current_time=datetime.now().strftime('%Y-%m-%d %H:%M:%S'),
        mysql_status=mysql_test['status'],
        mysql_message=mysql_test['message'],
        redis_status=redis_test['status'],
        redis_message=redis_test['message'],
        usuarios=usuarios
    )

@app.route('/api/status')
def api_status():
    """API de status"""
    mysql_test = test_mysql()
    redis_test = test_redis()
    
    return jsonify({
        'status': 'running',
        'timestamp': datetime.now().isoformat(),
        'services': {
            'mysql': mysql_test,
            'redis': redis_test
        }
    })

@app.route('/api/usuarios')
def api_usuarios():
    """API de usuários"""
    usuarios = get_usuarios()
    return jsonify({
        'usuarios': [{'id': u[0], 'nome': u[1], 'email': u[2]} for u in usuarios]
    })

@app.route('/api/test-redis')
def api_test_redis():
    """API de teste do Redis"""
    result = test_redis()
    return jsonify(result)

if __name__ == '__main__':
    print("🐍 Iniciando aplicação Flask...")
    print("📍 Acesse: http://localhost:5000")
    app.run(host='0.0.0.0', port=5000, debug=True)
