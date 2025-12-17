<?php
require_once '../includes/autenticacao.php';
require_once '../config/db.php';

$userId = $_SESSION['user_id'];
$is_admin = isAdmin();

// Filtros
$filter_type = $_GET['type'] ?? 'all'; // all, income, expense
$filter_period = $_GET['period'] ?? 'month'; // week, month, year, all

// Construir query baseado nos filtros
$transactions = [];

// 1. Faturas Pagas (Receitas)
$query = "SELECT 
            i.id,
            'income' as type,
            'Fatura Paga' as description,
            c.razao_social as related_to,
            i.valor,
            i.data_pagamento as date,
            i.criado_em
          FROM faturas i
          JOIN clientes c ON i.cliente_id = c.id
          WHERE i.status = 'pago'";

if (!$is_admin) {
    $query .= " AND c.vendedor_id = $userId";
}

// Aplicar filtro de período
if ($filter_period === 'week') {
    $query .= " AND i.data_pagamento >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
} elseif ($filter_period === 'month') {
    $query .= " AND i.data_pagamento >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
} elseif ($filter_period === 'year') {
    $query .= " AND i.data_pagamento >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
}

if ($filter_type === 'all' || $filter_type === 'income') {
    $stmt = $pdo->query($query);
    $transactions = array_merge($transactions, $stmt->fetchAll());
}

// 2. Saques (Despesas)
$query = "SELECT 
            w.id,
            'expense' as type,
            'Saque Realizado' as description,
            u.nome as related_to,
            w.valor,
            w.processado_em as date,
            w.solicitado_em as created_at
          FROM saques w
          JOIN usuarios u ON w.usuario_id = u.id
          WHERE w.status IN ('aprovado', 'pago')";

if (!$is_admin) {
    $query .= " AND w.usuario_id = $userId";
}

// Aplicar filtro de período
if ($filter_period === 'week') {
    $query .= " AND w.processado_em >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
} elseif ($filter_period === 'month') {
    $query .= " AND w.processado_em >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
} elseif ($filter_period === 'year') {
    $query .= " AND w.processado_em >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
}

if ($filter_type === 'all' || $filter_type === 'expense') {
    $stmt = $pdo->query($query);
    $transactions = array_merge($transactions, $stmt->fetchAll());
}

// Ordenar por data
usort($transactions, function ($a, $b) {
    return strtotime($b['date'] ?? $b['criado_em']) - strtotime($a['date'] ?? $a['criado_em']);
});

// Calcular totais
$totalIncome = 0;
$totalExpense = 0;

foreach ($transactions as $t) {
    if ($t['type'] === 'income') {
        $totalIncome += $t['valor'];
    } else {
        $totalExpense += $t['valor'];
    }
}

$balance = $totalIncome - $totalExpense;
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Extrato - Gestor de Vendas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="app-container">
        <?php include '../includes/menu_lateral.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <h2 style="font-size: 1.5rem; font-weight: 600;">Extrato Financeiro</h2>
            </header>

            <div class="page-content">

                <!-- Resumo Financeiro -->
                <div
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: var(--spacing-lg); margin-bottom: var(--spacing-xl);">

                    <div class="card" style="border-left: 4px solid var(--success);">
                        <div class="card-title" style="color: var(--text-muted); font-size: 0.875rem;">Total Receitas
                        </div>
                        <div
                            style="font-size: 2rem; font-weight: 700; margin-top: var(--spacing-sm); color: var(--success);">
                            R$ <?= number_format($totalIncome, 2, ',', '.') ?>
                        </div>
                    </div>

                    <div class="card" style="border-left: 4px solid var(--danger);">
                        <div class="card-title" style="color: var(--text-muted); font-size: 0.875rem;">Total Despesas
                        </div>
                        <div
                            style="font-size: 2rem; font-weight: 700; margin-top: var(--spacing-sm); color: var(--danger);">
                            R$ <?= number_format($totalExpense, 2, ',', '.') ?>
                        </div>
                    </div>

                    <div class="card" style="border-left: 4px solid var(--primary-color);">
                        <div class="card-title" style="color: var(--text-muted); font-size: 0.875rem;">Saldo</div>
                        <div
                            style="font-size: 2rem; font-weight: 700; margin-top: var(--spacing-sm); color: <?= $balance >= 0 ? 'var(--success)' : 'var(--danger)' ?>;">
                            R$ <?= number_format($balance, 2, ',', '.') ?>
                        </div>
                    </div>

                </div>

                <!-- Filtros -->
                <div class="card" style="margin-bottom: var(--spacing-lg);">
                    <form method="GET" style="display: flex; gap: 1rem; align-items: end; flex-wrap: wrap;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Tipo</label>
                            <select name="type" class="form-control" style="width: 200px;">
                                <option value="all" <?= $filter_type === 'all' ? 'selected' : '' ?>>Todos</option>
                                <option value="income" <?= $filter_type === 'income' ? 'selected' : '' ?>>Receitas</option>
                                <option value="expense" <?= $filter_type === 'expense' ? 'selected' : '' ?>>Despesas
                                </option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Período</label>
                            <select name="period" class="form-control" style="width: 200px;">
                                <option value="week" <?= $filter_period === 'week' ? 'selected' : '' ?>>Última Semana
                                </option>
                                <option value="month" <?= $filter_period === 'month' ? 'selected' : '' ?>>Último Mês
                                </option>
                                <option value="year" <?= $filter_period === 'year' ? 'selected' : '' ?>>Último Ano</option>
                                <option value="all" <?= $filter_period === 'all' ? 'selected' : '' ?>>Tudo</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: auto;">Filtrar</button>
                    </form>
                </div>

                <!-- Transações -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Transações</h3>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Tipo</th>
                                    <th>Descrição</th>
                                    <th>Relacionado a</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $t): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($t['date'] ?? $t['criado_em'])) ?></td>
                                        <td>
                                            <span class="badge badge-<?= $t['type'] === 'income' ? 'success' : 'danger' ?>">
                                                <?= $t['type'] === 'income' ? 'Receita' : 'Despesa' ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($t['description']) ?></td>
                                        <td><?= htmlspecialchars($t['related_to']) ?></td>
                                        <td
                                            style="font-weight: 600; color: <?= $t['type'] === 'income' ? 'var(--success)' : 'var(--danger)' ?>;">
                                            <?= $t['type'] === 'income' ? '+' : '-' ?> R$
                                            <?= number_format($t['valor'], 2, ',', '.') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <?php if (empty($transactions)): ?>
                                    <tr>
                                        <td colspan="5"
                                            style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                            Nenhuma transação encontrada para os filtros selecionados.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>
</body>

</html>