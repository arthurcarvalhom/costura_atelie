<?php
/**
 * Configuração de Banco de Dados
 * Sistema de Gestão Ateliê Enxoval
 */

// Configurações de conexão
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'costura_atelier');

// Criar conexão
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Definir charset
$conn->set_charset("utf8mb4");

// Verificar conexão
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// Configurações gerais da aplicação
define('SITE_URL', 'http://localhost/costura/');
define('ADMIN_URL', SITE_URL . 'admin/');
define('ASSETS_URL', SITE_URL . 'assets/');
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/produtos/');
define('MAX_UPLOAD_SIZE', 5242880); // 5MB

// Configurações de sessão
define('SESSION_TIMEOUT', 3600); // 1 hora

?>
