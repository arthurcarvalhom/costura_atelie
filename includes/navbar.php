<?php
// Navbar com navegação comum
?>
<nav class="navbar">
    <div class="navbar-container">
        <div class="navbar-branding">
            <h1 class="navbar-title">
                <i class="fas fa-needle"></i> Costura Ateliê
            </h1>
        </div>
        
        <ul class="navbar-menu">
            <li><a href="<?php echo ADMIN_URL; ?>" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Painel
            </a></li>
            <li><a href="<?php echo ADMIN_URL; ?>produtos.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'produtos.php' ? 'active' : ''; ?>">
                <i class="fas fa-box"></i> Produtos
            </a></li>
            <li><a href="<?php echo ADMIN_URL; ?>estoque.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'estoque.php' ? 'active' : ''; ?>">
                <i class="fas fa-warehouse"></i> Estoque
            </a></li>
            <li><a href="<?php echo ADMIN_URL; ?>pedidos.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'pedidos.php' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-cart"></i> Pedidos
            </a></li>
            <li><a href="<?php echo ADMIN_URL; ?>producao.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'producao.php' ? 'active' : ''; ?>">
                <i class="fas fa-tasks"></i> Produção
            </a></li>
            <li><a href="<?php echo ADMIN_URL; ?>financeiro.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'financeiro.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-pie"></i> Financeiro
            </a></li>
            <li><a href="<?php echo SITE_URL; ?>" class="nav-link" title="Ver catálogo público">
                <i class="fas fa-eye"></i> Catálogo
            </a></li>
        </ul>

        <div class="navbar-user">
            <span class="user-info">
                <i class="fas fa-user-circle"></i> 
                <?php echo isset($_SESSION['usuario_nome']) ? htmlspecialchars($_SESSION['usuario_nome']) : 'Usuário'; ?>
            </span>
            <a href="<?php echo ADMIN_URL; ?>logout.php" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Sair
            </a>
        </div>
    </div>
</nav>
