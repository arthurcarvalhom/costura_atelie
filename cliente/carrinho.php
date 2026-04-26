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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $itens_selecionados = $_POST['itens_selecionados'] ?? [];
    if (count($itens_selecionados) > 0) {
        // Obter informações do cliente
        $stmt = $conn->prepare("SELECT email, telefone FROM clientes WHERE id = ?");
        $stmt->bind_param('i', $cliente_id);
        $stmt->execute();
        $cliente_info = $stmt->get_result()->fetch_assoc();

        // Preparar mensagem para WhatsApp
        $mensagem_wa = "Olá! Gostaria de encomendar os seguintes produtos:\n\n";
        $total_selecionado = 0;
        foreach ($itens_carrinho as $item) {
            if (in_array($item['id'], $itens_selecionados)) {
                $mensagem_wa .= "• " . $item['produto_nome'] . " (Qtd: " . $item['quantidade'] . ") - " . formatar_moeda($item['quantidade'] * $item['preco_unitario']) . "\n";
                $total_selecionado += $item['quantidade'] * $item['preco_unitario'];
            }
        }
        $mensagem_wa .= "\nTotal: " . formatar_moeda($total_selecionado);

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

<div class="cart-container">
    <h1 class="cart-title">🛒 Carrinho de Compras</h1>
    
    <div class="steps">
        <div class="step active">Carrinho</div>
        <div class="step">Dados</div>
        <div class="step">Pagamento</div>
        <div class="step">Finalizado</div>
    </div>
    
    <?php if (count($itens_carrinho) == 0): ?>
        <div class="carrinho-vazio" style="text-align: center; background: #fff; padding: 50px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
            <i class="fas fa-shopping-bag" style="font-size: 3rem; color: #ccc;"></i>
            <p style="font-size: 1.2rem; color: #666; margin: 20px 0;">Seu carrinho está vazio</p>
            <a href="<?php echo SITE_URL; ?>public/index.php" class="btn-continue">
                <i class="fas fa-arrow-left"></i> Continuar Comprando
            </a>
        </div>
    <?php else: ?>
        <form id="carrinho-form" method="POST" action="<?php echo SITE_URL; ?>cliente/carrinho.php">
            <div class="products">
                <div style="margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-weight: 600; color: #333;">
                        <input type="checkbox" id="selecionar-todos" style="width: 18px; height: 18px;">
                        <span>Selecionar Todos</span>
                    </label>
                </div>
                <?php foreach ($itens_carrinho as $item): ?>
                    <div class="product-card">
                        <div class="product-info">
                            <input type="checkbox" name="itens_selecionados[]" value="<?php echo $item['id']; ?>" class="product-checkbox item-checkbox" checked>
                            <div class="product-details">
                                <h3><?php echo htmlspecialchars($item['produto_nome']); ?></h3>
                                <p>Quantidade: <strong><?php echo $item['quantidade']; ?></strong> | Preço unitário: <strong><?php echo formatar_moeda($item['preco_unitario']); ?></strong></p>
                            </div>
                        </div>
                        <div class="product-price">
                            <?php echo formatar_moeda($item['quantidade'] * $item['preco_unitario']); ?>
                        </div>
                        <a href="<?php echo SITE_URL; ?>cliente/carrinho.php?remover=<?php echo $item['id']; ?>" class="remove-btn" onclick="return confirm('Tem certeza que deseja remover este item?')">
                            <i class="fas fa-trash"></i> Remover
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="summary">
                <h2>Resumo do Pedido</h2>
                <div class="summary-item">
                    <span>Subtotal (<span id="itens-count"><?php echo count($itens_carrinho); ?></span> itens):</span>
                    <span id="subtotal"><?php echo formatar_moeda($total); ?></span>
                </div>
                <div class="summary-item">
                    <span>Frete:</span>
                    <span>Grátis</span>
                </div>
                <div class="summary-item total">
                    <span>Total:</span>
                    <span id="total"><?php echo formatar_moeda($total); ?></span>
                </div>
            </div>

            <button type="submit" name="checkout" value="1" class="checkout-btn" id="btn-finalizar">
                <i class="fab fa-whatsapp"></i> Finalizar Compra (<span id="checkout-count"><?php echo count($itens_carrinho); ?></span> itens)
            </button>
            
            <div class="security">
                🔒 Compra 100% segura
            </div>
        </form>
    <?php endif; ?>

    <div style="text-align: center; margin-top: 3rem;">
        <a href="<?php echo SITE_URL; ?>cliente/sair.php" style="color: #FF6B9D; text-decoration: none;">
            <i class="fas fa-sign-out-alt"></i> Sair da conta
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    const selectAllCheckbox = document.getElementById('selecionar-todos');
    const subtotalElement = document.getElementById('subtotal');
    const totalElement = document.getElementById('total');
    const itensCountElement = document.getElementById('itens-count');
    const checkoutCountElement = document.getElementById('checkout-count');
    const btnFinalizar = document.getElementById('btn-finalizar');

    // Dados dos itens (passados do PHP)
    const itensData = <?php echo json_encode(array_map(function($item) {
        return [
            'id' => $item['id'],
            'preco' => $item['quantidade'] * $item['preco_unitario']
        ];
    }, $itens_carrinho)); ?>;

    function atualizarTotal() {
        let total = 0;
        let count = 0;
        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const itemId = parseInt(checkbox.value);
                const itemData = itensData.find(item => item.id === itemId);
                if (itemData) {
                    total += itemData.preco;
                    count++;
                }
            }
        });

        subtotalElement.textContent = 'R$ ' + total.toFixed(2).replace('.', ',');
        totalElement.textContent = 'R$ ' + total.toFixed(2).replace('.', ',');
        itensCountElement.textContent = count;
        checkoutCountElement.textContent = count;

        // Desabilitar botão se nenhum item selecionado
        btnFinalizar.disabled = count === 0;
        btnFinalizar.style.opacity = count === 0 ? '0.5' : '1';
    }

    // Event listener para checkboxes individuais
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            atualizarTotal();
            // Verificar se todos estão selecionados
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            const noneChecked = Array.from(checkboxes).every(cb => !cb.checked);
            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = !allChecked && !noneChecked;
        });
    });

    // Event listener para "Selecionar Todos"
    selectAllCheckbox.addEventListener('change', function() {
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
        atualizarTotal();
    });

    // Inicializar
    atualizarTotal();
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
