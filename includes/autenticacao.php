<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

function isAdmin()
{
    return isset($_SESSION['user_tipo']) && $_SESSION['user_tipo'] === 'admin';
}
?>