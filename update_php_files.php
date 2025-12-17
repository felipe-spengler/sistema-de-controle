<?php
/**
 * Script para atualizar TODOS os arquivos PHP
 * Substitui nomes de tabelas e colunas em inglês por português
 */

$replacements = [
    // Tabelas
    'FROM users' => 'FROM usuarios',
    'JOIN users' => 'JOIN usuarios',
    'INTO users' => 'INTO usuarios',
    'UPDATE users' => 'UPDATE usuarios',
    'FROM clients' => 'FROM clientes',
    'JOIN clients' => 'JOIN clientes',
    'INTO clients' => 'INTO clientes',
    'UPDATE clients' => 'UPDATE clientes',
    'FROM invoices' => 'FROM faturas',
    'JOIN invoices' => 'JOIN faturas',
    'INTO invoices' => 'INTO faturas',
    'UPDATE invoices' => 'UPDATE faturas',
    'FROM withdrawals' => 'FROM saques',
    'JOIN withdrawals' => 'JOIN saques',
    'INTO withdrawals' => 'INTO saques',
    'UPDATE withdrawals' => 'UPDATE saques',

    // Colunas - usuarios
    "['name']" => "['nome']",
    '["name"]' => '["nome"]',
    "['password']" => "['senha']",
    '["password"]' => '["senha"]',
    "['role']" => "['tipo']",
    '["role"]' => '["tipo"]',
    "['created_at']" => "['criado_em']",
    '["created_at"]' => '["criado_em"]',
    "['bank_name']" => "['banco_nome']",
    "['bank_agency']" => "['banco_agencia']",
    "['bank_account']" => "['banco_conta']",
    "['bank_account_type']" => "['banco_tipo_conta']",
    "['phone']" => "['telefone']",
    "['commission_rate']" => "['taxa_comissao']",

    // Colunas - clientes
    "['seller_id']" => "['vendedor_id']",
    "['company_name']" => "['razao_social']",
    "['software_type']" => "['tipo_software']",
    "['plan']" => "['plano']",
    "['monthly_fee']" => "['mensalidade']",

    // Colunas - faturas
    "['client_id']" => "['cliente_id']",
    "['due_date']" => "['data_vencimento']",
    "['payment_date']" => "['data_pagamento']",
    "['amount']" => "['valor']",
    "['asaas_payment_id']" => "['asaas_pagamento_id']",
    "['payment_url']" => "['url_pagamento']",

    // Colunas - saques
    "['user_id']" => "['usuario_id']",
    "['requested_at']" => "['solicitado_em']",
    "['processed_at']" => "['processado_em']",

    // SQL Columns
    'u.name' => 'u.nome',
    'c.seller_id' => 'c.vendedor_id',
    'c.company_name' => 'c.razao_social',
    'c.monthly_fee' => 'c.mensalidade',
    'c.created_at' => 'c.criado_em',
    'i.client_id' => 'i.cliente_id',
    'i.due_date' => 'i.data_vencimento',
    'i.payment_date' => 'i.data_pagamento',
    'i.amount' => 'i.valor',
    'i.created_at' => 'i.criado_em',
    'w.user_id' => 'w.usuario_id',
    'w.amount' => 'w.valor',
    'w.requested_at' => 'w.solicitado_em',
    'w.processed_at' => 'w.processado_em',

    // Status values
    "'active'" => "'ativo'",
    "'inactive'" => "'inativo'",
    "'pending'" => "'pendente'",
    "'paid'" => "'pago'",
    "'overdue'" => "'atrasado'",
    "'approved'" => "'aprovado'",
    "'rejected'" => "'rejeitado'",
    "'admin'" => "'admin'", // mantém
    "'seller'" => "'vendedor'",
    "'checking'" => "'corrente'",
    "'savings'" => "'poupanca'",

    // Session variables
    "\$_SESSION['user_name']" => "\$_SESSION['user_nome']",
    "\$_SESSION['user_role']" => "\$_SESSION['user_tipo']",
];

$files = [
    'pages/dashboard.php',
    'pages/clients.php',
    'pages/invoices.php',
    'pages/sellers.php',
    'pages/withdrawals.php',
    'pages/subscriptions.php',
    'pages/statement.php',
    'pages/my_account.php',
];

echo "<h2>🔄 Atualizando arquivos PHP</h2>";
echo "<pre>";

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "⚠️  Arquivo não encontrado: $file\n";
        continue;
    }

    $content = file_get_contents($file);
    $original = $content;

    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }

    if ($content !== $original) {
        file_put_contents($file, $content);
        echo "✅ Atualizado: $file\n";
    } else {
        echo "⏭️  Sem alterações: $file\n";
    }
}

echo "\n✅✅✅ TODOS OS ARQUIVOS ATUALIZADOS! ✅✅✅\n";
echo "</pre>";
?>