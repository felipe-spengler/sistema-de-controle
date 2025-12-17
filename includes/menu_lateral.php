<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <span style="color: var(--primary-color);">System</span>Manager
    </div>

    <nav class="sidebar-nav">
        <ul class="nav-list">
            <li>
                <a href="painel.php" class="nav-item <?= $current_page == 'painel.php' ? 'active' : '' ?>">
                    <span>Dashboard</span>
                </a>
            </li>

            <?php if (isAdmin()): ?>
                <li>
                    <a href="vendedores.php" class="nav-item <?= $current_page == 'vendedores.php' ? 'active' : '' ?>">
                        <span>Vendedores</span>
                    </a>
                </li>
            <?php endif; ?>

            <li>
                <a href="faturas.php" class="nav-item <?= $current_page == 'faturas.php' ? 'active' : '' ?>">
                    <span>Faturas</span>
                </a>
            </li>

            <li>
                <a href="clientes.php" class="nav-item <?= $current_page == 'clientes.php' ? 'active' : '' ?>">
                    <span>Clientes</span>
                </a>
            </li>

            <li>
                <a href="extrato.php" class="nav-item <?= $current_page == 'extrato.php' ? 'active' : '' ?>">
                    <span>Extrato</span>
                </a>
            </li>

            <li>
                <a href="saques.php" class="nav-item <?= $current_page == 'saques.php' ? 'active' : '' ?>">
                    <span>Saques</span>
                </a>
            </li>

            <li>
                <a href="assinaturas.php"
                    class="nav-item <?= $current_page == 'assinaturas.php' ? 'active' : '' ?>">
                    <span>Assinaturas</span>
                </a>
            </li>

            <li>
                <a href="minha_conta.php" class="nav-item <?= $current_page == 'minha_conta.php' ? 'active' : '' ?>">
                    <span>Minha Conta</span>
                </a>
            </li>
        </ul>
    </nav>

    <div style="padding: var(--spacing-md); border-top: 1px solid rgba(255,255,255,0.1);">
        <a href="../logout.php" class="nav-item" style="color: #ef4444;">
            <span>Sair</span>
        </a>
    </div>
</aside>