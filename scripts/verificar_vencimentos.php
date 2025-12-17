<?php
// Permitir execução apenas via linha de comando ou com chave secreta
if (php_sapi_name() !== 'cli' && !isset($_GET['token'])) {
    die('Acesso negado');
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/config_sistema.php';

echo "Iniciando verificação de vencimentos...\n";

$wahaUrl = getConfig('waha_url');
if (!$wahaUrl) {
    die("URL do Waha não configurada.\n");
}

// Verificar conexão com WhatsApp
function checkWahaConnection()
{
    global $wahaUrl;
    $ch = curl_init($wahaUrl . '/api/sessions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Assumindo que 200 OK significa que o serviço está rodando.
    // Para ser mais específico, verificar se há uma sessão 'default' com status 'WORKING'
    if ($httpCode != 200)
        return false;

    $data = json_decode($response, true);
    foreach ($data as $session) {
        if ($session['name'] == 'default' && $session['status'] == 'WORKING') {
            return true;
        }
    }
    return false;
}

$wahaConnected = checkWahaConnection();
echo "Status do WhatsApp: " . ($wahaConnected ? "CONECTADO" : "DESCONECTADO") . "\n";

/**
 * Função para registrar log
 */
function logCobranca($tipo, $vendedor, $cliente, $telefone, $status, $msg, $erro = null)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO logs_cobrancas (tipo, vendedor_nome, cliente_rs, telefone, status, mensagem, erro) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$tipo, $vendedor, $cliente, $telefone, $status, $msg, $erro]);
    } catch (Exception $e) {
        echo "Erro ao salvar log: " . $e->getMessage() . "\n";
    }
}

/**
 * Função para enviar mensagem via Waha
 */
function sendWahaMessage($phone, $message)
{
    global $wahaUrl;

    // Formatar telefone (ex: 554799999999)
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) < 10)
        return false;
    if (substr($phone, 0, 2) != '55')
        $phone = '55' . $phone;
    $chatId = $phone . '@c.us';

    $payload = [
        'chatId' => $chatId,
        'text' => $message,
        'session' => 'default'
    ];

    $ch = curl_init($wahaUrl . '/api/sendText');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($httpCode == 201 || $httpCode == 200) {
        return ['success' => true];
    } else {
        return ['success' => false, 'error' => "HTTP $httpCode: $response $error"];
    }
}

/**
 * Função para substituir variáveis no template
 */
function formatMessage($template, $vendedor, $cliente, $fatura)
{
    if (!$template)
        return "";

    $replaces = [
        '{vendedor}' => $vendedor['nome'],
        '{cliente}' => $cliente['razao_social'],
        '{valor}' => 'R$ ' . number_format($fatura['valor'], 2, ',', '.'),
        '{vencimento}' => date('d/m/Y', strtotime($fatura['data_vencimento'])),
        '{link_pagamento}' => $fatura['url_pagamento'] ?? 'N/A'
    ];

    return str_replace(array_keys($replaces), array_values($replaces), $template);
}


// Datas alvo
$hoje = date('Y-m-d');
$daqui5dias = date('Y-m-d', strtotime('+5 days'));

// 1. Vencendo em 5 dias
echo "Verificando faturas para: $daqui5dias (5 dias)\n";
$stmt = $pdo->prepare("
    SELECT f.*, c.razao_social, v.nome as vendedor_nome, v.telefone as vendedor_telefone 
    FROM faturas f
    JOIN clientes c ON f.cliente_id = c.id
    JOIN usuarios v ON c.vendedor_id = v.id
    WHERE f.data_vencimento = ? AND f.status = 'pendente'
");
$stmt->execute([$daqui5dias]);
$faturas = $stmt->fetchAll();

$template5dias = getConfig('msg_vencimento_5dias');
foreach ($faturas as $f) {
    if ($f['vendedor_telefone']) {
        $msg = formatMessage(
            $template5dias,
            ['nome' => $f['vendedor_nome']],
            ['razao_social' => $f['razao_social']],
            $f
        );

        if ($wahaConnected) {
            $res = sendWahaMessage($f['vendedor_telefone'], $msg);
            if ($res['success']) {
                logCobranca('5_dias', $f['vendedor_nome'], $f['razao_social'], $f['vendedor_telefone'], 'enviado', $msg);
                echo " [5 dias] Enviado: {$f['vendedor_nome']}\n";
            } else {
                logCobranca('5_dias', $f['vendedor_nome'], $f['razao_social'], $f['vendedor_telefone'], 'erro_envio', $msg, $res['error']);
                echo " [5 dias] Erro ao enviar: {$f['vendedor_nome']}\n";
            }
        } else {
            logCobranca('5_dias', $f['vendedor_nome'], $f['razao_social'], $f['vendedor_telefone'], 'nao_conectado', $msg, 'WhatsApp Desconectado');
            echo " [5 dias] Logado (sem envio): {$f['vendedor_nome']}\n";
        }
    }
}

// 2. Vencendo hoje
echo "Verificando faturas para: $hoje (hoje)\n";
$stmt = $pdo->prepare("
    SELECT f.*, c.razao_social, v.nome as vendedor_nome, v.telefone as vendedor_telefone 
    FROM faturas f
    JOIN clientes c ON f.cliente_id = c.id
    JOIN usuarios v ON c.vendedor_id = v.id
    WHERE f.data_vencimento = ? AND f.status = 'pendente'
");
$stmt->execute([$hoje]);
$faturas = $stmt->fetchAll();

$templateHoje = getConfig('msg_vencimento_hoje');
foreach ($faturas as $f) {
    if ($f['vendedor_telefone']) {
        $msg = formatMessage(
            $templateHoje,
            ['nome' => $f['vendedor_nome']],
            ['razao_social' => $f['razao_social']],
            $f
        );

        if ($wahaConnected) {
            $res = sendWahaMessage($f['vendedor_telefone'], $msg);
            if ($res['success']) {
                logCobranca('hoje', $f['vendedor_nome'], $f['razao_social'], $f['vendedor_telefone'], 'enviado', $msg);
                echo " [HOJE] Enviado: {$f['vendedor_nome']}\n";
            } else {
                logCobranca('hoje', $f['vendedor_nome'], $f['razao_social'], $f['vendedor_telefone'], 'erro_envio', $msg, $res['error']);
                echo " [HOJE] Erro ao enviar: {$f['vendedor_nome']}\n";
            }
        } else {
            logCobranca('hoje', $f['vendedor_nome'], $f['razao_social'], $f['vendedor_telefone'], 'nao_conectado', $msg, 'WhatsApp Desconectado');
            echo " [HOJE] Logado (sem envio): {$f['vendedor_nome']}\n";
        }
    }
}

// 3. Atrasadas
echo "Verificando faturas atrasadas...\n";
$stmt = $pdo->prepare("
    SELECT f.*, c.razao_social, v.nome as vendedor_nome, v.telefone as vendedor_telefone 
    FROM faturas f
    JOIN clientes c ON f.cliente_id = c.id
    JOIN usuarios v ON c.vendedor_id = v.id
    WHERE f.data_vencimento < ? 
    AND f.data_vencimento > DATE_SUB(?, INTERVAL 30 DAY)
    AND f.status IN ('pendente', 'atrasado')
");
$stmt->execute([$hoje, $hoje]);
$faturas = $stmt->fetchAll();

$templateAtraso = getConfig('msg_vencimento_atrasado');
foreach ($faturas as $f) {
    if ($f['vendedor_telefone']) {
        $msg = formatMessage(
            $templateAtraso,
            ['nome' => $f['vendedor_nome']],
            ['razao_social' => $f['razao_social']],
            $f
        );

        if ($wahaConnected) {
            $res = sendWahaMessage($f['vendedor_telefone'], $msg);
            if ($res['success']) {
                logCobranca('atrasado', $f['vendedor_nome'], $f['razao_social'], $f['vendedor_telefone'], 'enviado', $msg);
                echo " [ATRASO] Enviado: {$f['vendedor_nome']}\n";
            } else {
                logCobranca('atrasado', $f['vendedor_nome'], $f['razao_social'], $f['vendedor_telefone'], 'erro_envio', $msg, $res['error']);
                echo " [ATRASO] Erro ao enviar: {$f['vendedor_nome']}\n";
            }
        } else {
            logCobranca('atrasado', $f['vendedor_nome'], $f['razao_social'], $f['vendedor_telefone'], 'nao_conectado', $msg, 'WhatsApp Desconectado');
            echo " [ATRASO] Logado (sem envio): {$f['vendedor_nome']}\n";
        }
    }
}

echo "Concluído.\n";
?>