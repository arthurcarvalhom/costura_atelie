<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Se já está logado, redirecionar
if (isset($_SESSION['usuario_id'])) {
    header('Location: ' . ADMIN_URL);
    exit();
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = limpar_entrada($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (fazer_login($email, $senha)) {
        header('Location: ' . ADMIN_URL);
        exit();
    } else {
        $erro = 'Email ou senha inválidos';
    }
}

$titulo_pagina = 'Login';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Costura Ateliê</title>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #FF6B9D, #FF85B3);
        }
        
        .login-container {
            background: white;
            padding: 3rem;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-title {
            font-size: 2rem;
            color: #FF6B9D;
            margin-bottom: 0.5rem;
        }
        
        .login-subtitle {
            color: #999;
            font-size: 0.9rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #FF6B9D;
            box-shadow: 0 0 0 3px rgba(255, 107, 157, 0.1);
        }
        
        .btn-login {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #FF6B9D, #FF85B3);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 157, 0.4);
        }
        
        .error-message {
            background-color: #FFCDD2;
            color: #C62828;
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 2rem;
            color: #999;
            font-size: 0.85rem;
        }

        .register-panel {
            margin-top: 2rem;
            padding: 1rem;
            background: #faf4f8;
            border: 1px solid #ffd6e6;
            border-radius: 10px;
        }

        .register-panel p {
            margin-bottom: 1rem;
            color: #666;
            text-align: center;
        }

        .register-options {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .register-card {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 0.9rem 1rem;
            border-radius: 8px;
            background: white;
            color: #FF6B9D;
            text-decoration: none;
            border: 1px solid #ffd6e6;
            font-weight: 600;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .register-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 107, 157, 0.12);
        }

        .register-card i {
            font-size: 1.1rem;
        }
        
        @media (max-width: 600px) {
            .login-container {
                padding: 2rem 1.5rem;
                margin: 1rem;
            }
            
            .login-title {
                font-size: 1.5rem;
            }

            .register-options {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-title">
                <i class="fas fa-needle"></i> Costura Ateliê
            </div>
            <p class="login-subtitle">Sistema de Gestão</p>
        </div>

        <?php if ($erro): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo htmlspecialchars($erro); ?></span>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required autofocus>
            </div>

            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Entrar
            </button>
        </form>

        <div class="register-panel">
            <p>Não tem conta? Selecione o tipo de cadastro:</p>
            <div class="register-options">
                <a href="<?php echo SITE_URL; ?>criar_usuario.php?tipo=admin" class="register-card">
                    <i class="fas fa-user-shield"></i>
                    Administrador / Fornecedor
                </a>
                <a href="<?php echo SITE_URL; ?>criar_usuario.php?tipo=cliente" class="register-card">
                    <i class="fas fa-user"></i>
                    Cliente
                </a>
            </div>
        </div>

    </div>
</body>
</html>
