<?php
require_once 'config/db.php';

echo "<h2>🔄 Migrando Banco de Dados para Português</h2>";
echo "<pre>";

try {
    // ========================================
    // TABELA: users → usuarios
    // ========================================
    echo "📋 Renomeando tabela 'users' para 'usuarios'...\n";
    $pdo->exec("RENAME TABLE users TO usuarios");
    echo "✅ Tabela renomeada!\n\n";

    echo "📋 Traduzindo colunas da tabela 'usuarios'...\n";

    // Renomear colunas
    $pdo->exec("ALTER TABLE usuarios CHANGE COLUMN name nome VARCHAR(100) NOT NULL");
    echo "✅ name → nome\n";

    $pdo->exec("ALTER TABLE usuarios CHANGE COLUMN email email VARCHAR(100) NOT NULL");
    echo "✅ email → email (mantido)\n";

    $pdo->exec("ALTER TABLE usuarios CHANGE COLUMN password senha VARCHAR(255) NOT NULL");
    echo "✅ password → senha\n";

    $pdo->exec("ALTER TABLE usuarios CHANGE COLUMN role tipo ENUM('admin', 'vendedor') DEFAULT 'vendedor'");
    echo "✅ role → tipo (admin/vendedor)\n";

    $pdo->exec("ALTER TABLE usuarios CHANGE COLUMN created_at criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    echo "✅ created_at → criado_em\n";

    $pdo->exec("ALTER TABLE usuarios CHANGE COLUMN bank_name banco_nome VARCHAR(100)");
    echo "✅ bank_name → banco_nome\n";

    $pdo->exec("ALTER TABLE usuarios CHANGE COLUMN bank_agency banco_agencia VARCHAR(20)");
    echo "✅ bank_agency → banco_agencia\n";

    $pdo->exec("ALTER TABLE usuarios CHANGE COLUMN bank_account banco_conta VARCHAR(20)");
    echo "✅ bank_account → banco_conta\n";

    $pdo->exec("ALTER TABLE usuarios CHANGE COLUMN bank_account_type banco_tipo_conta ENUM('corrente', 'poupanca') DEFAULT 'corrente'");
    echo "✅ bank_account_type → banco_tipo_conta (corrente/poupanca)\n";

    $pdo->exec("ALTER TABLE usuarios CHANGE COLUMN phone telefone VARCHAR(20)");
    echo "✅ phone → telefone\n";

    $pdo->exec("ALTER TABLE usuarios CHANGE COLUMN asaas_account_id asaas_conta_id VARCHAR(100)");
    echo "✅ asaas_account_id → asaas_conta_id\n";

    $pdo->exec("ALTER TABLE usuarios CHANGE COLUMN commission_rate taxa_comissao DECIMAL(5, 2) DEFAULT 10.00");
    echo "✅ commission_rate → taxa_comissao\n\n";

    // ========================================
    // TABELA: clients → clientes
    // ========================================
    echo "📋 Renomeando tabela 'clients' para 'clientes'...\n";
    $pdo->exec("RENAME TABLE clients TO clientes");
    echo "✅ Tabela renomeada!\n\n";

    echo "📋 Traduzindo colunas da tabela 'clientes'...\n";

    $pdo->exec("ALTER TABLE clientes CHANGE COLUMN seller_id vendedor_id INT NOT NULL");
    echo "✅ seller_id → vendedor_id\n";

    $pdo->exec("ALTER TABLE clientes CHANGE COLUMN company_name razao_social VARCHAR(150) NOT NULL");
    echo "✅ company_name → razao_social\n";

    $pdo->exec("ALTER TABLE clientes CHANGE COLUMN cnpj cnpj VARCHAR(20)");
    echo "✅ cnpj → cnpj (mantido)\n";

    $pdo->exec("ALTER TABLE clientes CHANGE COLUMN software_type tipo_software VARCHAR(50)");
    echo "✅ software_type → tipo_software\n";

    $pdo->exec("ALTER TABLE clientes CHANGE COLUMN plan plano VARCHAR(50)");
    echo "✅ plan → plano\n";

    $pdo->exec("ALTER TABLE clientes CHANGE COLUMN status status ENUM('ativo', 'inativo', 'pendente') DEFAULT 'pendente'");
    echo "✅ status → status (ativo/inativo/pendente)\n";

    $pdo->exec("ALTER TABLE clientes CHANGE COLUMN monthly_fee mensalidade DECIMAL(10, 2) DEFAULT 0.00");
    echo "✅ monthly_fee → mensalidade\n";

    $pdo->exec("ALTER TABLE clientes CHANGE COLUMN created_at criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    echo "✅ created_at → criado_em\n\n";

    // ========================================
    // TABELA: invoices → faturas
    // ========================================
    echo "📋 Renomeando tabela 'invoices' para 'faturas'...\n";
    $pdo->exec("RENAME TABLE invoices TO faturas");
    echo "✅ Tabela renomeada!\n\n";

    echo "📋 Traduzindo colunas da tabela 'faturas'...\n";

    $pdo->exec("ALTER TABLE faturas CHANGE COLUMN client_id cliente_id INT NOT NULL");
    echo "✅ client_id → cliente_id\n";

    $pdo->exec("ALTER TABLE faturas CHANGE COLUMN due_date data_vencimento DATE NOT NULL");
    echo "✅ due_date → data_vencimento\n";

    $pdo->exec("ALTER TABLE faturas CHANGE COLUMN payment_date data_pagamento DATE");
    echo "✅ payment_date → data_pagamento\n";

    $pdo->exec("ALTER TABLE faturas CHANGE COLUMN amount valor DECIMAL(10, 2) NOT NULL");
    echo "✅ amount → valor\n";

    $pdo->exec("ALTER TABLE faturas CHANGE COLUMN status status ENUM('pago', 'pendente', 'atrasado') DEFAULT 'pendente'");
    echo "✅ status → status (pago/pendente/atrasado)\n";

    $pdo->exec("ALTER TABLE faturas CHANGE COLUMN created_at criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    echo "✅ created_at → criado_em\n";

    $pdo->exec("ALTER TABLE faturas CHANGE COLUMN asaas_payment_id asaas_pagamento_id VARCHAR(100)");
    echo "✅ asaas_payment_id → asaas_pagamento_id\n";

    $pdo->exec("ALTER TABLE faturas CHANGE COLUMN payment_url url_pagamento VARCHAR(255)");
    echo "✅ payment_url → url_pagamento\n";

    $pdo->exec("ALTER TABLE faturas CHANGE COLUMN barcode codigo_barras TEXT");
    echo "✅ barcode → codigo_barras\n";

    $pdo->exec("ALTER TABLE faturas CHANGE COLUMN pix_qrcode pix_qrcode TEXT");
    echo "✅ pix_qrcode → pix_qrcode (mantido)\n\n";

    // ========================================
    // TABELA: withdrawals → saques
    // ========================================
    echo "📋 Renomeando tabela 'withdrawals' para 'saques'...\n";
    $pdo->exec("RENAME TABLE withdrawals TO saques");
    echo "✅ Tabela renomeada!\n\n";

    echo "📋 Traduzindo colunas da tabela 'saques'...\n";

    $pdo->exec("ALTER TABLE saques CHANGE COLUMN user_id usuario_id INT NOT NULL");
    echo "✅ user_id → usuario_id\n";

    $pdo->exec("ALTER TABLE saques CHANGE COLUMN amount valor DECIMAL(10, 2) NOT NULL");
    echo "✅ amount → valor\n";

    $pdo->exec("ALTER TABLE saques CHANGE COLUMN status status ENUM('pendente', 'aprovado', 'pago', 'rejeitado') DEFAULT 'pendente'");
    echo "✅ status → status (pendente/aprovado/pago/rejeitado)\n";

    $pdo->exec("ALTER TABLE saques CHANGE COLUMN asaas_transfer_id asaas_transferencia_id VARCHAR(100)");
    echo "✅ asaas_transfer_id → asaas_transferencia_id\n";

    $pdo->exec("ALTER TABLE saques CHANGE COLUMN requested_at solicitado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    echo "✅ requested_at → solicitado_em\n";

    $pdo->exec("ALTER TABLE saques CHANGE COLUMN processed_at processado_em TIMESTAMP NULL");
    echo "✅ processed_at → processado_em\n";

    $pdo->exec("ALTER TABLE saques CHANGE COLUMN notes observacoes TEXT");
    echo "✅ notes → observacoes\n\n";

    // ========================================
    // Atualizar Foreign Keys
    // ========================================
    echo "📋 Atualizando Foreign Keys...\n";

    // Remover constraints antigas
    $pdo->exec("ALTER TABLE clientes DROP FOREIGN KEY clientes_ibfk_1");
    $pdo->exec("ALTER TABLE faturas DROP FOREIGN KEY faturas_ibfk_1");
    $pdo->exec("ALTER TABLE saques DROP FOREIGN KEY saques_ibfk_1");

    // Adicionar novas constraints
    $pdo->exec("ALTER TABLE clientes ADD CONSTRAINT fk_clientes_vendedor FOREIGN KEY (vendedor_id) REFERENCES usuarios(id) ON DELETE CASCADE");
    echo "✅ Foreign key clientes → usuarios\n";

    $pdo->exec("ALTER TABLE faturas ADD CONSTRAINT fk_faturas_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE");
    echo "✅ Foreign key faturas → clientes\n";

    $pdo->exec("ALTER TABLE saques ADD CONSTRAINT fk_saques_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE");
    echo "✅ Foreign key saques → usuarios\n\n";

    echo "✅✅✅ MIGRAÇÃO CONCLUÍDA COM SUCESSO! ✅✅✅\n";
    echo "\n📊 Resumo:\n";
    echo "- Tabela 'users' → 'usuarios'\n";
    echo "- Tabela 'clients' → 'clientes'\n";
    echo "- Tabela 'invoices' → 'faturas'\n";
    echo "- Tabela 'withdrawals' → 'saques'\n";
    echo "- Todas as colunas traduzidas para português\n";
    echo "- Foreign keys atualizadas\n";

} catch (PDOException $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "\nSe o erro for sobre constraint já existente, ignore.\n";
}

echo "</pre>";
echo "<br><a href='index.php' style='padding: 10px 20px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px;'>Ir para Login</a>";
?>