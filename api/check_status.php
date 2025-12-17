<?php
// API simples de Verificação de Licença
// Pode ser consumida por sistemas externos usando cURL
// URL: /api/check_status.php?cpf_cnpj=00000000000

require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_GET['cpf_cnpj'])) {
    http_response_code(400);
    echo json_encode(['error' => 'CPF/CNPJ não fornecido']);
    exit;
}

$cpf_cnpj = preg_replace('/[^0-9]/', '', $_GET['cpf_cnpj']);

try {
    // Buscar usuário pelo CPF/CNPJ
    $stmt = $pdo->prepare("SELECT id, razao_social FROM clientes WHERE cpf_cnpj = ?");
    $stmt->execute([$cpf_cnpj]);
    $cliente = $stmt->fetch();

    if (!$cliente) {
        echo json_encode(['status' => 'not_found', 'message' => 'Cliente não encontrado']);
        exit;
    }

    // Buscar faturas vencidas há mais de 5 dias (tolerância)
    $hoje = date('Y-m-d');
    $stmtFaturas = $pdo->prepare("
        SELECT COUNT(*) as atrasadas 
        FROM faturas 
        WHERE cliente_id = ? 
        AND status = 'pendente' 
        AND data_vencimento < DATE_SUB(?, INTERVAL 5 DAY)
    ");
    $stmtFaturas->execute([$cliente['id'], $hoje]);
    $faturas = $stmtFaturas->fetch();

    if ($faturas['atrasadas'] > 0) {
        echo json_encode([
            'status' => 'blocked',
            'message' => 'Suspenso por inadimplência. Entre em contato para regularizar.',
            'cliente' => $cliente['razao_social']
        ]);
    } else {
        echo json_encode([
            'status' => 'active',
            'message' => 'Licença Ativa',
            'cliente' => $cliente['razao_social']
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?>