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
            $materia = obter_materia_prima_por_id($id);
            if ($materia) {
                $resposta['sucesso'] = true;
                $resposta['dados'] = $materia;
            }
        }
        break;

    case 'listar':
        $estoque = obter_estoque();
        $resposta['sucesso'] = true;
        $resposta['dados'] = $estoque;
        break;

    case 'registrar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $materia_prima_id = intval($_POST['materia_prima_id'] ?? 0);
            $tipo = limpar_entrada($_POST['tipo'] ?? '');
            $quantidade = floatval($_POST['quantidade'] ?? 0);
            $descricao = limpar_entrada($_POST['descricao'] ?? '');

            if (registrar_movimentacao($materia_prima_id, $tipo, $quantidade, $descricao)) {
                $resposta['sucesso'] = true;
                $resposta['mensagem'] = 'Movimentação registrada com sucesso';
            } else {
                $resposta['mensagem'] = 'Erro ao registrar movimentação';
            }
        }
        break;

    case 'alertas':
        $alertas = obter_alertas_estoque_baixo();
        $resposta['sucesso'] = true;
        $resposta['dados'] = $alertas;
        break;

    default:
        $resposta['mensagem'] = 'Ação inválida';
}

echo json_encode($resposta);
?>
