<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Verificar se cliente está logado
if (!isset($_SESSION['cliente_id'])) {
    header('Location: ' . SITE_URL . 'cliente/login.php');
    exit();
}

$cliente_id = $_SESSION['cliente_id'];
$cliente_nome = $_SESSION['cliente_nome'] ?? 'Cliente';

// Obter itens do carrinho
$sql = "SELECT c.*, p.nome as produto_nome, p.id as produto_id FROM carrinho c 
        JOIN produtos p ON c.produto_id = p.id 
        WHERE c.cliente_id = ? 
        ORDER BY c.data_adicao DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $cliente_id);
$stmt->execute();
$result = $stmt->get_result();
$itens_carrinho = $result->fetch_all(MYSQLI_ASSOC);

$total = 0;
foreach ($itens_carrinho as $item) {
    $total += $item['quantidade'] * $item['preco_unitario'];
}

// Remover item do carrinho via GET
if (isset($_GET['remover'])) {
    $item_id = intval($_GET['remover']);
    $stmt = $conn->prepare("DELETE FROM carrinho WHERE id = ? AND cliente_id = ?");
    $stmt->bind_param('ii', $item_id, $cliente_id);
    $stmt->execute();
    header('Location: ' . SITE_URL . 'cliente/carrinho.php');
    exit();
}

// Remover item do carrinho via POST (backwards compatibility)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remover_item'])) {
    $item_id = intval($_POST['remover_item']);
    $stmt = $conn->prepare("DELETE FROM carrinho WHERE id = ? AND cliente_id = ?");
    $stmt->bind_param('ii', $item_id, $cliente_id);
    $stmt->execute();
    header('Location: ' . SITE_URL . 'cliente/carrinho.php');
    exit();
}

// Fazer checkout
if (isset($_GET['checkout']) && $_GET['checkout'] == '1') {
    if (count($itens_carrinho) > 0) {
        // Obter informações do cliente
        $stmt = $conn->prepare("SELECT email, telefone FROM clientes WHERE id = ?");
        $stmt->bind_param('i', $cliente_id);
        $stmt->execute();
        $cliente_info = $stmt->get_result()->fetch_assoc();

        // Preparar mensagem para WhatsApp
        $mensagem_wa = "Olá! Gostaria de encomendar os seguintes produtos:\n\n";
        foreach ($itens_carrinho as $item) {
            $mensagem_wa .= "• " . $item['produto_nome'] . " (Qtd: " . $item['quantidade'] . ") - " . formatar_moeda($item['quantidade'] * $item['preco_unitario']) . "\n";
        }
        $mensagem_wa .= "\nTotal: " . formatar_moeda($total);

        // Redirecionar para WhatsApp
        $numero_whatsapp = '5511999999999'; // Alterar com número do negócio
        $url_wa = "https://wa.me/" . $numero_whatsapp . "?text=" . urlencode($mensagem_wa);
        header('Location: ' . $url_wa);
        exit();
    }
}

$titulo_pagina = 'Carrinho';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<style>
    .carrinho-hero {
        background: linear-gradient(135deg, var(--cor-primaria), #FF85B3);
        color: white;
        padding: 3rem 2rem;
        text-align: center;
        margin-bottom: 2rem;
    }

    .carrinho-hero h1 {
        font-size: 2.5rem;
        margin: 0 0 0.5rem;
    }

    .carrinho-hero p {
        font-size: 1.1rem;
        opacity: 0.95;
        margin: 0;
    }

    .carrinho-container {
        max-width: 900px;
        margin: 0 auto;
    }

    .carrinho-vazio {
        text-align: center;
        padding: 3rem 2rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .carrinho-vazio i {
        font-size: 4rem;
        color: #ddd;
        margin-bottom: 1rem;
    }

    .carrinho-vazio p {
        font-size: 1.2rem;
        color: #666;
        margin-bottom: 2rem;
    }

    .carrinho-itens {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .carrinho-item {
        padding: 1.5rem;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 2rem;
    }

    .carrinho-item:last-child {
        border-bottom: none;
    }

    .item-info {
        flex: 1;
    }

    .item-nome {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 0.5rem;
    }

    .item-detalhes {
        font-size: 0.95rem;
        color: #666;
    }

    .item-preco-total {
        font-size: 1.3rem;
        font-weight: bold;
        color: var(--cor-primaria);
        min-width: 120px;
        text-align: right;
    }

    .item-acoes {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .btn-remover {
        background: #f44336;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.9rem;
    }

    .btn-remover:hover {
        background: #d32f2f;
    }

    .carrinho-resumo {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        padding: 2rem;
        margin-top: 2rem;
        text-align: right;
    }

    .resumo-linha {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
        font-size: 1.1rem;
    }

    .resumo-total {
        font-size: 1.5rem;
        font-weight: bold;
        color: var(--cor-primaria);
        border-top: 2px solid #eee;
        padding-top: 1rem;
    }

    .carrinho-acoes {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        justify-content: center;
    }

    .btn-voltar, .btn-finalizar {
        padding: 0.85rem 1.5rem;
        border-radius: 4px;
        text-decoration: none;
        font-weight: 600;
        border: none;
        cursor: pointer;
        font-size: 1rem;
    }

    .btn-voltar {
        background: #ccc;
        color: #333;
    }

    .btn-voltar:hover {
        background: #bbb;
    }

    .btn-finalizar {
        background: linear-gradient(135deg, var(--cor-primaria), #FF85B3);
        color: white;
    }

    .btn-finalizar:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 107, 157, 0.4);
    }
</style>

<div class="carrinho-hero">
    <h1><i class="fas fa-shopping-cart"></i> Carrinho de Compras</h1>
    <p>Bem-vindo, <?php echo htmlspecialchars($cliente_nome); ?>!</p>
</div>

<div class="carrinho-container">
    <?php if (count($itens_carrinho) == 0): ?>
        <div class="carrinho-vazio">
            <i class="fas fa-shopping-bag"></i>
            <p>Seu carrinho está vazio</p>
            <a href="<?php echo SITE_URL; ?>public/index.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Continuar Comprando
            </a>
        </div>
    <?php else: ?>
        <div class="carrinho-itens">
            <?php foreach ($itens_carrinho as $item): ?>
                <div class="carrinho-item">
                    <div class="item-info">
                        <div class="item-nome"><?php echo htmlspecialchars($item['produto_nome']); ?></div>
                        <div class="item-detalhes">
                            Quantidade: <strong><?php echo $item['quantidade']; ?></strong> | 
                            Preço unitário: <strong><?php echo formatar_moeda($item['preco_unitario']); ?></strong>
                        </div>
                    </div>
                    <div class="item-preco-total">
                        <?php echo formatar_moeda($item['quantidade'] * $item['preco_unitario']); ?>
                    </div>
                    <a href="<?php echo SITE_URL; ?>cliente/carrinho.php?remover=<?php echo $item['id']; ?>" class="btn-remover" onclick="return confirm('Tem certeza que deseja remover este item?')">
                        <i class="fas fa-trash"></i> Remover
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="carrinho-resumo">
            <div class="resumo-linha">
                <span>Subtotal (<?php echo count($itens_carrinho); ?> itens):</span>
                <span><?php echo formatar_moeda($total); ?></span>
            </div>
            <div class="resumo-total">
                Total: <?php echo formatar_moeda($total); ?>
            </div>
        </div>

        <div class="carrinho-acoes">
            <a href="<?php echo SITE_URL; ?>public/index.php" class="btn-voltar">
                <i class="fas fa-arrow-left"></i> Continuar Comprando
            </a>
            <a href="<?php echo SITE_URL; ?>cliente/carrinho.php?checkout=1" class="btn-finalizar">
                <i class="fab fa-whatsapp"></i> Finalizar Compra
            </a>
        </div>
    <?php endif; ?>

    <div style="text-align: center; margin-top: 3rem;">
        <a href="<?php echo SITE_URL; ?>cliente/sair.php" style="color: #FF6B9D; text-decoration: none;">
            <i class="fas fa-sign-out-alt"></i> Sair da conta
        </a>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
