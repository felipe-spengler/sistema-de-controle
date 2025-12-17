<?php
require_once 'config/db.php';

echo "<h2>🔄 Adicionando Sistema de Comissões</h2>";
echo "<pre>";

try {
    // Adicionar campo de percentual de comissão na tabela clientes
    echo "📋 Adicionando campo 'percentual_vendedor' na tabela clientes...\n";
    $pdo->exec("ALTER TABLE clientes ADD COLUMN IF NOT EXISTS percentual_vendedor DECIMAL(5, 2) DEFAULT 50.00 COMMENT 'Percentual que o vendedor recebe (padrão 50%)'");
    echo "✅ Campo adicionado!\n\n";

    // Atualizar clientes existentes para ter 50%
    echo "📋 Configurando 50% para clientes existentes...\n";
    $pdo->exec("UPDATE clientes SET percentual_vendedor = 50.00 WHERE percentual_vendedor IS NULL");
    echo "✅ Clientes atualizados!\n\n";

    echo "✅✅✅ SISTEMA DE COMISSÕES IMPLEMENTADO! ✅✅✅\n";
    echo "\n📊 Resumo:\n";
    echo "- Campo 'percentual_vendedor' adicionado na tabela clientes\n";
    echo "- Valor padrão: 50% para o vendedor\n";
    echo "- Admin pode alterar individualmente por cliente\n";
    echo "- Cálculo de saldo agora considera o percentual\n";

} catch (PDOException $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}

echo "</pre>";
echo "<br><a href='index.php' style='padding: 10px 20px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px;'>Ir para Login</a>";
?>