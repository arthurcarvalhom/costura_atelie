<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/functions.php';

$resposta = ['sucesso' => false, 'mensagem' => ''];

// Verificar se há cliente logado
if (!isset($_SESSION['cliente_id'])) {
    $resposta['mensagem'] = 'Você precisa estar logado para adicionar itens ao carrinho';
    echo json_encode($resposta);
    exit();
}

$cliente_id = $_SESSION['cliente_id'];
$acao = $_GET['acao'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($acao === 'adicionar') {
        $produto_id = intval($_POST['produto_id'] ?? 0);
        $quantidade = intval($_POST['quantidade'] ?? 1);

        if ($produto_id > 0 && $quantidade > 0) {
            if (adicionar_carrinho($cliente_id, $produto_id, $quantidade)) {
                $resposta['sucesso'] = true;
                $resposta['mensagem'] = 'Produto adicionado ao carrinho!';
                $resposta['total_itens'] = contar_itens_carrinho($cliente_id);
            } else {
                $resposta['mensagem'] = 'Erro ao adicionar ao carrinho';
            }
        } else {
            $resposta['mensagem'] = 'Dados inválidos';
        }
    } elseif ($acao === 'remover') {
        $item_id = intval($_POST['item_id'] ?? 0);
        if ($item_id > 0) {
            if (remover_do_carrinho($item_id, $cliente_id)) {
                $resposta['sucesso'] = true;
                $resposta['mensagem'] = 'Produto removido do carrinho';
                $resposta['total_itens'] = contar_itens_carrinho($cliente_id);
            } else {
                $resposta['mensagem'] = 'Erro ao remover do carrinho';
            }
        }
    } else {
        $resposta['mensagem'] = 'Ação inválida';
    }
} else {
    if ($acao === 'contar') {
        $resposta['sucesso'] = true;
        $resposta['total_itens'] = contar_itens_carrinho($cliente_id);
    } else {
        $resposta['mensagem'] = 'Ação inválida';
    }
}

echo json_encode($resposta);
?>
