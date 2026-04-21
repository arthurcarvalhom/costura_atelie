<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação do Sistema - Costura Ateliê</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 2rem;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        h1 {
            color: #FF6B9D;
            margin-bottom: 2rem;
            text-align: center;
        }
        .check-item {
            background: white;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .check-item.success {
            border-left: 4px solid #4CAF50;
        }
        .check-item.error {
            border-left: 4px solid #F44336;
        }
        .check-item.warning {
            border-left: 4px solid #FF9800;
        }
        .status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: bold;
        }
        .icon {
            font-size: 1.5rem;
        }
        .success .icon { color: #4CAF50; }
        .error .icon { color: #F44336; }
        .warning .icon { color: #FF9800; }
        .detail {
            font-size: 0.85rem;
            color: #999;
            margin-top: 0.5rem;
        }
        .summary {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            text-align: center;
            margin-top: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .summary h2 {
            color: #FF6B9D;
            margin-bottom: 1rem;
        }
        .summary p {
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #FF6B9D;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 1rem;
        }
        .btn:hover {
            background: #FF5A8D;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Verificação do Sistema - Costura Ateliê</h1>

        <?php
        $erros = [];
        $avisos = [];
        $sucessos = [];

        // Verificar PHP
        if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
            $sucessos[] = ['PHP', 'Versão ' . PHP_VERSION, 'PHP 7.4 ou superior'];
        } else {
            $erros[] = ['PHP', PHP_VERSION, 'PHP 7.4 ou superior necessário'];
        }

        // Verificar Extensões
        $extensoes = ['mysqli', 'json', 'fileinfo'];
        foreach ($extensoes as $ext) {
            if (extension_loaded($ext)) {
                $sucessos[] = ['Extensão ' . $ext, 'Carregada', ''];
            } else {
                $erros[] = ['Extensão ' . $ext, 'Não carregada', 'Abra php.ini e descomente a linha'];
            }
        }

        // Verificar Banco de Dados
        require_once __DIR__ . '/config/database.php';
        
        if (@$conn->ping()) {
            $sucessos[] = ['Banco de Dados', 'Conectado a ' . DB_NAME, ''];
            
            // Verificar tabelas
            $result = $conn->query("SHOW TABLES FROM " . DB_NAME);
            $num_tabelas = $result->num_rows;
            
            if ($num_tabelas > 10) {
                $sucessos[] = ['Tabelas do Banco', $num_tabelas . ' tabelas encontradas', ''];
            } else {
                $avisos[] = ['Tabelas do Banco', $num_tabelas . ' tabelas', 'Importe schema.sql via phpMyAdmin'];
            }
        } else {
            $erros[] = ['Banco de Dados', 'Desconectado', 'Verifique as credenciais em config/database.php'];
        }

        // Verificar Pastas
        $pastas = [
            'config' => 'config/',
            'includes' => 'includes/',
            'admin' => 'admin/',
            'public' => 'public/',
            'assets' => 'assets/',
            'sql' => 'sql/',
        ];

        foreach ($pastas as $nome => $caminho) {
            if (is_dir($caminho)) {
                $sucessos[] = ['Pasta ' . $nome, 'Existe', $caminho];
            } else {
                $erros[] = ['Pasta ' . $nome, 'Não encontrada', 'A pasta ' . $caminho . ' é obrigatória'];
            }
        }

        // Verificar Arquivos
        $arquivos = [
            'schema.sql' => 'sql/schema.sql',
            'database.php' => 'config/database.php',
            'functions.php' => 'includes/functions.php',
            'style.css' => 'assets/css/style.css',
            'script.js' => 'assets/js/script.js',
        ];

        foreach ($arquivos as $nome => $caminho) {
            if (file_exists($caminho)) {
                $sucessos[] = ['Arquivo ' . $nome, 'Encontrado', $caminho];
            } else {
                $erros[] = ['Arquivo ' . $nome, 'Não encontrado', 'A arquivo ' . $caminho . ' é obrigatório'];
            }
        }

        // Verificar Permissões
        $uploads = 'assets/uploads/produtos/';
        if (is_writable($uploads)) {
            $sucessos[] = ['Permissão upload', 'Escrita permitida', $uploads];
        } else {
            if (!is_dir($uploads)) {
                $erros[] = ['Permissão upload', 'Pasta não existe', 'Crie a pasta: ' . $uploads];
            } else {
                $avisos[] = ['Permissão upload', 'Readwrite', 'Verifique permissões (chmod 777)'];
            }
        }

        // Exibir resultados
        echo '<div class="results">';

        // Sucessos
        foreach ($sucessos as $check) {
            echo '
            <div class="check-item success">
                <div>
                    <strong>' . $check[0] . '</strong>
                    <div class="detail">' . $check[1] . '</div>
                </div>
                <div class="status">
                    <span class="icon">✓</span>
                    <span>OK</span>
                </div>
            </div>';
        }

        // Avisos
        foreach ($avisos as $check) {
            echo '
            <div class="check-item warning">
                <div>
                    <strong>' . $check[0] . '</strong>
                    <div class="detail">' . $check[1] . ' - ' . $check[2] . '</div>
                </div>
                <div class="status">
                    <span class="icon">⚠</span>
                    <span>Aviso</span>
                </div>
            </div>';
        }

        // Erros
        foreach ($erros as $check) {
            echo '
            <div class="check-item error">
                <div>
                    <strong>' . $check[0] . '</strong>
                    <div class="detail">' . $check[1] . ' - ' . $check[2] . '</div>
                </div>
                <div class="status">
                    <span class="icon">✗</span>
                    <span>Erro</span>
                </div>
            </div>';
        }

        echo '</div>';

        // Resumo
        $total_checks = count($sucessos) + count($avisos) + count($erros);
        $percentual = ($total_checks > 0) ? round((count($sucessos) / $total_checks) * 100) : 0;

        echo '
        <div class="summary">
            <h2>📊 Resumo da Verificação</h2>
            <p><strong>Testes Executados:</strong> ' . $total_checks . '</p>
            <p><strong>Sucessos:</strong> ' . count($sucessos) . ' | <strong>Avisos:</strong> ' . count($avisos) . ' | <strong>Erros:</strong> ' . count($erros) . '</p>
            <p><strong>Status:</strong> ' . $percentual . '% ✓</p>';

        if (count($erros) === 0 && count($avisos) === 0) {
            echo '<p style="color: #4CAF50; font-weight: bold;">🎉 Sistema pronto para uso!</p>';
            echo '<a href="admin/" class="btn">Ir para o Painel</a>';
            echo '<a href="public/" class="btn" style="margin-left: 0.5rem;">Ver Catálogo</a>';
        } elseif (count($erros) > 0) {
            echo '<p style="color: #F44336; font-weight: bold;">❌ Existem erros que precisam ser corrigidos.</p>';
        } else {
            echo '<p style="color: #FF9800; font-weight: bold;">⚠️ Há avisos, mas o sistema pode funcionar.</p>';
        }

        echo '</div>';
        ?>
    </div>
</body>
</html>
