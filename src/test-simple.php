<?php
// Teste simples da função Docker

function getContainerDescription($name, $image) {
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

function testDockerStatus() {
    $status = [];
    
    // Comando simples
    $output = shell_exec('docker ps --format "{{.Names}} {{.Status}}" 2>&1');
    
    echo "<h3>Comando executado:</h3>";
    echo "<pre>docker ps --format \"{{.Names}} {{.Status}}\"</pre>";
    
    echo "<h3>Saída bruta:</h3>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    if ($output && !strpos($output, 'error')) {
        $lines = explode("\n", trim($output));
        
        echo "<h3>Linhas processadas:</h3>";
        foreach ($lines as $line) {
            if (trim($line)) {
                $parts = explode(' ', trim($line), 2);
                if (count($parts) >= 2) {
                    $name = $parts[0];
                    $statusInfo = $parts[1];
                    $description = getContainerDescription($name, '');
                    $isRunning = strpos($statusInfo, 'Up') !== false;
                    
                    echo "<div style='border: 1px solid #0EE57F; margin: 5px; padding: 10px;'>";
                    echo "<strong>Nome:</strong> $name<br>";
                    echo "<strong>Status:</strong> $statusInfo<br>";
                    echo "<strong>Descrição:</strong> $description<br>";
                    echo "<strong>Rodando:</strong> " . ($isRunning ? 'SIM' : 'NÃO') . "<br>";
                    echo "</div>";
                    
                    $status[$name] = [
                        'name' => $description,
                        'status' => $isRunning ? 'running' : 'stopped',
                        'uptime' => $isRunning ? $statusInfo : 'Parado'
                    ];
                }
            }
        }
    }
    
    return $status;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Teste Docker Simples</title>
    <style>
        body { background: #111424; color: white; font-family: Arial; padding: 20px; }
        h3 { color: #0EE57F; }
        pre { background: #1a1f3a; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🧪 Teste Docker Simples</h1>
    <?php 
    $result = testDockerStatus();
    echo "<h3>Resultado final:</h3>";
    echo "<pre>" . print_r($result, true) . "</pre>";
    ?>
</body>
</html>
