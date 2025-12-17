<?php
require_once __DIR__ . '/../config/db.php';

/**
 * Obtém o valor de uma configuração
 */
function getConfig($chave)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT valor FROM configuracoes WHERE chave = ?");
    $stmt->execute([$chave]);
    return $stmt->fetchColumn();
}

/**
 * Salva o valor de uma configuração
 */
function setConfig($chave, $valor)
{
    global $pdo;
    $stmt = $pdo->prepare("UPDATE configuracoes SET valor = ? WHERE chave = ?");
    return $stmt->execute([$valor, $chave]);
}

/**
 * Obtém todas as configurações
 */
function getAllConfigs()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM configuracoes ORDER BY chave");
    return $stmt->fetchAll();
}
?>