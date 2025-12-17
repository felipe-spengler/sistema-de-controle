<?php
require_once '../includes/autenticacao.php';
require_once '../config/db.php';

if (!isAdmin()) {
    header("Location: dashboard.php");
    exit;
}

// Handle Add Seller
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, 'vendedor')");
    try {
        $stmt->execute([$name, $email, $password]);
    } catch (PDOException $e) {
        $error = "Erro ao criar vendedor: " . $e->getMessage();
    }

    header("Location: vendedores.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM usuarios WHERE tipo = 'vendedor' ORDER BY criado_em DESC");
$sellers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendedores - Gestor de Vendas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="app-container">
        <?php include '../includes/menu_lateral.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <h2 style="font-size: 1.5rem; font-weight: 600;">Vendedores</h2>
                <button onclick="document.getElementById('addSellerModal').style.display='flex'" class="btn btn-primary"
                    style="width: auto;">
                    + Novo Vendedor
                </button>
            </header>

            <div class="page-content">
                <div class="card">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>E-mail</th>
                                    <th>Data Cadastro</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sellers as $seller): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($seller['nome']) ?></td>
                                        <td><?= htmlspecialchars($seller['email']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($seller['criado_em'])) ?></td>
                                        <td>
                                            <a href="painel.php?view_seller_id=<?= $seller['id'] ?>" class="btn"
                                                style="padding: 0.25rem 0.5rem; font-size: 0.8rem; background: #f1f5f9; color: var(--text-main); text-decoration: none; display: inline-block;">Acessar
                                                Painel</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <?php if (empty($sellers)): ?>
                                    <tr>
                                        <td colspan="4"
                                            style="text-align: center; padding: 2rem; color: var(--text-muted);">Nenhum
                                            vendedor cadastrado.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Novo Vendedor -->
    <div id="addSellerModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
        <div class="card" style="width: 100%; max-width: 400px; margin: 0;">
            <div class="card-header">
                <h3 class="card-title">Novo Vendedor</h3>
                <button onclick="document.getElementById('addSellerModal').style.display='none'"
                    style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label class="form-label">Nome</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Senha</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Salvar Vendedor</button>
            </form>
        </div>
    </div>
</body>

</html>