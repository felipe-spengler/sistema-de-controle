<?php
require_once '../includes/autenticacao.php';
require_once '../config/db.php';
require_once '../includes/asaas.php';

$userId = $_SESSION['user_id'];
$is_admin = isAdmin();

// Buscar dados bancários do usuário
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// ========================================
// CÁLCULO CORRETO DO SALDO COM COMISSÃO
// ========================================

// Calcular total recebido (considerando o percentual do vendedor)
$query = "SELECT 
            SUM(f.valor * (u.taxa_comissao / 100)) as total_comissao
          FROM faturas f
          JOIN clientes c ON f.cliente_id = c.id
          JOIN usuarios u ON c.vendedor_id = u.id
          WHERE f.status = 'pago' AND c.vendedor_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$userId]);
$totalRecebidoComComissao = $stmt->fetch()['total_comissao'] ?? 0;

// Total já sacado
$stmt = $pdo->prepare("SELECT SUM(valor) as total_withdrawn FROM saques WHERE usuario_id = ? AND status IN ('aprovado', 'pago')");
$stmt->execute([$userId]);
$totalWithdrawn = $stmt->fetch()['total_withdrawn'] ?? 0;

// Total pendente
$stmt = $pdo->prepare("SELECT SUM(valor) as total_pending FROM saques WHERE usuario_id = ? AND status = 'pendente'");
$stmt->execute([$userId]);
$totalPending = $stmt->fetch()['total_pending'] ?? 0;

// Saldo disponível = Total recebido (com comissão) - Sacado - Pendente
$availableBalance = $totalRecebidoComComissao - $totalWithdrawn - $totalPending;

// Buscar detalhes das comissões (para exibir)
$query = "SELECT 
            c.razao_social,
            u.taxa_comissao as percentual_vendedor,
            SUM(f.valor) as total_faturado,
            SUM(f.valor * (u.taxa_comissao / 100)) as total_comissao
          FROM faturas f
          JOIN clientes c ON f.cliente_id = c.id
          JOIN usuarios u ON c.vendedor_id = u.id
          WHERE f.status = 'pago' AND c.vendedor_id = ?
          GROUP BY c.id, c.razao_social, u.taxa_comissao";
$stmt = $pdo->prepare($query);
$stmt->execute([$userId]);
$detalhesComissoes = $stmt->fetchAll();

// Processar solicitação de saque
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request') {
    $amount = floatval($_POST['amount']);

    if ($amount <= 0) {
        $error = "Valor inválido.";
    } elseif ($amount > $availableBalance) {
        $error = "Saldo insuficiente.";
    } elseif (empty($user['chave_pix']) || !$user['cpf_cnpj']) {
        $error = "Cadastre sua Chave Pix e CPF/CNPJ antes de solicitar um saque.";
    } else {
        try {
            // Salvar no banco de dados
            $stmt = $pdo->prepare("INSERT INTO saques (usuario_id, valor, status) VALUES (?, ?, 'pendente')");
            $stmt->execute([$userId, $amount]);

            $success = "Saque solicitado com sucesso! Aguarde a aprovação.";

            // Atualizar saldos
            $availableBalance -= $amount;
            $totalPending += $amount;

        } catch (Exception $e) {
            $error = "Erro ao processar saque: " . $e->getMessage();
        }
    }
}

// Buscar histórico de saques
$stmt = $pdo->prepare("SELECT * FROM saques WHERE usuario_id = ? ORDER BY solicitado_em DESC");
$stmt->execute([$userId]);
$withdrawals = $stmt->fetchAll();

// Se for admin, buscar todos os saques
if ($is_admin) {
    $stmt = $pdo->query("SELECT w.*, u.nome as user_name FROM saques w JOIN usuarios u ON w.usuario_id = u.id ORDER BY w.solicitado_em DESC");
    $withdrawals = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saques - Gestor de Vendas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="app-container">
        <?php include '../includes/menu_lateral.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <h2 style="font-size: 1.5rem; font-weight: 600;">Saques</h2>
            </header>

            <div class="page-content">

                <?php if (isset($error)): ?>
                    <div
                        style="background-color: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                    <div
                        style="background-color: #dcfce7; color: #166534; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                        <?= $success ?>
                    </div>
                <?php endif; ?>

                <!-- Saldo Cards -->
                <div
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: var(--spacing-lg); margin-bottom: var(--spacing-xl);">

                    <div class="card" style="border-left: 4px solid var(--success);">
                        <div class="card-title" style="color: var(--text-muted); font-size: 0.875rem;">Saldo Disponível
                            (Comissões)</div>
                        <div
                            style="font-size: 2rem; font-weight: 700; margin-top: var(--spacing-sm); color: var(--success);">
                            R$ <?= number_format($availableBalance, 2, ',', '.') ?>
                        </div>
                    </div>

                    <div class="card" style="border-left: 4px solid var(--warning);">
                        <div class="card-title" style="color: var(--text-muted); font-size: 0.875rem;">Saques Pendentes
                        </div>
                        <div
                            style="font-size: 2rem; font-weight: 700; margin-top: var(--spacing-sm); color: var(--warning);">
                            R$ <?= number_format($totalPending, 2, ',', '.') ?>
                        </div>
                    </div>

                    <div class="card" style="border-left: 4px solid var(--primary-color);">
                        <div class="card-title" style="color: var(--text-muted); font-size: 0.875rem;">Total Sacado
                        </div>
                        <div style="font-size: 2rem; font-weight: 700; margin-top: var(--spacing-sm);">
                            R$ <?= number_format($totalWithdrawn, 2, ',', '.') ?>
                        </div>
                    </div>

                    <div class="card" style="border-left: 4px solid var(--info);">
                        <div class="card-title" style="color: var(--text-muted); font-size: 0.875rem;">Total Recebido
                            (Comissões)</div>
                        <div style="font-size: 2rem; font-weight: 700; margin-top: var(--spacing-sm);">
                            R$ <?= number_format($totalRecebidoComComissao, 2, ',', '.') ?>
                        </div>
                    </div>

                </div>

                <!-- Detalhamento de Comissões -->
                <?php if (!$is_admin && !empty($detalhesComissoes)): ?>
                    <div class="card" style="margin-bottom: var(--spacing-lg);">
                        <div class="card-header">
                            <h3 class="card-title">Detalhamento de Comissões por Cliente</h3>
                        </div>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>% Comissão</th>
                                        <th>Total Faturado</th>
                                        <th>Sua Comissão</th>
                                        <th>Comissão Admin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($detalhesComissoes as $detalhe): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($detalhe['razao_social']) ?></td>
                                            <td><span
                                                    class="badge badge-info"><?= number_format($detalhe['percentual_vendedor'], 0) ?>%</span>
                                            </td>
                                            <td>R$ <?= number_format($detalhe['total_faturado'], 2, ',', '.') ?></td>
                                            <td style="color: var(--success); font-weight: 600;">R$
                                                <?= number_format($detalhe['total_comissao'], 2, ',', '.') ?>
                                            </td>
                                            <td style="color: var(--text-muted);">R$
                                                <?= number_format($detalhe['total_faturado'] - $detalhe['total_comissao'], 2, ',', '.') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Solicitar Saque -->
                <?php if (!$is_admin): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Solicitar Saque</h3>
                        </div>
                        <form method="POST" style="max-width: 400px;">
                            <input type="hidden" name="action" value="request">
                            <div class="form-group">
                                <label class="form-label">Valor do Saque (R$)</label>
                                <input type="number" step="0.01" name="amount" class="form-control"
                                    max="<?= $availableBalance ?>" placeholder="0,00" required>
                                <small style="color: var(--text-muted);">Saldo disponível: R$
                                    <?= number_format($availableBalance, 2, ',', '.') ?></small>
                            </div>

                            <?php if (empty($user['chave_pix'])): ?>
                                <div
                                    style="background-color: #fef3c7; color: #92400e; padding: 0.75rem; border-radius: 0.375rem; margin-bottom: 1rem;">
                                    ⚠️ Cadastre sua <a href="minha_conta.php" style="text-decoration: underline;">Chave Pix</a>
                                    em Minha Conta antes de solicitar um saque.
                                </div>
                            <?php endif; ?>

                            <button type="submit" class="btn btn-primary" <?= empty($user['chave_pix']) ? 'disabled' : '' ?>>
                                Solicitar Saque
                            </button>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- Histórico de Saques -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Histórico de Saques</h3>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <?php if ($is_admin): ?>
                                        <th>Vendedor</th><?php endif; ?>
                                    <th>Data Solicitação</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                    <th>Data Processamento</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($withdrawals as $w): ?>
                                    <tr>
                                        <?php if ($is_admin): ?>
                                            <td><?= htmlspecialchars($w['user_name']) ?></td><?php endif; ?>
                                        <td><?= date('d/m/Y H:i', strtotime($w['solicitado_em'])) ?></td>
                                        <td>R$ <?= number_format($w['valor'], 2, ',', '.') ?></td>
                                        <td>
                                            <span class="badge badge-<?=
                                                $w['status'] === 'pago' ? 'success' :
                                                ($w['status'] === 'aprovado' ? 'info' :
                                                    ($w['status'] === 'rejeitado' ? 'danger' : 'warning'))
                                                ?>">
                                                <?=
                                                    $w['status'] === 'pago' ? 'Pago' :
                                                    ($w['status'] === 'aprovado' ? 'Aprovado' :
                                                        ($w['status'] === 'rejeitado' ? 'Rejeitado' : 'Pendente'))
                                                    ?>
                                            </span>
                                        </td>
                                        <td><?= $w['processado_em'] ? date('d/m/Y H:i', strtotime($w['processado_em'])) : '-' ?>
                                        </td>
                                        <td>
                                            <?php if ($is_admin && $w['status'] === 'pendente'): ?>
                                                <button class="btn"
                                                    style="padding: 0.25rem 0.5rem; font-size: 0.8rem; background: #dcfce7; color: #166534;">Aprovar</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <?php if (empty($withdrawals)): ?>
                                    <tr>
                                        <td colspan="<?= $is_admin ? 6 : 5 ?>"
                                            style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                            Nenhum saque encontrado.
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