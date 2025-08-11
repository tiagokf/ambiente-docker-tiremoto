<?php
// Função corrigida para Docker
function getDockerContainerStatusFixed() {
    $status = [];
    
    // Verificar se o Docker está disponível
    $dockerCheck = shell_exec('docker --version 2>&1');
    if (strpos($dockerCheck, 'Docker version') === false) {
        return [
            'docker_unavailable' => [
                'name' => 'Docker não disponível',
                'status' => 'error',
                'uptime' => 'Docker não está instalado ou não está rodando'
            ]
        ];
    }
    
    // Comando sem espaços extras
    $cmd = 'docker ps -a --format "{{.Names}}\t{{.Status}}" 2>&1';
    $output = shell_exec($cmd);
    
    if ($output && !strpos($output, 'error')) {
        $lines = explode("\n", trim($output));
        
        foreach ($lines as $line) {
            if (trim($line)) {
                $parts = explode("\t", trim($line));
                if (count($parts) >= 2) {
                    $name = $parts[0];
                    $statusInfo = $parts[1];
                    
                    // Determinar descrição
                    $description = getContainerDescriptionFixed($name);
                    
                    // Determinar se está rodando
                    $isRunning = strpos($statusInfo, 'Up') !== false;
                    
                    $status[$name] = [
                        'name' => $description,
                        'status' => $isRunning ? 'running' : 'stopped',
                        'uptime' => $isRunning ? $statusInfo : 'Parado'
                    ];
                }
            }
        }
    }
    
    // Se não encontrou containers
    if (empty($status)) {
        $status['no_containers'] = [
            'name' => 'Nenhum container encontrado',
            'status' => 'stopped',
            'uptime' => 'Execute docker-compose up para iniciar os containers'
        ];
    }
    
    return $status;
}

function getContainerDescriptionFixed($name) {
    $nameMapping = [
        'dev_web' => '🌐 Web Server (Apache/PHP)',
        'dev_mysql' => '🗄️ MySQL Database',
        'dev_redis' => '🔴 Redis Cache',
        'dev_python' => '🐍 Python Environment',
        'dev_node' => '📦 Node.js Environment',
        'dev_phpmyadmin' => '⚡ phpMyAdmin',
        'dev_mailhog' => '📧 MailHog'
    ];
    
    if (isset($nameMapping[$name])) {
        return $nameMapping[$name];
    }
    
    return '📦 ' . ucfirst(str_replace(['dev_', '_'], ['', ' '], $name));
}

// Teste
$result = getDockerContainerStatusFixed();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Docker Fix Test</title>
    <style>
        body { background: #111424; color: white; font-family: Arial; padding: 20px; }
        .container { border: 1px solid #0EE57F; margin: 10px; padding: 15px; border-radius: 5px; }
        .running { border-color: #0EE57F; background: rgba(14, 229, 127, 0.1); }
        .stopped { border-color: #ff5252; background: rgba(255, 82, 82, 0.1); }
        .error { border-color: #ff5252; background: rgba(255, 82, 82, 0.2); }
    </style>
</head>
<body>
    <h1>🐳 Teste Docker Corrigido</h1>
    
    <?php foreach ($result as $key => $container): ?>
        <div class="container <?= $container['status'] ?>">
            <h3><?= htmlspecialchars($container['name']) ?></h3>
            <p><strong>Status:</strong> <?= ucfirst($container['status']) ?></p>
            <p><strong>Uptime:</strong> <?= htmlspecialchars($container['uptime']) ?></p>
        </div>
    <?php endforeach; ?>
    
    <h2>Debug Info:</h2>
    <pre><?= print_r($result, true) ?></pre>
</body>
</html>
