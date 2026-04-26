<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// Se já está logado como cliente, redirecionar para catálogo
if (isset($_SESSION['cliente_id'])) {
    header('Location: ' . SITE_URL . 'public/index.php');
    exit();
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $erro = 'Email e senha são obrigatórios';
    } else {
        $stmt = $conn->prepare("SELECT id, nome, senha FROM clientes WHERE email = ? AND ativo = 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $cliente = $result->fetch_assoc();
            if (password_verify($senha, $cliente['senha'])) {
                $_SESSION['cliente_id'] = $cliente['id'];
                $_SESSION['cliente_nome'] = $cliente['nome'];
                header('Location: ' . SITE_URL . 'public/?logado=1');
                exit();
            } else {
                $erro = 'Email ou senha inválidos';
            }
        } else {
            $erro = 'Email ou senha inválidos';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Costura Ateliê</title>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Fundo */
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #fff0f6, #ffd9e8, #f7c6dc);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 80%, rgba(234, 54, 114, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 80% 20%, rgba(242, 126, 170, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        /* Login Card */
        .login-container {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0,0,0,.12);
            width: 100%;
            max-width: 450px;
            padding: 3rem 2.5rem;
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Logo / Título */
        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .login-title {
            font-size: 2.8rem;
            font-weight: 700;
            color: #ea3672;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .login-title i {
            color: #f27eaa;
        }

        .login-subtitle {
            color: #6c757d;
            font-size: 1rem;
            font-weight: 400;
            margin: 0;
        }

        /* Inputs */
        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #2b2b2b;
            font-size: 0.95rem;
        }

        input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #f0f0f0;
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            background: #ffffff;
            color: #2b2b2b;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-sizing: border-box;
        }

        input::placeholder {
            color: #adb5bd;
            font-weight: 400;
        }

        input:focus {
            outline: none;
            border-color: #ea3672;
            box-shadow: 0 0 0 3px rgba(234, 54, 114, 0.1);
            transform: translateY(-1px);
        }

        /* Botões */
        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(45deg, #ea3672, #f27eaa);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(234, 54, 114, 0.3);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(234, 54, 114, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* Cadastro */
        .cadastro-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #f0f0f0;
        }

        .cadastro-title {
            text-align: center;
            color: #6c757d;
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .cadastro-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .cadastro-option {
            background: #ffffff;
            border: 2px solid #f0f0f0;
            border-radius: 12px;
            padding: 1.5rem 1rem;
            text-align: center;
            text-decoration: none;
            color: #2b2b2b;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .cadastro-option:hover {
            border-color: #ea3672;
            background: #fef8fb;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(234, 54, 114, 0.1);
        }

        .cadastro-option i {
            font-size: 1.5rem;
            color: #ea3672;
            margin-bottom: 0.25rem;
        }

        .cadastro-option span {
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Segurança */
        .security-notice {
            text-align: center;
            color: #6c757d;
            font-size: 0.85rem;
            margin-top: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .security-notice i {
            color: #ea3672;
        }

        /* Mensagens de erro */
        .error-message {
            background: linear-gradient(135deg, #ffebee, #ffcdd2);
            color: #c62828;
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-left: 4px solid #f44336;
            animation: slideIn 0.4s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .error-message i {
            font-size: 1.1rem;
        }

        /* Links */
        .login-footer {
            text-align: center;
            margin-top: 2rem;
        }

        .login-footer a {
            color: #ea3672;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .login-footer a:hover {
            color: #d42b5f;
            text-decoration: underline;
        }

        /* Responsivo */
        @media (max-width: 768px) {
            .login-container {
                margin: 1rem;
                padding: 2rem 1.5rem;
                max-width: 95%;
            }

            .login-title {
                font-size: 2.2rem;
            }

            .cadastro-options {
                grid-template-columns: 1fr;
            }

            .cadastro-option {
                padding: 1.25rem 1rem;
            }
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 1.5rem 1rem;
            }

            .login-title {
                font-size: 1.8rem;
                flex-direction: column;
                gap: 0.25rem;
            }

            .login-subtitle {
                font-size: 0.9rem;
            }

            input {
                padding: 12px 14px;
            }

            .btn-login {
                padding: 14px;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-title">
                <i class="fas fa-heart"></i>
                Costura Ateliê
            </div>
            <p class="login-subtitle">Sistema de Gestão</p>
        </div>

        <?php if ($erro): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-triangle"></i>
            <span><?php echo htmlspecialchars($erro); ?></span>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="seu@email.com" required autofocus>
            </div>

            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i>
                Entrar
            </button>
        </form>

        <div class="cadastro-section">
            <div class="cadastro-title">Não tem conta? Escolha seu perfil:</div>
            <div class="cadastro-options">
                <a href="<?php echo SITE_URL; ?>admin/login.php" class="cadastro-option">
                    <i class="fas fa-cog"></i>
                    <span>Administrador<br>Fornecedor</span>
                </a>
                <a href="<?php echo SITE_URL; ?>criar_usuario.php?tipo=cliente" class="cadastro-option">
                    <i class="fas fa-user"></i>
                    <span>Cliente</span>
                </a>
            </div>
        </div>

        <div class="security-notice">
            <i class="fas fa-shield-alt"></i>
            <span>Seus dados estão protegidos</span>
        </div>

        <div class="login-footer">
            <a href="<?php echo SITE_URL; ?>public/index.php">← Voltar para o catálogo</a>
        </div>
    </div>
</body>
</html>
