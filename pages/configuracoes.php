<?php
session_start();
require_once '../includes/autenticacao.php';
require_once '../includes/config_sistema.php';

// Apenas admin pode acessar
if ($_SESSION['user_tipo'] !== 'admin') {
    header('Location: painel.php');
    exit;
}

$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $chave => $valor) {
        if (strpos($chave, 'config_') === 0) {
            $key = substr($chave, 7); // Remove 'config_'
            setConfig($key, $valor);
        }
    }
    $mensagem = 'Configurações atualizadas com sucesso!';
}

$configs = [];
foreach (getAllConfigs() as $c) {
    $configs[$c['chave']] = $c['valor'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações do Sistema</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="layout">
        <?php include '../includes/menu_lateral.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <div class="welcome-text">
                    <h1>Configurações do Sistema</h1>
                    <p>Gerencie as integrações e parâmetros</p>
                </div>
            </header>

            <?php if ($mensagem): ?>
                <div
                    style="background: #dcfce7; color: #166534; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                    <?= $mensagem ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="grid-container">
                <!-- Configurações Asaas -->
                <div class="card">
                    <div class="card-header">
                        <h2>Integração Asaas</h2>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ambiente</label>
                        <select name="config_asaas_ambiente" class="form-control">
                            <option value="sandbox" <?= ($configs['asaas_ambiente'] ?? '') === 'sandbox' ? 'selected' : '' ?>>Sandbox (Testes)</option>
                            <option value="production" <?= ($configs['asaas_ambiente'] ?? '') === 'production' ? 'selected' : '' ?>>Produção</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">API Key</label>
                        <input type="text" name="config_asaas_api_key" class="form-control"
                            value="<?= htmlspecialchars($configs['asaas_api_key'] ?? '') ?>">
                    </div>
                </div>

                <!-- Configurações WhatsApp (Waha) -->
                <div class="card">
                    <div class="card-header"
                        style="display: flex; justify-content: space-between; align-items: center;">
                        <h2>Integração WhatsApp (Waha)</h2>
                        <a href="relatorio_cobrancas.php" class="btn btn-secondary"
                            style="font-size: 0.85em; padding: 0.4em 0.8em;">Ver Logs de Envio</a>
                    </div>

                    <div class="form-group">
                        <label class="form-label">URL da API Waha</label>
                        <input type="text" name="config_waha_url" class="form-control"
                            value="<?= htmlspecialchars($configs['waha_url'] ?? 'http://waha:3000') ?>">
                        <small style="color: grey;">Internamente no Docker é geralmente http://waha:3000</small>
                    </div>

                    <div style="margin-top: 1rem; padding: 1rem; background: #f8fafc; border-radius: 0.5rem;">
                        <h3>Status da Conexão</h3>
                        <p>Para conectar o WhatsApp, acesse o painel do Waha na porta 3050 do seu servidor (ex:
                            http://seu-ip:3050/dashboard).</p>
                    </div>
                </div>

                <!-- Configurações de Mensagens -->
                <div class="card" style="grid-column: 1 / -1;">
                    <div class="card-header">
                        <h2>Modelos de Mensagens (WhatsApp)</h2>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Aviso 5 dias antes</label>
                        <textarea name="config_msg_vencimento_5dias" class="form-control"
                            rows="2"><?= htmlspecialchars($configs['msg_vencimento_5dias'] ?? '') ?></textarea>
                        <small>Variáveis disponíveis: {vendedor}, {cliente}, {valor}, {vencimento},
                            {link_pagamento}</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Aviso no dia do vencimento</label>
                        <textarea name="config_msg_vencimento_hoje" class="form-control"
                            rows="2"><?= htmlspecialchars($configs['msg_vencimento_hoje'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Aviso diário de atraso</label>
                        <textarea name="config_msg_vencimento_atrasado" class="form-control"
                            rows="2"><?= htmlspecialchars($configs['msg_vencimento_atrasado'] ?? '') ?></textarea>
                    </div>

                    <div style="margin-top: 1rem; text-align: right;">
                        <button type="submit" class="btn btn-primary">Salvar Configurações</button>
                    </div>
                </div>
            </form>
        </main>
    </div>
</body>

</html>