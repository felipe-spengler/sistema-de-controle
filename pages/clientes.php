<?php
require_once '../includes/autenticacao.php';
require_once '../config/db.php';

$userId = $_SESSION['user_id'];
$is_admin = isAdmin();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // Adicionar cliente
    if ($_POST['action'] === 'add') {
        $razao_social = $_POST['razao_social'];
        $cnpj = $_POST['cnpj'];
        $tipo_software = $_POST['tipo_software'];
        $plano = $_POST['plano'];
        $mensalidade = $_POST['mensalidade'];
        $stmt = $pdo->prepare("INSERT INTO clientes (vendedor_id, razao_social, cnpj, tipo_software, plano, mensalidade, status) VALUES (?, ?, ?, ?, ?, ?, 'ativo')");
        $stmt->execute([$userId, $razao_social, $cnpj, $tipo_software, $plano, $mensalidade]);

        header("Location: clientes.php?success=created");
        exit;
    }

    // Editar cliente
    if ($_POST['action'] === 'edit') {
        $id = $_POST['cliente_id'];
        $razao_social = $_POST['razao_social'];
        $cnpj = $_POST['cnpj'];
        $tipo_software = $_POST['tipo_software'];
        $plano = $_POST['plano'];
        $mensalidade = $_POST['mensalidade'];
        $status = $_POST['status'];
        $stmt = $pdo->prepare("UPDATE clientes SET razao_social = ?, cnpj = ?, tipo_software = ?, plano = ?, mensalidade = ?, status = ? WHERE id = ?");
        $stmt->execute([$razao_social, $cnpj, $tipo_software, $plano, $mensalidade, $status, $id]);

        header("Location: clientes.php?success=updated");
        exit;
    }

    // Excluir cliente
    if ($_POST['action'] === 'delete') {
        $id = $_POST['cliente_id'];

        $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = ?");
        $stmt->execute([$id]);

        header("Location: clientes.php?success=deleted");
        exit;
    }
}

// Buscar cliente para edição
$editClient = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editClient = $stmt->fetch();
}

// Fetch Clients
$query = "SELECT c.*, u.nome as seller_name, u.taxa_comissao as percentual_vendedor FROM clientes c JOIN usuarios u ON c.vendedor_id = u.id";
if (!$is_admin) {
    $query .= " WHERE c.vendedor_id = $userId";
}
$query .= " ORDER BY c.criado_em DESC";

$stmt = $pdo->query($query);
$clients = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - Gestor de Vendas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="app-container">
        <?php include '../includes/menu_lateral.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <h2 style="font-size: 1.5rem; font-weight: 600;">Clientes</h2>
                <button onclick="document.getElementById('addClientModal').style.display='flex'" class="btn btn-primary"
                    style="width: auto;">
                    + Novo Cliente
                </button>
            </header>

            <div class="page-content">

                <?php if (isset($_GET['success'])): ?>
                    <div
                        style="background-color: #dcfce7; color: #166534; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                        <?php
                        $messages = [
                            'created' => 'Cliente criado com sucesso!',
                            'updated' => 'Cliente atualizado com sucesso!',
                            'deleted' => 'Cliente excluído com sucesso!'
                        ];
                        echo $messages[$_GET['success']] ?? 'Operação realizada com sucesso!';
                        ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Razão Social</th>
                                    <th>CNPJ</th>
                                    <th>Software / Plano</th>
                                    <?php if ($is_admin): ?>
                                        <th>Vendedor</th><?php endif; ?>
                                    <th>Mensalidade</th>
                                    <?php if ($is_admin): ?>
                                        <th>% Vendedor</th><?php endif; ?>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clients as $client): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($client['razao_social']) ?></td>
                                        <td><?= htmlspecialchars($client['cnpj']) ?></td>
                                        <td>
                                            <div style="font-weight: 500;"><?= htmlspecialchars($client['tipo_software']) ?>
                                            </div>
                                            <div style="font-size: 0.8rem; color: var(--text-muted);">
                                                <?= htmlspecialchars($client['plano']) ?>
                                            </div>
                                        </td>
                                        <?php if ($is_admin): ?>
                                            <td><?= htmlspecialchars($client['seller_name']) ?></td><?php endif; ?>
                                        <td>R$ <?= number_format($client['mensalidade'], 2, ',', '.') ?></td>
                                        <?php if ($is_admin): ?>
                                            <td><span
                                                    class="badge badge-info"><?= number_format($client['percentual_vendedor'], 0) ?>%</span>
                                            </td>
                                        <?php endif; ?>
                                        <td>
                                            <span
                                                class="badge badge-<?= $client['status'] === 'ativo' ? 'success' : ($client['status'] === 'pendente' ? 'warning' : 'danger') ?>">
                                                <?= $client['status'] === 'ativo' ? 'Ativo' : ($client['status'] === 'pendente' ? 'Pendente' : 'Inativo') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 0.5rem;">
                                                <a href="?edit=<?= $client['id'] ?>" class="btn"
                                                    style="padding: 0.25rem 0.5rem; font-size: 0.8rem; background: #e0f2fe; color: #075985;">Editar</a>
                                                <form method="POST" style="display: inline;"
                                                    onsubmit="return confirm('Tem certeza que deseja excluir este cliente?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="cliente_id" value="<?= $client['id'] ?>">
                                                    <button type="submit" class="btn"
                                                        style="padding: 0.25rem 0.5rem; font-size: 0.8rem; background: #fee2e2; color: #991b1b;">Excluir</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <?php if (empty($clients)): ?>
                                    <tr>
                                        <td colspan="<?= $is_admin ? 8 : 6 ?>"
                                            style="text-align: center; padding: 2rem; color: var(--text-muted);">Nenhum
                                            cliente encontrado.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Adicionar/Editar Cliente -->
    <div id="addClientModal"
        style="display: <?= $editClient ? 'flex' : 'none' ?>; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000;">
        <div class="card" style="width: 100%; max-width: 500px; margin: 0;">
            <div class="card-header">
                <h3 class="card-title"><?= $editClient ? 'Editar Cliente' : 'Novo Cliente' ?></h3>
                <a href="clientes.php"
                    style="background: none; border: none; font-size: 1.5rem; cursor: pointer; text-decoration: none;">&times;</a>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="<?= $editClient ? 'edit' : 'add' ?>">
                <?php if ($editClient): ?>
                    <input type="hidden" name="cliente_id" value="<?= $editClient['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label">Razão Social</label>
                    <input type="text" name="razao_social" class="form-control"
                        value="<?= $editClient ? htmlspecialchars($editClient['razao_social']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">CNPJ</label>
                    <input type="text" name="cnpj" class="form-control"
                        value="<?= $editClient ? htmlspecialchars($editClient['cnpj']) : '' ?>" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Software</label>
                        <input type="text" name="tipo_software" class="form-control" placeholder="Ex: ERP"
                            value="<?= $editClient ? htmlspecialchars($editClient['tipo_software']) : '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Plano</label>
                        <input type="text" name="plano" class="form-control" placeholder="Ex: Básico"
                            value="<?= $editClient ? htmlspecialchars($editClient['plano']) : '' ?>" required>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Mensalidade (R$)</label>
                        <input type="number" step="0.01" name="mensalidade" class="form-control"
                            value="<?= $editClient ? $editClient['mensalidade'] : '' ?>" required>
                    </div>

                </div>
                <?php if ($editClient): ?>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="ativo" <?= $editClient['status'] === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                            <option value="inativo" <?= $editClient['status'] === 'inativo' ? 'selected' : '' ?>>Inativo
                            </option>
                            <option value="pendente" <?= $editClient['status'] === 'pendente' ? 'selected' : '' ?>>Pendente
                            </option>
                        </select>
                    </div>
                <?php endif; ?>
                <button type="submit"
                    class="btn btn-primary"><?= $editClient ? 'Salvar Alterações' : 'Salvar Cliente' ?></button>
            </form>
        </div>
    </div>
</body>

</html>