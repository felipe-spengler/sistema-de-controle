<?php
require_once '../includes/autenticacao.php';
require_once '../config/db.php';

// Fetch stats
$userId = $_SESSION['user_id'];
$is_admin = isAdmin();
$viewingSeller = false;
$sellerName = '';

// Check if admin is viewing a specific seller
if ($is_admin && isset($_GET['view_seller_id'])) {
    $targetId = $_GET['view_seller_id'];

    // Verify seller exists
    $stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ? AND tipo = 'vendedor'");
    $stmt->execute([$targetId]);
    $seller = $stmt->fetch();

    if ($seller) {
        $viewingSeller = true;
        $userId = $targetId; // Impersonate for stats
        $sellerName = $seller['nome'];
    }
}

// Base query conditions
// If viewing a seller, treating it like a normal seller view (not admin view)
$useFilters = !$is_admin || $viewingSeller;

$sellerCondition = ($useFilters) ? "vendedor_id = $userId" : "1=1";

// 1. Total Clients
$stmt = $pdo->query("SELECT COUNT(*) FROM clientes WHERE $sellerCondition");
$totalClients = $stmt->fetchColumn();

// 2. Active Subscriptions
$stmt = $pdo->query("SELECT COUNT(*) FROM clientes WHERE status = 'ativo' AND $sellerCondition");
$activeSubs = $stmt->fetchColumn();

// 3. Pending Invoices Count
$query = (!$useFilters)
    ? "SELECT COUNT(*) FROM faturas WHERE status = 'pendente'"
    : "SELECT COUNT(*) FROM faturas i JOIN clientes c ON i.cliente_id = c.id WHERE c.vendedor_id = $userId AND i.status = 'pendente'";
$stmt = $pdo->query($query);
$pendingInvoices = $stmt->fetchColumn();

// 4. Monthly Revenue (Paid invoices this month)
$currentMonth = date('Y-m');
$query = (!$useFilters)
    ? "SELECT SUM(valor) FROM faturas WHERE status = 'pago' AND DATE_FORMAT(data_pagamento, '%Y-%m') = '$currentMonth'"
    : "SELECT SUM(i.valor) FROM faturas i JOIN clientes c ON i.cliente_id = c.id WHERE c.vendedor_id = $userId AND i.status = 'pago' AND DATE_FORMAT(i.data_pagamento, '%Y-%m') = '$currentMonth'";
$stmt = $pdo->query($query);
$monthlyRevenue = $stmt->fetchColumn() ?: 0;

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gestor de Vendas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="app-container">
        <?php include '../includes/menu_lateral.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <h2 style="font-size: 1.5rem; font-weight: 600;">Dashboard</h2>
                <div class="user-info">
                    <span>Olá, <strong><?= htmlspecialchars($_SESSION['user_nome']) ?></strong></span>
                </div>
            </header>

            <div class="page-content">
                <?php if ($viewingSeller): ?>
                    <div
                        style="background-color: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
                        <span>Você está visualizando o painel do vendedor:
                            <strong><?= htmlspecialchars($sellerName) ?></strong></span>
                        <a href="painel.php" class="btn"
                            style="padding: 0.25rem 0.75rem; font-size: 0.875rem; background: #fff; color: #1e40af; border: 1px solid #bfdbfe;">Voltar
                            para Visão Geral</a>
                    </div>
                <?php endif; ?>

                <!-- Stats Grid -->
                <div
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: var(--spacing-lg); margin-bottom: var(--spacing-xl);">

                    <div class="card" style="border-left: 4px solid var(--primary-color);">
                        <div class="card-title" style="color: var(--text-muted); font-size: 0.875rem;">Total de Clientes
                        </div>
                        <div style="font-size: 2rem; font-weight: 700; margin-top: var(--spacing-sm);">
                            <?= $totalClients ?>
                        </div>
                    </div>

                    <div class="card" style="border-left: 4px solid var(--success);">
                        <div class="card-title" style="color: var(--text-muted); font-size: 0.875rem;">Assinaturas
                            Ativas</div>
                        <div style="font-size: 2rem; font-weight: 700; margin-top: var(--spacing-sm);">
                            <?= $activeSubs ?>
                        </div>
                    </div>

                    <div class="card" style="border-left: 4px solid var(--warning);">
                        <div class="card-title" style="color: var(--text-muted); font-size: 0.875rem;">Faturas Pendentes
                        </div>
                        <div style="font-size: 2rem; font-weight: 700; margin-top: var(--spacing-sm);">
                            <?= $pendingInvoices ?>
                        </div>
                    </div>

                    <div class="card" style="border-left: 4px solid var(--info);">
                        <div class="card-title" style="color: var(--text-muted); font-size: 0.875rem;">Receita Mensal
                        </div>
                        <div style="font-size: 2rem; font-weight: 700; margin-top: var(--spacing-sm);">R$
                            <?= number_format($monthlyRevenue, 2, ',', '.') ?>
                        </div>
                    </div>

                </div>

                <!-- Recent Activity / Placeholder -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Visão Geral</h3>
                    </div>
                    <div
                        style="height: 300px; display: flex; align-items: center; justify-content: center; background-color: #f8fafc; border-radius: var(--radius-sm); border: 2px dashed #e2e8f0;">
                        <p style="color: var(--text-muted);">Gráfico de vendas será exibido aqui</p>
                    </div>
                </div>

            </div>
        </main>
    </div>
</body>

</html>