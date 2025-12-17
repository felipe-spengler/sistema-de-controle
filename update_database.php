<?php
require_once 'config/db.php';

try {
    // Tabela de Saques
    $pdo->exec("CREATE TABLE IF NOT EXISTS withdrawals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        amount DECIMAL(10, 2) NOT NULL,
        status ENUM('pending', 'approved', 'paid', 'rejected') DEFAULT 'pending',
        asaas_transfer_id VARCHAR(100),
        requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        processed_at TIMESTAMP NULL,
        notes TEXT,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // Adicionar campos bancários na tabela users
    $pdo->exec("ALTER TABLE users 
        ADD COLUMN IF NOT EXISTS bank_name VARCHAR(100),
        ADD COLUMN IF NOT EXISTS bank_agency VARCHAR(20),
        ADD COLUMN IF NOT EXISTS bank_account VARCHAR(20),
        ADD COLUMN IF NOT EXISTS bank_account_type ENUM('checking', 'savings') DEFAULT 'checking',
        ADD COLUMN IF NOT EXISTS cpf_cnpj VARCHAR(20),
        ADD COLUMN IF NOT EXISTS phone VARCHAR(20),
        ADD COLUMN IF NOT EXISTS asaas_account_id VARCHAR(100)
    ");

    // Adicionar campos do Asaas na tabela invoices
    $pdo->exec("ALTER TABLE invoices 
        ADD COLUMN IF NOT EXISTS asaas_payment_id VARCHAR(100),
        ADD COLUMN IF NOT EXISTS payment_url VARCHAR(255),
        ADD COLUMN IF NOT EXISTS barcode TEXT,
        ADD COLUMN IF NOT EXISTS pix_qrcode TEXT
    ");

    // Adicionar campo de comissão
    $pdo->exec("ALTER TABLE users 
        ADD COLUMN IF NOT EXISTS commission_rate DECIMAL(5, 2) DEFAULT 10.00
    ");

    echo "✅ Tabelas atualizadas com sucesso!<br>";
    echo "✅ Tabela de saques criada<br>";
    echo "✅ Campos bancários adicionados<br>";
    echo "✅ Integração Asaas preparada<br>";

} catch (PDOException $e) {
    echo "❌ Erro ao atualizar banco de dados: " . $e->getMessage();
}
?>