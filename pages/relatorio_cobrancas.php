<?php
session_start();
require_once '../includes/autenticacao.php';
require_once '../config/db.php';

// Apenas admin
if ($_SESSION['user_tipo'] !== 'admin') {
    header('Location: painel.php');
    exit;
}

// Paginação simples
$limit = 50;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Buscar logs
$stmt = $pdo->prepare("SELECT COUNT(*) FROM logs_cobrancas");
$stmt->execute();
$total_logs = $stmt->fetchColumn();
$total_pages = ceil($total_logs / $limit);

$stmt = $pdo->prepare("SELECT * FROM logs_cobrancas ORDER BY data_hora DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Cobranças - Logs</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            font-weight: 500;
        }

        .badge-enviado {
            background: #dcfce7;
            color: #166534;
        }

        .badge-erro {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-simulado {
            background: #fef9c3;
            color: #854d0e;
        }

        .badge-nao-conectado {
            background: #e5e7eb;
            color: #374151;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        th,
        td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background-color: #f9fafb;
            font-weight: 600;
        }

        tr:hover {
            background-color: #f9fafb;
        }
    </style>
</head>

<body>
    <div class="layout">
        <?php include '../includes/menu_lateral.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <div class="welcome-text">
                    <h1>Logs de Cobrança Automática</h1>
                    <p>Histórico de verificaçes e envios de mensagens</p>
                </div>
                <div class="actions">
                    <a href="configuracoes.php" class="btn btn-secondary">Configurações</a>
                </div>
            </header>

            <div class="card">
                <div class="card-header">
                    <h2>Histórico</h2>
                </div>

                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Data/Hora</th>
                                <th>Tipo</th>
                                <th>Vendedor</th>
                                <th>Cliente</th>
                                <th>Status</th>
                                <th>Erro/Obs</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($log['data_hora'])) ?></td>
                                    <td>
                                        <?php
                                        $tipos = [
                                            '5_dias' => 'Pré-Venc. (5d)',
                                            'hoje' => 'Venc. Hoje',
                                            'atrasado' => 'Atrasado'
                                        ];
                                        echo $tipos[$log['tipo']] ?? $log['tipo'];
                                        ?>
                                    </td>
                                    <td><?= htmlspecialchars($log['vendedor_nome']) ?> <br>
                                        <small><?= htmlspecialchars($log['telefone']) ?></small></td>
                                    <td><?= htmlspecialchars($log['cliente_rs']) ?></td>
                                    <td>
                                        <?php
                                        $statusClass = 'badge-simulado';
                                        if ($log['status'] == 'enviado')
                                            $statusClass = 'badge-enviado';
                                        if ($log['status'] == 'erro_envio')
                                            $statusClass = 'badge-erro';
                                        if ($log['status'] == 'nao_conectado')
                                            $statusClass = 'badge-nao-conectado';
                                        ?>
                                        <span
                                            class="badge <?= $statusClass ?>"><?= ucfirst(str_replace('_', ' ', $log['status'])) ?></span>
                                    </td>
                                    <td style="max-width: 300px; word-wrap: break-word;">
                                        <?php if ($log['erro']): ?>
                                            <span style="color: red;"><?= htmlspecialchars($log['erro']) ?></span>
                                        <?php else: ?>
                                            <small style="color: grey;">OK</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 2rem;">Nenhum registro encontrado.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginação -->
                <?php if ($total_pages > 1): ?>
                    <div style="margin-top: 1rem; display: flex; gap: 0.5rem; justify-content: center;">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?= $i ?>" class="btn <?= $i == $page ? 'btn-primary' : 'btn-secondary' ?>"
                                style="padding: 5px 10px;"><?= $i ?></a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>

            </div>
        </main>
    </div>
</body>

</html>