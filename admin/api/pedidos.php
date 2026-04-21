<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/functions.php';

verificar_autenticacao();

$acao = $_GET['acao'] ?? '';
$resposta = ['sucesso' => false, 'mensagem' => ''];

switch ($acao) {
    case 'obter':
        $id = intval($_GET['id'] ?? 0);
        if ($id) {
            $pedido = obter_pedido_por_id($id);
            if ($pedido) {
                $resposta['sucesso'] = true;
                $resposta['dados'] = $pedido;
            }
        }
        break;

    case 'listar':
        $status = $_GET['status'] ?? '';
        $pedidos = obter_pedidos($status ? $status : null);
        $resposta['sucesso'] = true;
        $resposta['dados'] = $pedidos;
        break;

    case 'atualizar_status':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pedido_id = intval($_POST['pedido_id'] ?? 0);
            $status = limpar_entrada($_POST['status'] ?? '');

            if ($pedido_id && atualizar_status_pedido($pedido_id, $status)) {
                $resposta['sucesso'] = true;
                $resposta['mensagem'] = 'Status atualizado com sucesso';
            } else {
                $resposta['mensagem'] = 'Erro ao atualizar status';
            }
        }
        break;

    case 'lucro':
        $pedido_id = intval($_GET['id'] ?? 0);
        if ($pedido_id) {
            $lucro_info = calcular_lucro_pedido($pedido_id);
            $resposta['sucesso'] = true;
            $resposta['dados'] = $lucro_info;
        }
        break;

    default:
        $resposta['mensagem'] = 'Ação inválida';
}

echo json_encode($resposta);
?>
