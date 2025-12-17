<?php
require_once 'config/db.php';

try {
    // Tabela Usuarios
    $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        senha VARCHAR(255) NOT NULL,
        tipo ENUM('admin', 'vendedor') DEFAULT 'vendedor',
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        banco_nome VARCHAR(100),
        banco_agencia VARCHAR(20),
        banco_conta VARCHAR(20),
        banco_tipo_conta ENUM('corrente', 'poupanca') DEFAULT 'corrente',
        cpf_cnpj VARCHAR(20),
        telefone VARCHAR(20),
        asaas_conta_id VARCHAR(100),
        taxa_comissao DECIMAL(5, 2) DEFAULT 10.00
    )");

    // Tabela Clientes
    $pdo->exec("CREATE TABLE IF NOT EXISTS clientes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        vendedor_id INT NOT NULL,
        razao_social VARCHAR(150) NOT NULL,
        cnpj VARCHAR(20),
        tipo_software VARCHAR(50),
        plano VARCHAR(50),
        status ENUM('ativo', 'inativo', 'pendente') DEFAULT 'pendente',
        mensalidade DECIMAL(10, 2) DEFAULT 0.00,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (vendedor_id) REFERENCES usuarios(id) ON DELETE CASCADE
    )");

    // Tabela Faturas
    $pdo->exec("CREATE TABLE IF NOT EXISTS faturas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cliente_id INT NOT NULL,
        data_vencimento DATE NOT NULL,
        data_pagamento DATE,
        valor DECIMAL(10, 2) NOT NULL,
        status ENUM('pago', 'pendente', 'atrasado') DEFAULT 'pendente',
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        asaas_pagamento_id VARCHAR(100),
        url_pagamento VARCHAR(255),
        codigo_barras TEXT,
        pix_qrcode TEXT,
        FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
    )");

    // Tabela Saques
    $pdo->exec("CREATE TABLE IF NOT EXISTS saques (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        valor DECIMAL(10, 2) NOT NULL,
        status ENUM('pendente', 'aprovado', 'pago', 'rejeitado') DEFAULT 'pendente',
        asaas_transferencia_id VARCHAR(100),
        solicitado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        processado_em TIMESTAMP NULL,
        observacoes TEXT,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    )");

    // Tabela Logs de Cobrança
    $pdo->exec("CREATE TABLE IF NOT EXISTS logs_cobrancas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        data_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
        tipo VARCHAR(50), 
        vendedor_nome VARCHAR(255),
        cliente_rs VARCHAR(255),
        telefone VARCHAR(50),
        status VARCHAR(50),
        mensagem TEXT,
        erro TEXT
    )");

    // Tabela Configurações
    $pdo->exec("CREATE TABLE IF NOT EXISTS configuracoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        chave VARCHAR(50) NOT NULL UNIQUE,
        valor TEXT,
        descricao VARCHAR(255),
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Inserir configurações padrão se não existirem
    $defaultConfigs = [
        ['asaas_api_key', '', 'Chave da API do Asaas'],
        ['asaas_ambiente', 'sandbox', 'Ambiente do Asaas (sandbox ou production)'],
        ['waha_url', 'http://waha:3000', 'URL do servidor Waha (interno)'], // Usando nome do container
        ['msg_vencimento_5dias', 'Olá {vendedor}, a fatura do cliente {cliente} vence em 5 dias.', 'Mensagem 5 dias antes'],
        ['msg_vencimento_hoje', 'Olá {vendedor}, a fatura do cliente {cliente} vence hoje!', 'Mensagem no dia do vencimento'],
        ['msg_vencimento_atrasado', 'Olá {vendedor}, a fatura do cliente {cliente} está atrasada.', 'Mensagem de atraso (diária)']
    ];

    $stmtConfig = $pdo->prepare("INSERT IGNORE INTO configuracoes (chave, valor, descricao) VALUES (?, ?, ?)");
    foreach ($defaultConfigs as $config) {
        $stmtConfig->execute($config);
    }


    // Criar Admin User se não existir
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
    $stmt->execute(['admin@sistema.com']);
    if ($stmt->fetchColumn() == 0) {
        $pass = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Administrador', 'admin@sistema.com', $pass, 'admin']);
        echo "✅ Usuário Admin criado: admin@sistema.com / admin123<br>";
    }

    echo "✅ Banco de dados configurado com sucesso!<br>";
    echo "✅ Todas as tabelas em PORTUGUÊS!<br>";

} catch (PDOException $e) {
    echo "❌ Erro ao configurar banco de dados: " . $e->getMessage();
}
?>