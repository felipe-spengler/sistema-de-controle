<?php
require_once '../includes/autenticacao.php';
require_once '../config/db.php';

$userId = $_SESSION['usuario_id'];
$is_admin = isAdmin();

// Buscar assinaturas (clientes ativos)
$query = "SELECT c.*, u.nome as seller_name,
          (SELECT COUNT(*) FROM faturas WHERE client_id = c.id AND status = 'pago') as paid_invoices,
          (SELECT COUNT(*) FROM faturas WHERE client_id = c.id AND status = 'pendente') as pending_invoices,
          (SELECT MAX(due_date) FROM faturas WHERE client_id = c.id) as next_renewal
          FROM clientes c 
          JOIN usuarios u ON c.vendedor_id = u.id
          WHERE c.status = 'ativo'";

if (!$is_admin) {
    $query .= " AND c.vendedor_id = $userId";
}

$query .= " ORDER BY c.criado_em DESC";

$stmt = $pdo->query($query);
$subscriptions = $stmt->fetchAll();

// Estatísticas
$totalActive = count($subscriptions);
$totalMonthlyRevenue = array_sum(array_column($subscriptions, 'monthly_fee'));

$stmt = $pdo->prepare("SELECT COUNT(*) FROM clientes WHERE status = 'inativo' AND seller_id = ?");
$stmt->execute([$userId]);
$totalInactive = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assinaturas - Gestor de Vendas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="app-container">
        <?php include '../includes/menu_lateral.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <h2 style="font-size: 1.5rem; font-weight: 600;">Assinaturas</h2>
            </header>

            <div class="page-content">

                <!-- Stats -->
                <div
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: var(--spacing-lg); margin-bottom: var(--spacing-xl);">

                    <div class="card" style="border-left: 4px solid var(--success);">
                        <div class="card-title" style="color: var(--text-muted); font-size: 0.875rem;">Assinaturas
                            Ativas</div>
                        <div style="font-size: 2rem; font-weight: 700; margin-top: var(--spacing-sm);">
                            <?= $totalActive ?></div>
                    </div>

                    <div class="card" style="border-left: 4px solid var(--danger);">
                        <div class="card-title" style="color: var(--text-muted); font-size: 0.875rem;">Assinaturas
                            Inativas</div>
                        <div style="font-size: 2rem; font-weight: 700; margin-top: var(--spacing-sm);">
                            <?= $totalInactive ?></div>
                    </div>

                    <div class="card" style="border-left: 4px solid var(--primary-color);">
                        <div class="card-title" style="color: var(--text-muted); font-size: 0.875rem;">Receita Mensal
                            Recorrente</div>
                        <div style="font-size: 2rem; font-weight: 700; margin-top: var(--spacing-sm);">R$
                            <?= number_format($totalMonthlyRevenue, 2, ',', '.') ?></div>
                    </div>

                </div>

                <!-- Lista de Assinaturas -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Assinaturas Ativas</h3>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Software / Plano</th>
                                    <?php if ($is_admin): ?>
                                        <th>Vendedor</th><?php endif; ?>
                                    <th>Mensalidade</th>
                                    <th>Faturas Pagas</th>
                                    <th>Faturas Pendentes</th>
                                    <th>Próxima Renovação</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subscriptions as $sub): ?>
                                    <tr>
                                        <td>
                                            <div style="font-weight: 500;"><?= htmlspecialchars($sub['razao_social']) ?>
                                            </div>
                                            <div style="font-size: 0.8rem; color: var(--text-muted);">
                                                <?= htmlspecialchars($sub['cnpj']) ?></div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 500;"><?= htmlspecialchars($sub['tipo_software']) ?>
                                            </div>
                                            <div style="font-size: 0.8rem; color: var(--text-muted);">
                                                <?= htmlspecialchars($sub['plano']) ?></div>
                                        </td>
                                        <?php if ($is_admin): ?>
                                            <td><?= htmlspecialchars($sub['seller_name']) ?></td>
                                        <?php endif; ?>
                                        <td>R$ <?= number_format($sub['mensalidade'], 2, ',', '.') ?></td>
                                        <td>
                                            <span class="badge badge-success"><?= $sub['paid_invoices'] ?></span>
                                        </td>
                                        <td>
                                            <span class="badge badge-warning"><?= $sub['pending_invoices'] ?></span>
                                        </td>
                                        <td><?= $sub['next_renewal'] ? date('d/m/Y', strtotime($sub['next_renewal'])) : '-' ?>
                                        </td>
                                        <td>
                                            <button class="btn"
                                                style="padding: 0.25rem 0.5rem; font-size: 0.8rem; background: #f1f5f9; color: var(--text-main);">Ver
                                                Detalhes</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <?php if (empty($subscriptions)): ?>
                                    <tr>
                                        <td colspan="<?= $is_admin ? 8 : 7 ?>"
                                            style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                            Nenhuma assinatura ativa encontrada.
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