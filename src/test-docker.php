<?php
/**
 * Teste de Debug para Docker
 */

// Função de teste para containers
function testDockerContainers() {
    echo "<h2>🐳 Teste Docker Debug</h2>";
    
    // Teste 1: Verificar se Docker está disponível
    echo "<h3>1. Verificação do Docker:</h3>";
    $dockerCheck = shell_exec('docker --version 2>&1');
    echo "<pre>Comando: docker --version</pre>";
    echo "<pre>Resultado: " . htmlspecialchars($dockerCheck) . "</pre>";
    
    // Teste 2: Listar containers rodando
    echo "<h3>2. Containers rodando:</h3>";
    $runningContainers = shell_exec('docker ps --format "{{.Names}}\t{{.Status}}\t{{.Image}}" 2>&1');
    echo "<pre>Comando: docker ps --format \"{{.Names}}\\t{{.Status}}\\t{{.Image}}\"</pre>";
    echo "<pre>Resultado:\n" . htmlspecialchars($runningContainers) . "</pre>";
    
    // Teste 3: Listar todos os containers
    echo "<h3>3. Todos os containers:</h3>";
    $allContainers = shell_exec('docker ps -a --format "{{.Names}}\t{{.Status}}\t{{.Image}}" 2>&1');
    echo "<pre>Comando: docker ps -a --format \"{{.Names}}\\t{{.Status}}\\t{{.Image}}\"</pre>";
    echo "<pre>Resultado:\n" . htmlspecialchars($allContainers) . "</pre>";
    
    // Teste 4: Processar dados
    echo "<h3>4. Processamento dos dados:</h3>";
    if ($allContainers && !strpos($allContainers, 'error')) {
        $lines = explode("\n", trim($allContainers));
        echo "<p>Total de linhas encontradas: " . count($lines) . "</p>";
        
        foreach ($lines as $index => $line) {
            if (trim($line)) {
                $parts = explode("\t", trim($line));
                echo "<div style='border: 1px solid #ccc; margin: 5px; padding: 10px;'>";
                echo "<strong>Linha " . ($index + 1) . ":</strong> " . htmlspecialchars($line) . "<br>";
                echo "<strong>Partes:</strong> " . count($parts) . "<br>";
                if (count($parts) >= 2) {
                    echo "<strong>Nome:</strong> " . htmlspecialchars($parts[0]) . "<br>";
                    echo "<strong>Status:</strong> " . htmlspecialchars($parts[1]) . "<br>";
                    echo "<strong>Imagem:</strong> " . (isset($parts[2]) ? htmlspecialchars($parts[2]) : 'N/A') . "<br>";
                    echo "<strong>Rodando:</strong> " . (strpos($parts[1], 'Up') !== false ? 'SIM' : 'NÃO') . "<br>";
                }
                echo "</div>";
            }
        }
    } else {
        echo "<p style='color: red;'>Erro ou nenhum container encontrado</p>";
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Docker - TI Remoto</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #111424;
            color: white;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        pre {
            background: #1a1f3a;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #0EE57F;
            overflow-x: auto;
        }
        h2, h3 {
            color: #0EE57F;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Debug Docker - TI Remoto</h1>
        <?php testDockerContainers(); ?>
    </div>
</body>
</html>
