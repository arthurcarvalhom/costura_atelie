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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #FF6B9D, #FF85B3);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            background: white;
            padding: 3rem;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 420px;
        }
        h1 {
            color: #FF6B9D;
            margin-bottom: 0.75rem;
            text-align: center;
        }
        p.subtitle {
            color: #666;
            text-align: center;
            margin-bottom: 1.75rem;
            font-size: 0.98rem;
        }
        .form-group {
            margin-bottom: 1.25rem;
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
        .btn {
            width: 100%;
            padding: 0.85rem;
            background: linear-gradient(135deg, #FF6B9D, #FF85B3);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 157, 0.4);
        }
        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.25rem;
        }
        .alert.success {
            background: #C8E6C9;
            color: #2E7D32;
        }
        .alert.error {
            background: #FFCDD2;
            color: #C62828;
        }
        .alert.info {
            background: #B3E5FC;
            color: #01579B;
        }
        .footer {
            text-align: center;
            margin-top: 1.5rem;
            color: #999;
            font-size: 0.9rem;
        }
        a {
            color: #FF6B9D;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($titulo); ?></h1>
        <p class="subtitle"><?php echo htmlspecialchars($subtitulo); ?></p>

        <?php if ($mensagem): ?>
            <div class="alert <?php echo htmlspecialchars($tipo_mensagem); ?>"><?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>

        

        <form method="POST" action="<?php echo htmlspecialchars($form_action); ?>">
            <div class="form-group">
                <label for="nome">Nome Completo *</label>
                <input type="text" id="nome" name="nome" required autofocus placeholder="Seu nome">
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

            <button type="submit" class="btn"><?php echo htmlspecialchars($botao); ?></button>
        </form>

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
