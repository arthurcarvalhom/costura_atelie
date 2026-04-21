<?php
/**
 * Funções Globais do Sistema
 */

// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// ==========================================
// AUTENTICAÇÃO
// ==========================================

function verificar_autenticacao() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ' . ADMIN_URL . 'login.php');
        exit();
    }
}

function fazer_login($email, $senha) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id, nome, senha FROM usuarios WHERE email = ? AND ativo = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();
        if (password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            return true;
        }
    }
    return false;
}

function fazer_logout() {
    session_destroy();
    header('Location: ' . ADMIN_URL . 'login.php');
    exit();
}

// ==========================================
// FUNÇÕES DE PRODUTOS
// ==========================================

function obter_produtos($filtro_categoria = null) {
    global $conn;
    
    $sql = "SELECT p.*, c.nome as categoria_nome FROM produtos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id 
            WHERE p.ativo = 1";
    
    if ($filtro_categoria) {
        $sql .= " AND p.categoria_id = " . intval($filtro_categoria);
    }
    
    $sql .= " ORDER BY p.data_criacao DESC";
    $result = $conn->query($sql);
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function obter_produto_por_id($id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT p.*, c.nome as categoria_nome 
        FROM produtos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        WHERE p.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function adicionar_produto($nome, $descricao, $categoria_id, $preco, $custo, $material, $foto) {
    global $conn;
    
    $stmt = $conn->prepare("
        INSERT INTO produtos (nome, descricao, categoria_id, preco_venda, custo_producao, material, foto_url)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param("ssidds", $nome, $descricao, $categoria_id, $preco, $custo, $material, $foto);
    $resultado = $stmt->execute();
    
    return $resultado ? $stmt->insert_id : false;
}

function atualizar_produto($id, $nome, $descricao, $categoria_id, $preco, $custo, $material) {
    global $conn;
    
    $stmt = $conn->prepare("
        UPDATE produtos 
        SET nome = ?, descricao = ?, categoria_id = ?, preco_venda = ?, 
            custo_producao = ?, material = ?
        WHERE id = ?
    ");
    
    $stmt->bind_param("ssidds", $nome, $descricao, $categoria_id, $preco, $custo, $material, $id);
    return $stmt->execute();
}

function deletar_produto($id) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE produtos SET ativo = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// ==========================================
// FUNÇÕES DE ESTOQUE
// ==========================================

function obter_estoque() {
    global $conn;
    
    $result = $conn->query("
        SELECT * FROM materias_primas 
        ORDER BY tipo, nome
    ");
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function obter_materia_prima_por_id($id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM materias_primas WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function adicionar_materia_prima($nome, $tipo, $unidade, $preco, $quantidade_minima) {
    global $conn;
    
    $stmt = $conn->prepare("
        INSERT INTO materias_primas (nome, tipo, unidade, preco_unitario, quantidade_minima)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param("sssdd", $nome, $tipo, $unidade, $preco, $quantidade_minima);
    return $stmt->execute();
}

function registrar_movimentacao($materia_prima_id, $tipo, $quantidade, $descricao = '') {
    global $conn;
    
    // Registrar a movimentação
    $stmt = $conn->prepare("
        INSERT INTO movimentacao_estoque (materia_prima_id, tipo, quantidade, descricao)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->bind_param("idds", $materia_prima_id, $tipo, $quantidade, $descricao);
    $resultado = $stmt->execute();
    
    if ($resultado) {
        // Atualizar o estoque
        if ($tipo === 'entrada') {
            $conn->query("UPDATE materias_primas SET quantidade_estoque = quantidade_estoque + $quantidade WHERE id = $materia_prima_id");
        } else {
            $conn->query("UPDATE materias_primas SET quantidade_estoque = quantidade_estoque - $quantidade WHERE id = $materia_prima_id");
        }
    }
    
    return $resultado;
}

function obter_alertas_estoque_baixo() {
    global $conn;
    
    $result = $conn->query("
        SELECT * FROM materias_primas 
        WHERE quantidade_estoque <= quantidade_minima
        ORDER BY quantidade_estoque ASC
    ");
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

// ==========================================
// FUNÇÕES DE PEDIDOS
// ==========================================

function obter_pedidos($status = null) {
    global $conn;
    
    $sql = "SELECT p.*, c.nome as cliente_nome, COUNT(pi.id) as quantidade_itens
            FROM pedidos p
            LEFT JOIN clientes c ON p.cliente_id = c.id
            LEFT JOIN pedido_itens pi ON p.id = pi.pedido_id";
    
    if ($status) {
        $sql .= " WHERE p.status = '$status'";
    }
    
    $sql .= " GROUP BY p.id ORDER BY p.data_entrega_prevista ASC";
    
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function obter_pedido_por_id($id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT p.*, c.nome as cliente_nome, c.email, c.telefone
        FROM pedidos p
        LEFT JOIN clientes c ON p.cliente_id = c.id
        WHERE p.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function obter_itens_pedido($pedido_id) {
    global $conn;
    
    $result = $conn->query("
        SELECT pi.*, prod.nome as produto_nome
        FROM pedido_itens pi
        JOIN produtos prod ON pi.produto_id = prod.id
        WHERE pi.pedido_id = $pedido_id
    ");
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function criar_pedido($cliente_id, $data_entrega, $descricao, $valor_total) {
    global $conn;
    
    $stmt = $conn->prepare("
        INSERT INTO clientes (nombre) VALUES (?)
        ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)
    ");
    
    $stmt = $conn->prepare("
        INSERT INTO pedidos (cliente_id, data_entrega_prevista, descricao_customizacao, valor_total)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->bind_param("issd", $cliente_id, $data_entrega, $descricao, $valor_total);
    
    if ($stmt->execute()) {
        return $stmt->insert_id;
    }
    return false;
}

function adicionar_item_pedido($pedido_id, $produto_id, $quantidade, $preco) {
    global $conn;
    
    $stmt = $conn->prepare("
        INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, preco_unitario)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->bind_param("iid", $pedido_id, $produto_id, $quantidade, $preco);
    return $stmt->execute();
}

function atualizar_status_pedido($pedido_id, $novo_status) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE pedidos SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $novo_status, $pedido_id);
    return $stmt->execute();
}

// ==========================================
// FUNÇÕES DE AGENDA
// ==========================================

function obter_agenda_producao($data_inicio = null, $data_fim = null) {
    global $conn;
    
    $sql = "SELECT ap.*, p.id as pedido_id, c.nome as cliente_nome
            FROM agenda_producao ap
            JOIN pedidos p ON ap.pedido_id = p.id
            LEFT JOIN clientes c ON p.cliente_id = c.id
            WHERE ap.status != 'concluida'";
    
    if ($data_inicio && $data_fim) {
        $sql .= " AND ap.data_prevista BETWEEN '$data_inicio' AND '$data_fim'";
    }
    
    $sql .= " ORDER BY ap.data_prevista ASC";
    
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function criar_tarefa_producao($pedido_id, $tipo_tarefa, $data_prevista) {
    global $conn;
    
    $stmt = $conn->prepare("
        INSERT INTO agenda_producao (pedido_id, tipo_tarefa, data_prevista)
        VALUES (?, ?, ?)
    ");
    
    $stmt->bind_param("iss", $pedido_id, $tipo_tarefa, $data_prevista);
    return $stmt->execute();
}

// ==========================================
// FUNÇÕES FINANCEIRAS
// ==========================================

function calcular_lucro_pedido($pedido_id) {
    global $conn;
    
    $result = $conn->query("
        SELECT valor_total FROM pedidos WHERE id = $pedido_id
    ")->fetch_assoc();
    
    $valor_venda = $result['valor_total'];
    
    // Calcular custo de materiais
    $custo_result = $conn->query("
        SELECT COALESCE(SUM(mp.preco_unitario * pm.quantidade_necessaria), 0) as custo_total
        FROM pedido_itens pi
        JOIN produtos prod ON pi.produto_id = prod.id
        LEFT JOIN produto_materiais pm ON prod.id = pm.produto_id
        LEFT JOIN materias_primas mp ON pm.materia_prima_id = mp.id
        WHERE pi.pedido_id = $pedido_id
    ")->fetch_assoc();
    
    $custo = $custo_result['custo_total'];
    $lucro = $valor_venda - $custo;
    
    return [
        'valor_venda' => $valor_venda,
        'custo' => $custo,
        'lucro' => $lucro,
        'percentual_lucro' => ($valor_venda > 0) ? ($lucro / $valor_venda) * 100 : 0
    ];
}

function obter_relatorio_financeiro($data_inicio = null, $data_fim = null) {
    global $conn;
    
    $sql = "SELECT * FROM view_custo_pedidos WHERE status != 'cancelado'";
    
    if ($data_inicio && $data_fim) {
        $sql .= " AND data_pedido BETWEEN '$data_inicio' AND '$data_fim'";
    }
    
    $sql .= " ORDER BY data_pedido DESC";
    
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// ==========================================
// FUNÇÕES DE ARQUIVO
// ==========================================

function upload_imagem_produto($arquivo) {
    // Verificar se é uma imagem válida
    $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    
    if (!in_array($arquivo['type'], $tipos_permitidos)) {
        return ['sucesso' => false, 'mensagem' => 'Tipo de arquivo não permitido'];
    }
    
    if ($arquivo['size'] > MAX_UPLOAD_SIZE) {
        return ['sucesso' => false, 'mensagem' => 'Arquivo muito grande'];
    }
    
    // Criar nome único
    $novo_nome = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . pathinfo($arquivo['name'], PATHINFO_EXTENSION);
    $caminho_destino = UPLOAD_DIR . $novo_nome;
    
    if (move_uploaded_file($arquivo['tmp_name'], $caminho_destino)) {
        return [
            'sucesso' => true,
            'arquivo' => $novo_nome,
            'url' => ASSETS_URL . 'uploads/produtos/' . $novo_nome
        ];
    }
    
    return ['sucesso' => false, 'mensagem' => 'Erro ao enviar arquivo'];
}

// ==========================================
// FUNÇÕES UTILITÁRIAS
// ==========================================

function limpar_entrada($dados) {
    global $conn;
    return $conn->real_escape_string(htmlspecialchars($dados, ENT_QUOTES, 'UTF-8'));
}

function formatar_moeda($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

function formatar_data($data) {
    return date('d/m/Y', strtotime($data));
}

function formatar_data_hora($data) {
    return date('d/m/Y H:i', strtotime($data));
}

function obter_categorias() {
    global $conn;
    
    $result = $conn->query("SELECT * FROM categorias ORDER BY nome");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function obter_categoria_por_id($id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM categorias WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// ==========================================
// FUNÇÕES DE CARRINHO DO CLIENTE
// ==========================================

function adicionar_carrinho($cliente_id, $produto_id, $quantidade = 1) {
    global $conn;
    
    // Obter preço do produto
    $stmt = $conn->prepare("SELECT preco_venda FROM produtos WHERE id = ?");
    $stmt->bind_param("i", $produto_id);
    $stmt->execute();
    $produto = $stmt->get_result()->fetch_assoc();
    
    if (!$produto) {
        return false;
    }
    
    $preco = $produto['preco_venda'];
    
    // Verificar se já existe no carrinho
    $check = $conn->prepare("SELECT id FROM carrinho WHERE cliente_id = ? AND produto_id = ?");
    $check->bind_param("ii", $cliente_id, $produto_id);
    $check->execute();
    $resultado = $check->get_result();
    
    if ($resultado->num_rows > 0) {
        // Atualizar quantidade
        $update = $conn->prepare("UPDATE carrinho SET quantidade = quantidade + ? WHERE cliente_id = ? AND produto_id = ?");
        $update->bind_param("iii", $quantidade, $cliente_id, $produto_id);
        return $update->execute();
    } else {
        // Inserir novo item
        $insert = $conn->prepare("INSERT INTO carrinho (cliente_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
        $insert->bind_param("iiid", $cliente_id, $produto_id, $quantidade, $preco);
        return $insert->execute();
    }
}

function obter_carrinho($cliente_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT c.*, p.nome as produto_nome FROM carrinho c 
        JOIN produtos p ON c.produto_id = p.id 
        WHERE c.cliente_id = ? 
        ORDER BY c.data_adicao DESC
    ");
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function remover_do_carrinho($item_id, $cliente_id) {
    global $conn;
    
    $stmt = $conn->prepare("DELETE FROM carrinho WHERE id = ? AND cliente_id = ?");
    $stmt->bind_param("ii", $item_id, $cliente_id);
    return $stmt->execute();
}

function limpar_carrinho($cliente_id) {
    global $conn;
    
    $stmt = $conn->prepare("DELETE FROM carrinho WHERE cliente_id = ?");
    $stmt->bind_param("i", $cliente_id);
    return $stmt->execute();
}

function contar_itens_carrinho($cliente_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM carrinho WHERE cliente_id = ?");
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['total'];
}

function deletar_materia_prima($id) {
    global $conn;
    
    $stmt = $conn->prepare("DELETE FROM materias_primas WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

?>
