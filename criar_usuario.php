<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$tipo = $_GET['tipo'] ?? 'admin';
$tipo = ($tipo === 'cliente') ? 'cliente' : 'admin';
$modo_cliente = $tipo === 'cliente';

$mensagem = '';
$tipo_mensagem = '';
$tem_usuarios = false;

if (!$modo_cliente) {
    $result = $conn->query("SELECT COUNT(*) as count FROM usuarios");
    $row = $result->fetch_assoc();
    $tem_usuarios = $row['count'] > 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($modo_cliente) {
        $telefone = trim($_POST['telefone'] ?? '');
        $senha = trim($_POST['senha'] ?? '');
        $confirmar_senha = trim($_POST['confirmar_senha'] ?? '');

        if (empty($nome)) {
            $mensagem = 'Nome é obrigatório';
            $tipo_mensagem = 'error';
        } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mensagem = 'Email inválido';
            $tipo_mensagem = 'error';
        } elseif (strlen($senha) < 6) {
            $mensagem = 'Senha deve ter pelo menos 6 caracteres';
            $tipo_mensagem = 'error';
        } elseif ($senha !== $confirmar_senha) {
            $mensagem = 'Senhas não coincidem';
            $tipo_mensagem = 'error';
        } else {
            $check = $conn->prepare("SELECT id FROM clientes WHERE email = ? LIMIT 1");
            $check->bind_param('s', $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $mensagem = 'Este email já está registrado como cliente';
                $tipo_mensagem = 'error';
            } else {
                $senha_hash = password_hash($senha, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("INSERT INTO clientes (nome, email, telefone, senha, ativo) VALUES (?, ?, ?, ?, 1)");
                $stmt->bind_param('ssss', $nome, $email, $telefone, $senha_hash);

                if ($stmt->execute()) {
                    $mensagem = '✅ Cadastro de cliente realizado com sucesso! Agora faça login com suas credenciais.';
                    $tipo_mensagem = 'success';
                } else {
                    $mensagem = '❌ Erro ao cadastrar cliente: ' . $conn->error;
                    $tipo_mensagem = 'error';
                }
            }
        }
    } else {
        $senha = $_POST['senha'] ?? '';
        $confirmar_senha = $_POST['confirmar_senha'] ?? '';

        if (empty($nome)) {
            $mensagem = 'Nome é obrigatório';
            $tipo_mensagem = 'error';
        } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mensagem = 'Email inválido';
            $tipo_mensagem = 'error';
        } elseif (strlen($senha) < 6) {
            $mensagem = 'Senha deve ter pelo menos 6 caracteres';
            $tipo_mensagem = 'error';
        } elseif ($senha !== $confirmar_senha) {
            $mensagem = 'Senhas não coincidem';
            $tipo_mensagem = 'error';
        } else {
            $check = $conn->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
            $check->bind_param('s', $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $mensagem = 'Este email já está registrado como administrador';
                $tipo_mensagem = 'error';
            } else {
                $senha_hash = password_hash($senha, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, ativo) VALUES (?, ?, ?, 1)");
                $stmt->bind_param('sss', $nome, $email, $senha_hash);

                if ($stmt->execute()) {
                    $mensagem = '✅ Usuário criado com sucesso! Faça login para continuar.';
                    $tipo_mensagem = 'success';
                } else {
                    $mensagem = '❌ Erro ao criar usuário: ' . $conn->error;
                    $tipo_mensagem = 'error';
                }
            }
        }
    }
}

$titulo = $modo_cliente ? 'Cadastro de Cliente' : 'Cadastro Administrador / Fornecedor';
$subtitulo = $modo_cliente ? 'Cadastre-se para receber informações e realizar pedidos.' : 'Cadastre um administrador ou fornecedor para gerenciar o sistema.';
$botao = $modo_cliente ? 'Cadastrar Cliente' : 'Criar Usuário';
$form_action = 'criar_usuario.php?tipo=' . $tipo;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titulo); ?> - Costura Ateliê</title>
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

        /* Card Cadastro */
        .container {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0,0,0,.12);
            width: 100%;
            max-width: 500px;
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

        /* Título */
        h1 {
            font-size: 2.8rem;
            font-weight: 700;
            color: #ea3672;
            margin-bottom: 0.5rem;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        h1 i {
            color: #f27eaa;
        }

        .subtitle {
            color: #6c757d;
            font-size: 1rem;
            font-weight: 400;
            text-align: center;
            margin-bottom: 2rem;
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
        .btn {
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

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(234, 54, 114, 0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        /* Alertas */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
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

        .alert.success {
            background: linear-gradient(135deg, #e8f5e8, #c8e6c9);
            color: #2e7d32;
            border-left: 4px solid #4caf50;
        }

        .alert.error {
            background: linear-gradient(135deg, #ffebee, #ffcdd2);
            color: #c62828;
            border-left: 4px solid #f44336;
        }

        .alert.info {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            color: #1565c0;
            border-left: 4px solid #2196f3;
        }

        .alert i {
            font-size: 1.1rem;
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

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #f0f0f0;
        }

        .footer p {
            margin-bottom: 0.5rem;
            color: #6c757d;
            font-size: 0.9rem;
        }

        a {
            color: #ea3672;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        a:hover {
            color: #d42b5f;
            text-decoration: underline;
        }

        /* Responsivo */
        @media (max-width: 768px) {
            .container {
                margin: 1rem;
                padding: 2rem 1.5rem;
                max-width: 95%;
            }

            h1 {
                font-size: 2.2rem;
            }

            .subtitle {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 1.5rem 1rem;
            }

            h1 {
                font-size: 1.8rem;
                flex-direction: column;
                gap: 0.25rem;
            }

            .subtitle {
                font-size: 0.85rem;
            }

            input {
                padding: 12px 14px;
            }

            .btn {
                padding: 14px;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>
            <i class="fas fa-user-plus"></i>
            <?php echo htmlspecialchars($titulo); ?>
        </h1>
        <p class="subtitle"><?php echo htmlspecialchars($subtitulo); ?></p>

        <?php if ($mensagem): ?>
            <div class="alert <?php echo htmlspecialchars($tipo_mensagem); ?>">
                <i class="fas fa-<?php echo $tipo_mensagem === 'success' ? 'check-circle' : ($tipo_mensagem === 'error' ? 'exclamation-triangle' : 'info-circle'); ?>"></i>
                <span><?php echo htmlspecialchars($mensagem); ?></span>
            </div>
        <?php endif; ?>

        

        <form method="POST" action="<?php echo htmlspecialchars($form_action); ?>">
            <div class="form-group">
                <label for="nome">Nome Completo *</label>
                <input type="text" id="nome" name="nome" required autofocus placeholder="Seu nome completo">
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required placeholder="seu@email.com">
            </div>

            <?php if ($modo_cliente): ?>
                <div class="form-group">
                    <label for="telefone">Telefone</label>
                    <input type="text" id="telefone" name="telefone" placeholder="(11) 99999-9999">
                </div>

                <div class="form-group">
                    <label for="senha">Senha *</label>
                    <input type="password" id="senha" name="senha" required placeholder="Mínimo 6 caracteres">
                </div>

                <div class="form-group">
                    <label for="confirmar_senha">Confirmar Senha *</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" required placeholder="Repita a senha">
                </div>
            <?php else: ?>
                <div class="form-group">
                    <label for="senha">Senha *</label>
                    <input type="password" id="senha" name="senha" required placeholder="Mínimo 6 caracteres">
                </div>

                <div class="form-group">
                    <label for="confirmar_senha">Confirmar Senha *</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" required placeholder="Repita a senha">
                </div>
            <?php endif; ?>

            <button type="submit" class="btn">
                <i class="fas fa-user-plus"></i>
                <?php echo htmlspecialchars($botao); ?>
            </button>
        </form>

        <div class="security-notice">
            <i class="fas fa-shield-alt"></i>
            <span>Seus dados estão protegidos</span>
        </div>

        <div class="footer">
            <?php if ($modo_cliente): ?>
                <p>Já tem conta? <a href="cliente/login.php">Faça login aqui</a></p>
                <p>Deseja criar um administrador ou fornecedor? <a href="criar_usuario.php?tipo=admin">Clique aqui</a></p>
                <p><a href="public/index.php">Voltar para o catálogo</a></p>
            <?php else: ?>
                <p>Já tem conta? <a href="admin/login.php">Entrar</a></p>
                <p><a href="public/index.php">Voltar para o catálogo</a></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
