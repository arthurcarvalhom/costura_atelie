<?php
// Navbar Premium para Admin
?>
<nav class="navbar-premium">
    <div class="navbar-premium-container">
        <div class="navbar-premium-branding">
            <h1 class="navbar-premium-title">
                <i class="fas fa-heart"></i> Costura Ateliê
            </h1>
        </div>

        <!-- Menu Toggle Mobile -->
        <button class="navbar-premium-menu-toggle" onclick="toggleMenu()">
            <i class="fas fa-bars"></i>
        </button>

        <ul class="navbar-premium-menu" id="navbarMenu">
            <li><a href="<?php echo ADMIN_URL; ?>" class="navbar-premium-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Painel
            </a></li>
            <li><a href="<?php echo ADMIN_URL; ?>produtos.php" class="navbar-premium-link <?php echo basename($_SERVER['PHP_SELF']) == 'produtos.php' ? 'active' : ''; ?>">
                <i class="fas fa-box"></i> Produtos
            </a></li>
            <li><a href="<?php echo ADMIN_URL; ?>estoque.php" class="navbar-premium-link <?php echo basename($_SERVER['PHP_SELF']) == 'estoque.php' ? 'active' : ''; ?>">
                <i class="fas fa-warehouse"></i> Estoque
            </a></li>
            <li><a href="<?php echo ADMIN_URL; ?>pedidos.php" class="navbar-premium-link <?php echo basename($_SERVER['PHP_SELF']) == 'pedidos.php' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-cart"></i> Pedidos
            </a></li>
            <li><a href="<?php echo ADMIN_URL; ?>producao.php" class="navbar-premium-link <?php echo basename($_SERVER['PHP_SELF']) == 'producao.php' ? 'active' : ''; ?>">
                <i class="fas fa-tasks"></i> Produção
            </a></li>
            <li><a href="<?php echo ADMIN_URL; ?>financeiro.php" class="navbar-premium-link <?php echo basename($_SERVER['PHP_SELF']) == 'financeiro.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-pie"></i> Financeiro
            </a></li>
            <li><a href="<?php echo SITE_URL; ?>?admin_view=1" class="navbar-premium-link" title="Ver catálogo público" target="_blank">
                <i class="fas fa-eye"></i> Catálogo
            </a></li>
        </ul>

        <div class="navbar-premium-user">
            <span class="navbar-premium-user-info">
                <i class="fas fa-user-circle"></i>
                <?php echo isset($_SESSION['usuario_nome']) ? htmlspecialchars($_SESSION['usuario_nome']) : 'Usuário'; ?>
            </span>
            <a href="<?php echo ADMIN_URL; ?>logout.php" class="navbar-premium-logout">
                <i class="fas fa-sign-out-alt"></i> Sair
            </a>
        </div>
    </div>
</nav>

<script>
// Toggle Menu Mobile
function toggleMenu() {
    const menu = document.getElementById('navbarMenu');
    menu.classList.toggle('active');
}

// Fechar menu ao clicar fora (mobile)
document.addEventListener('click', function(event) {
    const menu = document.getElementById('navbarMenu');
    const toggle = document.querySelector('.navbar-premium-menu-toggle');

    if (!menu.contains(event.target) && !toggle.contains(event.target)) {
        menu.classList.remove('active');
    }
});
</script>
