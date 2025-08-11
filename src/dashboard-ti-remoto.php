<?php
/**
 * Dashboard TI Remoto - Ambiente de Desenvolvimento
 * Personalizado com as cores e identidade da empresa
 */

// Configurações de erro para desenvolvimento
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Funções necessárias para o dashboard
function getDockerStatus() {
    $containers = [];

    // Verificar se o Docker está disponível
    $dockerCheck = shell_exec('docker --version 2>&1');
    if (strpos($dockerCheck, 'Docker version') === false) {
        return [];
    }

    // Obter lista de containers
    $output = shell_exec('docker ps -a --format "table {{ .Names }}\t{{ .Status }}\t{{ .Ports }}" 2>&1');

    if ($output && !strpos($output, 'error')) {
        $lines = explode("\n", trim($output));
        array_shift($lines); // Remove header

        foreach ($lines as $line) {
            if (trim($line)) {
                $parts = preg_split('/\s+/', trim($line), 3);
                if (count($parts) >= 2) {
                    $containers[] = [
                        'name' => $parts[0],
                        'status' => strpos($parts[1], 'Up') !== false ? 'running' : 'stopped',
                        'ports' => isset($parts[2]) ? $parts[2] : ''
                    ];
                }
            }
        }
    }

    return $containers;
}

function getSystemInfo() {
    return [
        'os' => PHP_OS_FAMILY,
        'hostname' => gethostname(),
        'user' => get_current_user(),
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'
    ];
}

function getNetworkInfo() {
    $localIp = 'N/A';

    // Tentar obter IP local
    if (PHP_OS_FAMILY === 'Windows') {
        $output = shell_exec('ipconfig | findstr /i "IPv4"');
        if ($output && preg_match('/(\d+\.\d+\.\d+\.\d+)/', $output, $matches)) {
            $localIp = $matches[1];
        }
    } else {
        $output = shell_exec('hostname -I 2>/dev/null');
        if ($output) {
            $localIp = trim(explode(' ', trim($output))[0]);
        }
    }

    return [
        'local_ip' => $localIp,
        'server_name' => $_SERVER['SERVER_NAME'] ?? 'localhost',
        'server_port' => $_SERVER['SERVER_PORT'] ?? '80'
    ];
}

function getProjectInfo() {
    $directory = __DIR__;
    $fileCount = 0;
    $totalSize = 0;

    // Contar arquivos e calcular tamanho
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $fileCount++;
            $totalSize += $file->getSize();
        }
    }

    // Formatar tamanho
    $units = ['B', 'KB', 'MB', 'GB'];
    $size = $totalSize;
    $unitIndex = 0;

    while ($size >= 1024 && $unitIndex < count($units) - 1) {
        $size /= 1024;
        $unitIndex++;
    }

    return [
        'directory' => basename($directory),
        'file_count' => $fileCount,
        'size' => round($size, 2) . ' ' . $units[$unitIndex]
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚀 TI Remoto - Dashboard de Desenvolvimento</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #111424 0%, #1a1f3a 50%, #111424 100%);
            min-height: 100vh;
            color: #ffffff;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header TI Remoto */
        .header {
            background: linear-gradient(135deg, #111424 0%, #0EE57F 100%);
            color: white;
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 15px 40px rgba(14, 229, 127, 0.3);
            position: relative;
            overflow: hidden;
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            position: relative;
            z-index: 2;
        }

        .header-logo {
            width: 80px;
            height: 80px;
            background: url('logo.png') no-repeat center;
            background-size: contain;
            filter: brightness(0) invert(1);
        }

        .header-text h1 {
            font-size: 3em;
            margin-bottom: 5px;
            font-weight: 700;
            background: linear-gradient(45deg, #ffffff, #0EE57F);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header-text p {
            font-size: 1.3em;
            opacity: 0.95;
            font-weight: 300;
        }

        /* Cards */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
        }

        .grid-2 {
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
        }

        .card {
            background: linear-gradient(145deg, #1a1f3a, #111424);
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
            transition: all 0.3s ease;
            border: 1px solid rgba(14, 229, 127, 0.2);
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #0EE57F, transparent);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(14, 229, 127, 0.2);
            border-color: rgba(14, 229, 127, 0.4);
        }

        .card:hover::before {
            height: 5px;
            background: linear-gradient(90deg, #0EE57F, #0EE57F);
        }

        .card h3 {
            color: #ffffff;
            margin-bottom: 20px;
            font-size: 1.4em;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }

        /* Status */
        .status {
            padding: 15px 18px;
            border-radius: 12px;
            margin: 10px 0;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .status-running {
            background: linear-gradient(135deg, rgba(14, 229, 127, 0.2), rgba(14, 229, 127, 0.1));
            color: #0EE57F;
            border: 1px solid rgba(14, 229, 127, 0.3);
            box-shadow: 0 4px 15px rgba(14, 229, 127, 0.1);
            animation: pulse 2s infinite;
        }

        .status-stopped {
            background: linear-gradient(135deg, rgba(255, 82, 82, 0.2), rgba(255, 82, 82, 0.1));
            color: #ff5252;
            border: 1px solid rgba(255, 82, 82, 0.3);
            box-shadow: 0 4px 15px rgba(255, 82, 82, 0.1);
        }

        .status-success {
            background: linear-gradient(135deg, rgba(14, 229, 127, 0.2), rgba(14, 229, 127, 0.1));
            color: #0EE57F;
            border: 1px solid rgba(14, 229, 127, 0.3);
            box-shadow: 0 4px 15px rgba(14, 229, 127, 0.1);
        }

        .status-error {
            background: linear-gradient(135deg, rgba(255, 82, 82, 0.2), rgba(255, 82, 82, 0.1));
            color: #ff5252;
            border: 1px solid rgba(255, 82, 82, 0.3);
            box-shadow: 0 4px 15px rgba(255, 82, 82, 0.1);
        }

        /* Tabelas */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: rgba(26, 31, 58, 0.5);
            border-radius: 10px;
            overflow: hidden;
        }

        .info-table th,
        .info-table td {
            padding: 15px 12px;
            text-align: left;
            border-bottom: 1px solid rgba(14, 229, 127, 0.1);
        }

        .info-table th {
            background: linear-gradient(135deg, rgba(14, 229, 127, 0.2), rgba(14, 229, 127, 0.1));
            font-weight: 600;
            color: #0EE57F;
            text-transform: uppercase;
            font-size: 0.85em;
            letter-spacing: 0.5px;
        }

        .info-table td {
            color: #ffffff;
        }

        .info-table tr:hover {
            background: rgba(14, 229, 127, 0.05);
        }

        /* Links */
        .link {
            color: #0EE57F;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .link:hover {
            color: #ffffff;
            text-shadow: 0 0 10px rgba(14, 229, 127, 0.5);
        }

        .link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: #0EE57F;
            transition: width 0.3s ease;
        }

        .link:hover::after {
            width: 100%;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 6px 12px;
            background: linear-gradient(135deg, rgba(26, 31, 58, 0.8), rgba(17, 20, 36, 0.8));
            border-radius: 20px;
            font-size: 0.85em;
            margin: 3px;
            font-weight: 500;
            border: 1px solid rgba(14, 229, 127, 0.2);
            transition: all 0.3s ease;
        }

        .badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(14, 229, 127, 0.2);
        }

        .badge-core {
            background: linear-gradient(135deg, rgba(14, 229, 127, 0.2), rgba(14, 229, 127, 0.1));
            color: #0EE57F;
            border-color: rgba(14, 229, 127, 0.3);
        }

        .badge-database {
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.2), rgba(255, 193, 7, 0.1));
            color: #ffc107;
            border-color: rgba(255, 193, 7, 0.3);
        }

        .badge-web {
            background: linear-gradient(135deg, rgba(0, 123, 255, 0.2), rgba(0, 123, 255, 0.1));
            color: #007bff;
            border-color: rgba(0, 123, 255, 0.3);
        }

        .badge-other {
            background: linear-gradient(135deg, rgba(108, 117, 125, 0.2), rgba(108, 117, 125, 0.1));
            color: #6c757d;
            border-color: rgba(108, 117, 125, 0.3);
        }

        /* Métricas */
        .metric {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, rgba(26, 31, 58, 0.6), rgba(17, 20, 36, 0.6));
            border-radius: 15px;
            margin: 10px 0;
            border: 1px solid rgba(14, 229, 127, 0.2);
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .metric:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(14, 229, 127, 0.15);
            border-color: rgba(14, 229, 127, 0.4);
        }

        .metric-value {
            font-size: 2.2em;
            font-weight: bold;
            color: #0EE57F;
            text-shadow: 0 0 10px rgba(14, 229, 127, 0.3);
        }

        .metric-label {
            font-size: 0.9em;
            color: #ffffff;
            margin-top: 8px;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Refresh indicator */
        .refresh-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #111424, #0EE57F);
            color: white;
            padding: 12px 20px;
            border-radius: 30px;
            font-size: 0.9em;
            box-shadow: 0 8px 25px rgba(14, 229, 127, 0.3);
            z-index: 1000;
            border: 1px solid rgba(14, 229, 127, 0.3);
            backdrop-filter: blur(10px);
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .refresh-indicator:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(14, 229, 127, 0.4);
        }

        /* Animações */
        <blade keyframes|%20pulse%20%7B>0% {
            box-shadow: 0 0 0 0 rgba(14, 229, 127, 0.4);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(14, 229, 127, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(14, 229, 127, 0);
        }
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(17, 20, 36, 0.5);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #0EE57F, #111424);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #111424, #0EE57F);
        }

        /* Responsividade */
        <blade media|%20(max-width%3A%20768px)%20%7B>.container {
            padding: 15px;
        }

        .header {
            padding: 25px;
        }

        .header-text h1 {
            font-size: 2.2em;
        }

        .header-text p {
            font-size: 1.1em;
        }

        .grid {
            grid-template-columns: 1fr;
        }

        .grid-2 {
            grid-template-columns: 1fr;
        }

        .refresh-indicator {
            top: 10px;
            right: 10px;
            padding: 8px 15px;
            font-size: 0.8em;
        }
        }
    </style>
</head>

<body>
    <!-- Indicador de refresh automático -->
    <div class="refresh-indicator" id="refreshIndicator">
        🔄 Atualizando em <span id="countdown">30</span>s
    </div>

    <div class="container">
        <!-- Header TI Remoto -->
        <div class="header">
            <div class="header-content">
                <div class="header-logo"></div>
                <div class="header-text">
                    <h1>TI Remoto</h1>
                    <p>Dashboard de Desenvolvimento Inteligente</p>
                </div>
            </div>
        </div>

        <?php
        // Reutilizar as funções do dashboard original
        $dockerStatus = getDockerStatus();
        $systemInfo = getSystemInfo();
        $networkInfo = getNetworkInfo();
        $projectInfo = getProjectInfo();
        ?>

        <!-- Grid principal -->
        <div class="grid">
            <!-- Status dos Containers -->
            <div class="card">
                <h3>🐳 Status dos Containers</h3>
                <?php if (!empty($dockerStatus)): ?>
                <?php foreach ($dockerStatus as $container): ?>
                <div class="status status-<?= $container['status'] === 'running' ? 'running' : 'stopped' ?>">
                    <strong><?= htmlspecialchars($container['name']) ?></strong>
                    <span><?= ucfirst($container['status']) ?></span>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="status status-error">
                    <strong>Docker não está rodando</strong>
                    <span>Verifique se o Docker Desktop está iniciado</span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Informações do Sistema -->
            <div class="card">
                <h3>💻 Sistema</h3>
                <table class="info-table">
                    <tr>
                        <th>OS</th>
                        <td><?= htmlspecialchars($systemInfo['os']) ?></td>
                    </tr>
                    <tr>
                        <th>Hostname</th>
                        <td><?= htmlspecialchars($systemInfo['hostname']) ?></td>
                    </tr>
                    <tr>
                        <th>IP Local</th>
                        <td><?= htmlspecialchars($networkInfo['local_ip']) ?></td>
                    </tr>
                    <tr>
                        <th>Usuário</th>
                        <td><?= htmlspecialchars($systemInfo['user']) ?></td>
                    </tr>
                </table>
            </div>

            <!-- Métricas Rápidas -->
            <div class="card">
                <h3>📊 Métricas</h3>
                <div class="metric">
                    <div class="metric-value">
                        <?= count($dockerStatus) ?>
                    </div>
                    <div class="metric-label">Total de Containers</div>
                </div>
                <div class="metric">
                    <div class="metric-value">
                        <?= array_sum(array_map(fn($s) => $s['status'] === 'running' ? 1 : 0, $dockerStatus)) ?>
                    </div>
                    <div class="metric-label">Containers Ativos</div>
                </div>
            </div>

            <!-- Informações do Projeto -->
            <div class="card">
                <h3>📁 Projeto</h3>
                <table class="info-table">
                    <tr>
                        <th>Diretório</th>
                        <td><?= htmlspecialchars($projectInfo['directory']) ?></td>
                    </tr>
                    <tr>
                        <th>Arquivos</th>
                        <td><?= $projectInfo['file_count'] ?></td>
                    </tr>
                    <tr>
                        <th>Tamanho</th>
                        <td><?= $projectInfo['size'] ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Links Rápidos -->
        <div class="grid grid-2" style="margin-top: 30px;">
            <div class="card">
                <h3>🔗 Links Rápidos</h3>
                <div
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px;">
                    <a href="http://localhost" class="link">🌐 Localhost</a>
                    <a href="http://localhost:8080" class="link">🚀 App (8080)</a>
                    <a href="http://localhost:3306" class="link">🗄️ MySQL (3306)</a>
                    <a href="http://localhost:8025" class="link">📧 MailHog (8025)</a>
                    <a href="http://localhost:9000" class="link">⚡ PHPMyAdmin (9000)</a>
                    <a href="http://localhost:6379" class="link">🔴 Redis (6379)</a>
                </div>
            </div>

            <div class="card">
                <h3>🛠️ Ferramentas</h3>
                <div
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px;">
                    <a href="?action=restart_containers" class="link">🔄 Reiniciar Containers</a>
                    <a href="?action=clear_cache" class="link">🧹 Limpar Cache</a>
                    <a href="?action=view_logs" class="link">📋 Ver Logs</a>
                    <a href="?action=backup" class="link">💾 Backup</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh da página
        let countdown = 30;
        const countdownElement = document.getElementById('countdown');

        function updateCountdown() {
            countdownElement.textContent = countdown;
            countdown--;

            if (countdown < 0) {
                location.reload();
            }
        }

        // Atualizar a cada segundo
        setInterval(updateCountdown, 1000);

        // Pausar countdown ao passar o mouse sobre a página
        document.addEventListener('mouseenter', () => {
            countdown = 30;
        });
    </script>
</body>

</html>