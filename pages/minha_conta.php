<?php
require_once '../includes/autenticacao.php';
require_once '../config/db.php';

$userId = $_SESSION['user_id'];

// Atualizar dados
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'update_profile') {
        $name = $_POST['nome'];
        $email = $_POST['email'];
        $phone = $_POST['telefone'];
        $cpf_cnpj = $_POST['cpf_cnpj'];

        $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ?, telefone = ?, cpf_cnpj = ? WHERE id = ?");
        $stmt->execute([$name, $email, $phone, $cpf_cnpj, $userId]);

        $_SESSION['user_nome'] = $name;
        $success = "Perfil atualizado com sucesso!";
    }

    if ($_POST['action'] === 'update_bank') {
        $bank_name = $_POST['banco_nome'];
        $bank_agency = $_POST['banco_agencia'];
        $bank_account = $_POST['banco_conta'];
        $bank_account_type = $_POST['banco_tipo_conta'];

        $stmt = $pdo->prepare("UPDATE usuarios SET banco_nome = ?, banco_agencia = ?, banco_conta = ?, banco_tipo_conta = ? WHERE id = ?");
        $stmt->execute([$bank_name, $bank_agency, $bank_account, $bank_account_type, $userId]);

        $success = "Dados bancários atualizados com sucesso!";
    }

    if ($_POST['action'] === 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!password_verify($current_password, $user['senha'])) {
            $error = "Senha atual incorreta.";
        } elseif ($new_password !== $confirm_password) {
            $error = "As senhas não coincidem.";
        } elseif (strlen($new_password) < 6) {
            $error = "A senha deve ter no mínimo 6 caracteres.";
        } else {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
            $stmt->execute([$hashed, $userId]);
            $success = "Senha alterada com sucesso!";
        }
    }
}

// Buscar dados do usuário
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Conta - Gestor de Vendas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="app-container">
        <?php include '../includes/menu_lateral.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <h2 style="font-size: 1.5rem; font-weight: 600;">Minha Conta</h2>
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

                <div
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: var(--spacing-lg);">

                    <!-- Dados Pessoais -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Dados Pessoais</h3>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="action" value="update_profile">
                            <div class="form-group">
                                <label class="form-label">Nome Completo</label>
                                <input type="text" name="name" class="form-control"
                                    value="<?= htmlspecialchars($user['nome']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">E-mail</label>
                                <input type="email" name="email" class="form-control"
                                    value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Telefone</label>
                                <input type="text" name="phone" class="form-control"
                                    value="<?= htmlspecialchars($user['telefone'] ?? '') ?>"
                                    placeholder="(00) 00000-0000">
                            </div>
                            <div class="form-group">
                                <label class="form-label">CPF/CNPJ</label>
                                <input type="text" name="cpf_cnpj" class="form-control"
                                    value="<?= htmlspecialchars($user['cpf_cnpj'] ?? '') ?>"
                                    placeholder="000.000.000-00">
                            </div>
                            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                        </form>
                    </div>

                    <!-- Dados Bancários -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Dados Bancários</h3>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="action" value="update_bank">
                            <div class="form-group">
                                <label class="form-label">Banco (Código)</label>
                                <input type="text" name="bank_name" class="form-control"
                                    value="<?= htmlspecialchars($user['banco_nome'] ?? '') ?>"
                                    placeholder="Ex: 001 (Banco do Brasil)">
                                <small style="color: var(--text-muted);">Informe o código do banco (3 dígitos)</small>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label class="form-label">Agência</label>
                                    <input type="text" name="bank_agency" class="form-control"
                                        value="<?= htmlspecialchars($user['banco_agencia'] ?? '') ?>"
                                        placeholder="0000">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Conta</label>
                                    <input type="text" name="bank_account" class="form-control"
                                        value="<?= htmlspecialchars($user['banco_conta'] ?? '') ?>"
                                        placeholder="00000-0">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tipo de Conta</label>
                                <select name="bank_account_type" class="form-control">
                                    <option value="checking" <?= ($user['banco_tipo_conta'] ?? '') === 'corrente' ? 'selected' : '' ?>>Conta Corrente</option>
                                    <option value="savings" <?= ($user['banco_tipo_conta'] ?? '') === 'poupanca' ? 'selected' : '' ?>>Conta Poupança</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Salvar Dados Bancários</button>
                        </form>
                    </div>

                    <!-- Alterar Senha -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Alterar Senha</h3>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="action" value="change_password">
                            <div class="form-group">
                                <label class="form-label">Senha Atual</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nova Senha</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Confirmar Nova Senha</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Alterar Senha</button>
                        </form>
                    </div>

                    <!-- Informações da Conta -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Informações da Conta</h3>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div>
                                <div style="color: var(--text-muted); font-size: 0.875rem;">Tipo de Conta</div>
                                <div style="font-weight: 600; margin-top: 0.25rem;">
                                    <?= $user['tipo'] === 'admin' ? 'Administrador' : 'Vendedor' ?>
                                </div>
                            </div>
                            <div>
                                <div style="color: var(--text-muted); font-size: 0.875rem;">Membro desde</div>
                                <div style="font-weight: 600; margin-top: 0.25rem;">
                                    <?= date('d/m/Y', strtotime($user['criado_em'])) ?>
                                </div>
                            </div>
                            <?php if ($user['tipo'] === 'vendedor'): ?>
                                <div>
                                    <div style="color: var(--text-muted); font-size: 0.875rem;">Taxa de Comissão</div>
                                    <div style="font-weight: 600; margin-top: 0.25rem;">
                                        <?= number_format($user['taxa_comissao'] ?? 10, 2, ',', '.') ?>%
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

            </div>
        </main>
    </div>
</body>

</html>