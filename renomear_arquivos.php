<?php
/**
 * Script para renomear todos os arquivos para português
 */

echo "<h2>🔄 Renomeando Arquivos para Português</h2>";
echo "<pre>";

// Mapeamento de arquivos antigos → novos
$renames = [
    // Páginas principais
    'pages/dashboard.php' => 'pages/painel.php',
    'pages/clients.php' => 'pages/clientes.php',
    'pages/invoices.php' => 'pages/faturas.php',
    'pages/sellers.php' => 'pages/vendedores.php',
    'pages/withdrawals.php' => 'pages/saques.php',
    'pages/subscriptions.php' => 'pages/assinaturas.php',
    'pages/statement.php' => 'pages/extrato.php',
    'pages/my_account.php' => 'pages/minha_conta.php',

    // Includes
    'includes/auth.php' => 'includes/autenticacao.php',
    'includes/sidebar.php' => 'includes/menu_lateral.php',
];

// Renomear arquivos
foreach ($renames as $old => $new) {
    if (file_exists($old)) {
        if (rename($old, $new)) {
            echo "✅ Renomeado: $old → $new\n";
        } else {
            echo "❌ Erro ao renomear: $old\n";
        }
    } else {
        echo "⚠️  Arquivo não encontrado: $old\n";
    }
}

echo "\n✅ Arquivos renomeados!\n";
echo "\n🔄 Agora atualizando referências nos arquivos...\n\n";

// Atualizar referências nos arquivos
$files_to_update = [
    'index.php',
    'logout.php',
    'pages/painel.php',
    'pages/clientes.php',
    'pages/faturas.php',
    'pages/vendedores.php',
    'pages/saques.php',
    'pages/assinaturas.php',
    'pages/extrato.php',
    'pages/minha_conta.php',
    'includes/autenticacao.php',
    'includes/menu_lateral.php',
];

$replacements = [
    // Includes
    "require_once '../includes/auth.php'" => "require_once '../includes/autenticacao.php'",
    "include '../includes/sidebar.php'" => "include '../includes/menu_lateral.php'",

    // Links de navegação
    'href="dashboard.php"' => 'href="painel.php"',
    'href="clients.php"' => 'href="clientes.php"',
    'href="invoices.php"' => 'href="faturas.php"',
    'href="sellers.php"' => 'href="vendedores.php"',
    'href="withdrawals.php"' => 'href="saques.php"',
    'href="subscriptions.php"' => 'href="assinaturas.php"',
    'href="statement.php"' => 'href="extrato.php"',
    'href="my_account.php"' => 'href="minha_conta.php"',

    // Redirects
    'Location: pages/dashboard.php' => 'Location: pages/painel.php',
    'Location: clients.php' => 'Location: clientes.php',
    'Location: invoices.php' => 'Location: faturas.php',
    'Location: sellers.php' => 'Location: vendedores.php',

    // Comparações de página atual
    "'dashboard.php'" => "'painel.php'",
    "'clients.php'" => "'clientes.php'",
    "'invoices.php'" => "'faturas.php'",
    "'sellers.php'" => "'vendedores.php'",
    "'withdrawals.php'" => "'saques.php'",
    "'subscriptions.php'" => "'assinaturas.php'",
    "'statement.php'" => "'extrato.php'",
    "'my_account.php'" => "'minha_conta.php'",

    // Comparações com ==
    '== "dashboard.php"' => '== "painel.php"',
    '== "clients.php"' => '== "clientes.php"',
    '== "invoices.php"' => '== "faturas.php"',
    '== "sellers.php"' => '== "vendedores.php"',
    '== "withdrawals.php"' => '== "saques.php"',
    '== "subscriptions.php"' => '== "assinaturas.php"',
    '== "statement.php"' => '== "extrato.php"',
    '== "my_account.php"' => '== "minha_conta.php"',
];

foreach ($files_to_update as $file) {
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

echo "\n✅✅✅ RENOMEAÇÃO CONCLUÍDA! ✅✅✅\n";
echo "\n📋 Arquivos renomeados:\n";
echo "- dashboard.php → painel.php\n";
echo "- clients.php → clientes.php\n";
echo "- invoices.php → faturas.php\n";
echo "- sellers.php → vendedores.php\n";
echo "- withdrawals.php → saques.php\n";
echo "- subscriptions.php → assinaturas.php\n";
echo "- statement.php → extrato.php\n";
echo "- my_account.php → minha_conta.php\n";
echo "- auth.php → autenticacao.php\n";
echo "- sidebar.php → menu_lateral.php\n";

echo "</pre>";
echo "<br><a href='index.php' style='padding: 10px 20px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px;'>Ir para Login</a>";
?>