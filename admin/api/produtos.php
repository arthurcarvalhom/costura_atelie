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
            $produto = obter_produto_por_id($id);
            if ($produto) {
                $resposta['sucesso'] = true;
                $resposta['dados'] = $produto;
            } else {
                $resposta['mensagem'] = 'Produto não encontrado';
            }
        }
        break;

    case 'listar':
        $categoria = $_GET['categoria'] ?? '';
        $produtos = obter_produtos($categoria);
        $resposta['sucesso'] = true;
        $resposta['dados'] = $produtos;
        break;

    case 'deletar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            if ($id && deletar_produto($id)) {
                $resposta['sucesso'] = true;
                $resposta['mensagem'] = 'Produto deletado com sucesso';
            } else {
                $resposta['mensagem'] = 'Erro ao deletar produto';
            }
        }
        break;

    default:
        $resposta['mensagem'] = 'Ação inválida';
}

echo json_encode($resposta);
?>
