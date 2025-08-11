<?php
/**
 * ========================================
 * TI REMOTO - DASHBOARD DE DESENVOLVIMENTO
 * ========================================
 *
 * Dashboard inteligente para monitoramento em tempo real
 * do ambiente de desenvolvimento Docker
 *
 * Funcionalidades:
 * - Status dos containers Docker
 * - Informações do sistema e performance
 * - Monitoramento de bancos de dados (MySQL/Redis)
 * - Informações de rede e conectividade
 * - Auto-refresh e interface responsiva
 *
 * @author TI Remoto
 * @version 2.0
 */

// ========================================
// CONFIGURAÇÕES INICIAIS
// ========================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ========================================
// FUNÇÕES DE SISTEMA E MONITORAMENTO
// ========================================

/**
 * Obtém informações detalhadas do sistema
 * @return array Informações do PHP, servidor e performance
 */
function getSystemInfo() {
    $info = [];

    // Informações básicas do PHP
    $info['php_version'] = phpversion();
    $info['server_software'] = $_SERVER['SERVER_SOFTWARE'] ?? 'N/A';
    $info['document_root'] = $_SERVER['DOCUMENT_ROOT'] ?? 'N/A';
    $info['server_time'] = date('Y-m-d H:i:s');
    $info['timezone'] = date_default_timezone_get();
    $info['memory_limit'] = ini_get('memory_limit');
    $info['max_execution_time'] = ini_get('max_execution_time');
    $info['upload_max_filesize'] = ini_get('upload_max_filesize');

    // Informações de memória
    $info['memory_usage'] = round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB';
    $info['memory_peak'] = round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB';

    return $info;
}

/**
 * Verifica o status dos containers Docker
 * @return array Status e informações de cada container
 */
function getDockerContainerStatus() {
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

    // Comando sem espaços extras para evitar problemas de formatação
    $cmd = 'docker ps -a --format "{{ .Names }}\t{{ .Status }}" 2>&1';
    $output = shell_exec($cmd);

    if ($output && !strpos($output, 'error')) {
        $lines = explode("\n", trim($output));

        foreach ($lines as $line) {
            if (trim($line)) {
                $parts = explode("\t", trim($line));
                if (count($parts) >= 2) {
                    $name = $parts[0];
                    $statusInfo = $parts[1];

                    // Determinar descrição baseada no nome
                    $description = getContainerDescription($name, '');

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

    // Se não encontrou containers, mostrar mensagem
    if (empty($status)) {
        $status['no_containers'] = [
            'name' => 'Nenhum container encontrado',
            'status' => 'stopped',
            'uptime' => 'Execute docker-compose up para iniciar os containers'
        ];
    }

    return $status;
}

/**
 * Determina a descrição do container baseada no nome e imagem
 * @param string $name Nome do container
 * @param string $image Imagem do container
 * @return string Descrição amigável
 */
function getContainerDescription($name, $image) {
    // Mapeamento baseado no nome
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

    // Mapeamento baseado na imagem
    if (strpos($image, 'mysql') !== false) return '🗄️ MySQL Database';
    if (strpos($image, 'redis') !== false) return '🔴 Redis Cache';
    if (strpos($image, 'python') !== false) return '🐍 Python Environment';
    if (strpos($image, 'node') !== false) return '📦 Node.js Environment';
    if (strpos($image, 'phpmyadmin') !== false) return '⚡ phpMyAdmin';
    if (strpos($image, 'mailhog') !== false) return '📧 MailHog';
    if (strpos($image, 'apache') !== false || strpos($image, 'php') !== false) return '🌐 Web Server';

    // Fallback para nome do container
    return '📦 ' . ucfirst(str_replace(['dev_', '_'], ['', ' '], $name));
}

/**
 * Testa conexão MySQL e obtém informações detalhadas
 * @return array Status, versão, uptime, conexões e bancos de dados
 */
function getMySQL_Info() {
    try {
        $pdo = new PDO(
            'mysql:host=mysql;dbname=desenvolvimento;charset=utf8mb4',
            'dev_user',
            'dev_pass',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Informações básicas
        $version = $pdo->query("SELECT VERSION()")->fetchColumn();
        $uptime = $pdo->query("SHOW STATUS LIKE 'Uptime'")->fetch()['Value'];
        $connections = $pdo->query("SHOW STATUS LIKE 'Threads_connected'")->fetch()['Value'];
        $queries = $pdo->query("SHOW STATUS LIKE 'Queries'")->fetch()['Value'];

        // Bancos de dados
        $databases = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);

        // Tabelas no banco desenvolvimento
        $tables = $pdo->query("SHOW TABLES FROM desenvolvimento")->fetchAll(PDO::FETCH_COLUMN);

        return [
            'status' => 'success',
            'version' => $version,
            'uptime' => gmdate("H:i:s", $uptime),
            'connections' => $connections,
            'queries' => number_format($queries),
            'databases' => $databases,
            'tables' => $tables,
            'message' => 'MySQL conectado e funcionando!'
        ];
    } catch (PDOException $e) {
        return [
            'status' => 'error',
            'message' => 'Erro na conexão MySQL: ' . $e->getMessage()
        ];
    }
}

/**
 * Testa conexão Redis e obtém informações detalhadas
 * @return array Status, versão, memória e estatísticas
 */
function getRedisInfo() {
    try {
        $redis = new Redis();
        $redis->connect('redis', 6379);

        // Informações do Redis
        $info = $redis->info();
        $dbsize = $redis->dbSize();

        // Teste de funcionamento
        $redis->set('dashboard_test', 'Dashboard funcionando - ' . date('Y-m-d H:i:s'));
        $testResult = $redis->get('dashboard_test');

        $redis->close();

        return [
            'status' => 'success',
            'version' => $info['redis_version'] ?? 'N/A',
            'uptime' => isset($info['uptime_in_seconds']) ? gmdate("H:i:s", $info['uptime_in_seconds']) : 'N/A',
            'memory_used' => isset($info['used_memory_human']) ? $info['used_memory_human'] : 'N/A',
            'keys_count' => $dbsize,
            'connected_clients' => $info['connected_clients'] ?? 'N/A',
            'message' => 'Redis conectado e funcionando!'
        ];
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Erro na conexão Redis: ' . $e->getMessage()
        ];
    }
}

/**
 * Obtém extensões PHP carregadas organizadas por categoria
 * @return array Extensões categorizadas (core, database, web, other)
 */
function getPHPExtensions() {
    $extensions = get_loaded_extensions();
    sort($extensions);

    $categorized = [
        'core' => [],
        'database' => [],
        'web' => [],
        'other' => []
    ];

    $dbExtensions = ['mysql', 'mysqli', 'pdo', 'pdo_mysql', 'redis', 'mongodb'];
    $webExtensions = ['curl', 'json', 'xml', 'gd', 'mbstring', 'openssl', 'zip'];
    $coreExtensions = ['core', 'standard', 'pcre', 'spl', 'reflection'];

    foreach ($extensions as $ext) {
        $extLower = strtolower($ext);
        if (in_array($extLower, $coreExtensions)) {
            $categorized['core'][] = $ext;
        } elseif (in_array($extLower, $dbExtensions)) {
            $categorized['database'][] = $ext;
        } elseif (in_array($extLower, $webExtensions)) {
            $categorized['web'][] = $ext;
        } else {
            $categorized['other'][] = $ext;
        }
    }

    return $categorized;
}

// ========================================
// EXECUÇÃO E COLETA DE DADOS
// ========================================

// Obter todas as informações do sistema
$systemInfo = getSystemInfo();
$dockerStatus = getDockerContainerStatus();
$mysqlInfo = getMySQL_Info();
$redisInfo = getRedisInfo();
$phpExtensions = getPHPExtensions();
?>
<!--
========================================
TI REMOTO - DASHBOARD HTML
========================================
Interface responsiva e moderna para monitoramento
do ambiente de desenvolvimento
-->
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚀 TI Remoto - Dashboard de Desenvolvimento</title>
    <style>
        /* ========================================
           TI REMOTO - ESTILOS PERSONALIZADOS
           ======================================== */

        /* Reset e configurações base */
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

        /* ========================================
           HEADER TI REMOTO
           ======================================== */
        .header {
            background: #111424;
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(17, 20, 36, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
        }



        .header-content {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }

        .header-logo img {
            height: 80px;
            width: auto;
            object-fit: contain;
        }



        /* ========================================
           LAYOUT E CARDS
           ======================================== */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
        }

        .grid-2 {
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
        }

        /* Cards */
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
            background: linear-gradient(90deg, #0EE57F, #0EE57F, transparent);
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

        /* ========================================
           COMPONENTES DE STATUS
           ======================================== */
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

        /* Tables */
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

        /* Metrics */
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

        /* Auto-refresh indicator */
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

        /* Scrollbar personalizada */
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

        .status-running {
            animation: pulse 2s infinite;
        }

        /* Responsividade */
        <blade media|%20(max-width%3A%20768px)%20%7B>.container {
            padding: 15px;
        }

        .header {
            padding: 25px;
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
    <!-- ========================================
         INTERFACE DO DASHBOARD
         ======================================== -->

    <!-- Indicador de refresh automático -->
    <div class="refresh-indicator" id="refreshIndicator">
        🔄 Atualizando em <span id="countdown">30</span>s
    </div>

    <!-- Container principal -->
    <div class="container">
        <!-- Header TI Remoto -->
        <div class="header">
            <div class="header-logo">
                <img src="logo.png" alt="TI Remoto Logo" />
            </div>
        </div>

        <!-- ========================================
             SEÇÃO 1: CONTAINERS E SISTEMA
             ======================================== -->
        <div class="grid">
            <div class="card">
                <h3>🐳 Status dos Containers</h3>
                <?php foreach ($dockerStatus as $container => $info): ?>
                <div class="status status-<?= $info['status'] ?>">
                    <strong><?= $info['name'] ?>:</strong>
                    <span><?= $info['status'] === 'running' ? '✅ ' . $info['uptime'] : '❌ Parado' ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Informações do Sistema -->
            <div class="card">
                <h3>💻 Sistema & Performance</h3>
                <table class="info-table">
                    <tr>
                        <th>PHP Version</th>
                        <td><?= $systemInfo['php_version'] ?></td>
                    </tr>
                    <tr>
                        <th>Servidor</th>
                        <td><?= $systemInfo['server_software'] ?></td>
                    </tr>
                    <tr>
                        <th>Timezone</th>
                        <td><?= $systemInfo['timezone'] ?></td>
                    </tr>
                    <tr>
                        <th>Memory Limit</th>
                        <td><?= $systemInfo['memory_limit'] ?></td>
                    </tr>
                    <tr>
                        <th>Uso de Memória</th>
                        <td><?= $systemInfo['memory_usage'] ?></td>
                    </tr>
                    <tr>
                        <th>Pico de Memória</th>
                        <td><?= $systemInfo['memory_peak'] ?></td>
                    </tr>
                    <tr>
                        <th>Max Execution Time</th>
                        <td><?= $systemInfo['max_execution_time'] ?>s</td>
                    </tr>
                    <tr>
                        <th>Upload Max Size</th>
                        <td><?= $systemInfo['upload_max_filesize'] ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- ========================================
             SEÇÃO 2: BANCOS DE DADOS
             ======================================== -->
        <div class="grid grid-2">
            <!-- MySQL -->
            <div class="card">
                <h3>🗄️ MySQL Database</h3>
                <div class="status status-<?= $mysqlInfo['status'] ?>">
                    <strong>Status:</strong> <?= $mysqlInfo['message'] ?>
                </div>

                <?php if ($mysqlInfo['status'] === 'success'): ?>
                <div class="grid"
                    style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin: 20px 0;">
                    <div class="metric">
                        <div class="metric-value"><?= $mysqlInfo['version'] ?></div>
                        <div class="metric-label">Versão</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value"><?= $mysqlInfo['uptime'] ?></div>
                        <div class="metric-label">Uptime</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value"><?= $mysqlInfo['connections'] ?></div>
                        <div class="metric-label">Conexões</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value"><?= $mysqlInfo['queries'] ?></div>
                        <div class="metric-label">Queries</div>
                    </div>
                </div>

                <h4>📊 Bancos de Dados:</h4>
                <p><?= implode(', ', $mysqlInfo['databases']) ?></p>

                <h4>📋 Tabelas (desenvolvimento):</h4>
                <p><?= !empty($mysqlInfo['tables']) ? implode(', ', $mysqlInfo['tables']) : 'Nenhuma tabela encontrada' ?>
                </p>
                <?php endif; ?>
            </div>

            <!-- Redis -->
            <div class="card">
                <h3>🔴 Redis Cache</h3>
                <div class="status status-<?= $redisInfo['status'] ?>">
                    <strong>Status:</strong> <?= $redisInfo['message'] ?>
                </div>

                <?php if ($redisInfo['status'] === 'success'): ?>
                <div class="grid"
                    style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin: 20px 0;">
                    <div class="metric">
                        <div class="metric-value"><?= $redisInfo['version'] ?></div>
                        <div class="metric-label">Versão</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value"><?= $redisInfo['uptime'] ?></div>
                        <div class="metric-label">Uptime</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value"><?= $redisInfo['keys_count'] ?></div>
                        <div class="metric-label">Chaves</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value"><?= $redisInfo['memory_used'] ?></div>
                        <div class="metric-label">Memória</div>
                    </div>
                </div>

                <h4>👥 Clientes Conectados:</h4>
                <p><?= $redisInfo['connected_clients'] ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- ========================================
             SEÇÃO 3: EXTENSÕES E FERRAMENTAS
             ======================================== -->
        <div class="grid">
            <!-- Extensões PHP Organizadas -->
            <div class="card">
                <h3>🔧 Extensões PHP</h3>

                <h4>🔵 Core:</h4>
                <div style="margin-bottom: 15px;">
                    <?php foreach ($phpExtensions['core'] as $ext): ?>
                    <span class="badge badge-core"><?= $ext ?></span>
                    <?php endforeach; ?>
                </div>

                <h4>🗄️ Database:</h4>
                <div style="margin-bottom: 15px;">
                    <?php foreach ($phpExtensions['database'] as $ext): ?>
                    <span class="badge badge-database"><?= $ext ?></span>
                    <?php endforeach; ?>
                </div>

                <h4>🌐 Web:</h4>
                <div style="margin-bottom: 15px;">
                    <?php foreach ($phpExtensions['web'] as $ext): ?>
                    <span class="badge badge-web"><?= $ext ?></span>
                    <?php endforeach; ?>
                </div>

                <h4>⚙️ Outras:</h4>
                <div style="max-height: 150px; overflow-y: auto;">
                    <?php foreach ($phpExtensions['other'] as $ext): ?>
                    <span class="badge badge-other"><?= $ext ?></span>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Links e Ferramentas -->
            <div class="card">
                <h3>🛠️ Ferramentas & Links</h3>

                <h4>🌐 Serviços Web:</h4>
                <ul style="list-style: none; padding: 0; margin-bottom: 20px;">
                    <li style="margin: 8px 0;">
                        <a href="http://localhost" target="_blank" class="link">🐘 Aplicação PHP</a>
                        <span style="color: #6c757d; font-size: 0.9em;"> - Porta 80</span>
                    </li>
                    <li style="margin: 8px 0;">
                        <a href="http://localhost:8080" target="_blank" class="link">📊 phpMyAdmin</a>
                        <span style="color: #6c757d; font-size: 0.9em;"> - Gerenciar MySQL</span>
                    </li>
                    <li style="margin: 8px 0;">
                        <a href="http://localhost:5000" target="_blank" class="link">🐍 Python Flask</a>
                        <span style="color: #6c757d; font-size: 0.9em;"> - Porta 5000</span>
                    </li>
                    <li style="margin: 8px 0;">
                        <a href="http://localhost:3000" target="_blank" class="link">🟢 Node.js Frontend</a>
                        <span style="color: #6c757d; font-size: 0.9em;"> - Porta 3000</span>
                    </li>
                    <li style="margin: 8px 0;">
                        <a href="http://localhost:8025" target="_blank" class="link">📧 MailHog</a>
                        <span style="color: #6c757d; font-size: 0.9em;"> - Testes de Email</span>
                    </li>
                </ul>

                <h4>💾 Credenciais MySQL:</h4>
                <table class="info-table" style="font-size: 0.9em;">
                    <tr>
                        <th>Host</th>
                        <td>mysql (interno) / localhost (externo)</td>
                    </tr>
                    <tr>
                        <th>Porta</th>
                        <td>3306</td>
                    </tr>
                    <tr>
                        <th>Usuário</th>
                        <td>dev_user</td>
                    </tr>
                    <tr>
                        <th>Senha</th>
                        <td>dev_pass</td>
                    </tr>
                    <tr>
                        <th>Banco</th>
                        <td>desenvolvimento</td>
                    </tr>
                </table>

                <h4>⚡ Comandos Úteis:</h4>
                <div
                    style="background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; font-size: 0.85em;">
                    <div>scripts\start.bat - Iniciar ambiente</div>
                    <div>scripts\stop.bat - Parar ambiente</div>
                    <div>docker-compose logs - Ver logs</div>
                    <div>docker-compose ps - Status containers</div>
                </div>
            </div>
        </div>

        <!-- Informações de Tempo Real -->
        <div class="card">
            <h3>⏱️ Informações em Tempo Real</h3>
            <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div class="metric">
                    <div class="metric-value" id="currentTime"><?= $systemInfo['server_time'] ?></div>
                    <div class="metric-label">Hora do Servidor</div>
                </div>
                <div class="metric">
                    <div class="metric-value"><?= count($dockerStatus) ?></div>
                    <div class="metric-label">Containers Configurados</div>
                </div>
                <div class="metric">
                    <div class="metric-value">
                        <?= array_sum(array_map(function($s) { return $s['status'] === 'running' ? 1 : 0; }, $dockerStatus)) ?>
                    </div>
                    <div class="metric-label">Containers Ativos</div>
                </div>
                <div class="metric">
                    <div class="metric-value">
                        <?= count($phpExtensions['core']) + count($phpExtensions['database']) + count($phpExtensions['web']) + count($phpExtensions['other']) ?>
                    </div>
                    <div class="metric-label">Extensões PHP</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================
         JAVASCRIPT - FUNCIONALIDADES DINÂMICAS
         ======================================== -->
    <script>
        // ========================================
        // AUTO-REFRESH E INTERATIVIDADE
        // ========================================
        let countdown = 30;
        const countdownElement = document.getElementById('countdown');
        const refreshIndicator = document.getElementById('refreshIndicator');

        function updateCountdown() {
            countdownElement.textContent = countdown;
            countdown--;

            if (countdown < 0) {
                refreshIndicator.innerHTML = '🔄 Atualizando...';
                location.reload();
            }
        }

        // Atualizar contador a cada segundo
        setInterval(updateCountdown, 1000);

        // Atualizar hora atual a cada segundo
        function updateTime() {
            const now = new Date();
            const timeString = now.getFullYear() + '-' +
                String(now.getMonth() + 1).padStart(2, '0') + '-' +
                String(now.getDate()).padStart(2, '0') + ' ' +
                String(now.getHours()).padStart(2, '0') + ':' +
                String(now.getMinutes()).padStart(2, '0') + ':' +
                String(now.getSeconds()).padStart(2, '0');

            const timeElement = document.getElementById('currentTime');
            if (timeElement) {
                timeElement.textContent = timeString;
            }
        }

        setInterval(updateTime, 1000);

        // Pausar auto-refresh quando o usuário interage
        let userInteracted = false;
        document.addEventListener('click', function () {
            if (!userInteracted) {
                userInteracted = true;
                refreshIndicator.innerHTML = '⏸️ Auto-refresh pausado (clique para reativar)';
                refreshIndicator.style.cursor = 'pointer';
                refreshIndicator.onclick = function () {
                    location.reload();
                };
            }
        });
    </script>
</body>

</html>