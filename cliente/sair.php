<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// Remover dados da sessão do cliente
unset($_SESSION['cliente_id']);
unset($_SESSION['cliente_nome']);

// Destruir sessão se não há nada mais
if (empty($_SESSION)) {
    session_destroy();
}

// Redirecionar para catálogo
header('Location: ' . SITE_URL . 'public/index.php?desconectado=1');
exit();
?>
