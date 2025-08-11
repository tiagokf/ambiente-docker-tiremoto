const express = require('express');
const cors = require('cors');
const path = require('path');
const fs = require('fs');

const app = express();
const PORT = 3000;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.static(path.join(__dirname)));

// Rota principal
app.get('/', (req, res) => {
    const htmlContent = `
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Node.js - Ambiente de Desenvolvimento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .container { margin-top: 50px; }
        .card { box-shadow: 0 10px 30px rgba(0,0,0,0.2); border: none; }
        .card-header { background: linear-gradient(45deg, #28a745, #20c997); color: white; }
        .status-badge { font-size: 0.9em; }
        .endpoint { background: #f8f9fa; padding: 10px; border-radius: 5px; margin: 5px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header text-center">
                        <h1><i class="fas fa-server"></i> 🚀 Node.js Express Server</h1>
                        <p class="mb-0">Ambiente de Desenvolvimento Docker</p>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5><i class="fas fa-info-circle"></i> 📊 Informações do Servidor</h5>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between">
                                        <strong>Porta:</strong> 
                                        <span class="badge bg-primary">${PORT}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <strong>Node.js:</strong> 
                                        <span class="badge bg-success">${process.version}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <strong>Status:</strong> 
                                        <span class="badge bg-success">🟢 Online</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <strong>Uptime:</strong> 
                                        <span class="badge bg-info">${Math.floor(process.uptime())}s</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5><i class="fas fa-link"></i> 🔗 Endpoints Disponíveis</h5>
                                <div class="endpoint">
                                    <strong>GET /</strong> - Esta página principal
                                </div>
                                <div class="endpoint">
                                    <strong>GET /api/status</strong> - Status do servidor em JSON
                                </div>
                                <div class="endpoint">
                                    <strong>GET /api/info</strong> - Informações do sistema
                                </div>
                                <div class="endpoint">
                                    <strong>GET /api/test</strong> - Endpoint de teste
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5><i class="fas fa-network-wired"></i> 🌐 Outros Serviços</h5>
                                <div class="row">
                                    <div class="col-md-3">
                                        <a href="http://localhost" class="btn btn-outline-primary btn-sm w-100" target="_blank">
                                            PHP/Apache
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="http://localhost:5000" class="btn btn-outline-success btn-sm w-100" target="_blank">
                                            Python Flask
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="http://localhost:8080" class="btn btn-outline-info btn-sm w-100" target="_blank">
                                            phpMyAdmin
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="http://localhost:8025" class="btn btn-outline-warning btn-sm w-100" target="_blank">
                                            MailHog
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-center text-muted">
                        <small>Ambiente Docker - Atualizado em: ${new Date().toLocaleString('pt-BR')}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>`;
    
    res.send(htmlContent);
});

// API Routes
app.get('/api/status', (req, res) => {
    res.json({
        status: 'online',
        timestamp: new Date().toISOString(),
        uptime: process.uptime(),
        version: process.version,
        port: PORT,
        environment: 'development'
    });
});

app.get('/api/info', (req, res) => {
    res.json({
        server: 'Node.js Express',
        version: process.version,
        platform: process.platform,
        architecture: process.arch,
        memory: process.memoryUsage(),
        uptime: process.uptime(),
        pid: process.pid
    });
});

app.get('/api/test', (req, res) => {
    res.json({
        message: 'Teste realizado com sucesso!',
        timestamp: new Date().toISOString(),
        random: Math.floor(Math.random() * 1000)
    });
});

// Iniciar servidor
app.listen(PORT, '0.0.0.0', () => {
    console.log(`🚀 Servidor Node.js rodando na porta ${PORT}`);
    console.log(`📍 Acesse: http://localhost:${PORT}`);
    console.log(`🔧 Ambiente: desenvolvimento`);
});
