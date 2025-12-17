<?php
require_once '../includes/autenticacao.php';
require_once '../config/db.php';
require_once '../includes/asaas.php';

$userId = $_SESSION['usuario_id'];
$is_admin = isAdmin();

// Handle Invoice Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // Criar fatura
    if ($_POST['action'] === 'create') {
        $client_id = $_POST['cliente_id'];
        $amount = $_POST['valor'];
        $due_date = $_POST['data_vencimento'];

        $stmt = $pdo->prepare("INSERT INTO faturas (client_id, amount, due_date, status) VALUES (?, ?, ?, 'pendente')");
        $stmt->execute([$client_id, $amount, $due_date]);

        header("Location: faturas.php?success=created");
        exit;
    }

    // Baixar fatura (marcar como paga)
    if ($_POST['action'] === 'pay') {
        $invoice_id = $_POST['invoice_id'];

        $stmt = $pdo->prepare("UPDATE faturas SET status = 'pago', payment_date = NOW() WHERE id = ?");
        $stmt->execute([$invoice_id]);

        header("Location: faturas.php?success=paid");
        exit;
    }
}

// Fetch Invoices
$query = "SELECT i.*, c.razao_social, c.cnpj FROM faturas i JOIN clientes c ON i.cliente_id = c.id";
if (!$is_admin) {
    $query .= " WHERE c.vendedor_id = $userId";
}
$query .= " ORDER BY i.data_vencimento ASC";

$stmt = $pdo->query($query);
$invoices = $stmt->fetchAll();

// Fetch Clients for Dropdown
$clientQuery = "SELECT id, company_name FROM clientes";
if (!$is_admin) {
    $clientQuery .= " WHERE seller_id = $userId";
}
$stmt = $pdo->query($clientQuery);
$clients = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faturas - Gestor de Vendas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="app-container">
        <?php include '../includes/menu_lateral.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <h2 style="font-size: 1.5rem; font-weight: 600;">Faturas</h2>
                <button onclick="document.getElementById('addInvoiceModal').style.display='flex'"
                    class="btn btn-primary" style="width: auto;">
                    + Nova Fatura
                </button>
            </header>

            <div class="page-content">

                <?php if (isset($_GET['success'])): ?>
                    <div
                        style="background-color: #dcfce7; color: #166534; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                        <?= $_GET['success'] === 'created' ? 'Fatura criada com sucesso!' : 'Fatura baixada com sucesso!' ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Vencimento</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                    <th>Pagamento</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($invoices as $inv): ?>
                                    <tr>
                                        <td>
                                            <div style="font-weight: 500;"><?= htmlspecialchars($inv['razao_social']) ?>
                                            </div>
                                            <div style="font-size: 0.8rem; color: var(--text-muted);">
                                                <?= htmlspecialchars($inv['cnpj']) ?></div>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($inv['data_vencimento'])) ?></td>
                                        <td>R$ <?= number_format($inv['valor'], 2, ',', '.') ?></td>
                                        <td>
                                            <span
                                                class="badge badge-<?= $inv['status'] === 'pago' ? 'success' : ($inv['status'] === 'pendente' ? 'warning' : 'danger') ?>">
                                                <?= $inv['status'] === 'pago' ? 'Pago' : ($inv['status'] === 'pendente' ? 'Pendente' : 'Atrasado') ?>
                                            </span>
                                        </td>
                                        <td><?= $inv['data_pagamento'] ? date('d/m/Y', strtotime($inv['data_pagamento'])) : '-' ?>
                                        </td>
                                        <td>
                                            <?php if ($inv['status'] !== 'pago'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="pay">
                                                    <input type="hidden" name="invoice_id" value="<?= $inv['id'] ?>">
                                                    <button type="submit" class="btn"
                                                        style="padding: 0.25rem 0.5rem; font-size: 0.8rem; background: #dcfce7; color: #166534;"
                                                        onclick="return confirm('Confirmar baixa desta fatura?')">
                                                        Baixar
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <?php if (empty($invoices)): ?>
                                    <tr>
                                        <td colspan="6"
                                            style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                            Nenhuma fatura encontrada.
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

    <!-- Modal Nova Fatura -->
    <div id="addInvoiceModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000;">
        <div class="card" style="width: 100%; max-width: 400px; margin: 0;">
            <div class="card-header">
                <h3 class="card-title">Nova Fatura</h3>
                <button onclick="document.getElementById('addInvoiceModal').style.display='none'"
                    style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <div class="form-group">
                    <label class="form-label">Cliente</label>
                    <select name="client_id" class="form-control" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($clients as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['razao_social']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Valor (R$)</label>
                    <input type="number" step="0.01" name="amount" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Vencimento</label>
                    <input type="date" name="due_date" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Gerar Fatura</button>
            </form>
        </div>
    </div>
</body>

</html>