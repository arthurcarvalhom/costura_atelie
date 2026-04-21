<?php
require_once __DIR__ . '/../includes/functions.php';

verificar_autenticacao();

$titulo_pagina = 'Estoque';
$acao = $_GET['acao'] ?? 'listar';
$materia_id = $_GET['id'] ?? null;
$mensagem = '';
$tipo_mensagem = '';

// Deletar matéria-prima
if ($acao === 'deletar' && $materia_id) {
    if (deletar_materia_prima($materia_id)) {
        $mensagem = 'Matéria-prima deletada com sucesso!';
        $tipo_mensagem = 'success';
    } else {
        $mensagem = 'Erro ao deletar matéria-prima';
        $tipo_mensagem = 'danger';
    }
    $acao = 'listar';
}

// Processar formulário de nova matéria-prima
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_form'])) {
    $acao_form = $_POST['acao_form'];

    if ($acao_form === 'adicionar_materia') {
        $nome = limpar_entrada($_POST['nome'] ?? '');
        $tipo = limpar_entrada($_POST['tipo'] ?? '');
        $unidade = limpar_entrada($_POST['unidade'] ?? '');
        $preco = floatval($_POST['preco'] ?? 0);
        $quantidade_minima = floatval($_POST['quantidade_minima'] ?? 5);

        if (adicionar_materia_prima($nome, $tipo, $unidade, $preco, $quantidade_minima)) {
            $mensagem = 'Matéria-prima adicionada com sucesso!';
            $tipo_mensagem = 'success';
            $acao = 'listar';
        } else {
            $mensagem = 'Erro ao adicionar matéria-prima';
            $tipo_mensagem = 'danger';
        }
    } elseif ($acao_form === 'registrar_movimentacao') {
        $materia_prima_id = intval($_POST['materia_prima_id'] ?? 0);
        $tipo = limpar_entrada($_POST['tipo'] ?? '');
        $quantidade = floatval($_POST['quantidade'] ?? 0);
        $descricao = limpar_entrada($_POST['descricao'] ?? '');

        if (registrar_movimentacao($materia_prima_id, $tipo, $quantidade, $descricao)) {
            $mensagem = 'Movimentação registrada com sucesso!';
            $tipo_mensagem = 'success';
        } else {
            $mensagem = 'Erro ao registrar movimentação';
            $tipo_mensagem = 'danger';
        }
    }
}

$estoque = obter_estoque();
$alertas = obter_alertas_estoque_baixo();
$materia_selecionada = null;

if ($materia_id) {
    $materia_selecionada = obter_materia_prima_por_id($materia_id);
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-warehouse"></i> Gerenciamento de Estoque
        </h1>
        <div class="page-actions">
            <?php if ($acao !== 'novo'): ?>
            <a href="?acao=novo" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nova Matéria-Prima
            </a>
            <?php else: ?>
            <a href="?acao=listar" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($mensagem): ?>
    <div class="alert alert-<?php echo $tipo_mensagem; ?>">
        <i class="fas fa-<?php echo $tipo_mensagem === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
        <span><?php echo $mensagem; ?></span>
    </div>
    <?php endif; ?>

    <!-- Alertas de Estoque Baixo -->
    <?php if (count($alertas) > 0): ?>
    <div class="alert alert-warning mb-3">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong><?php echo count($alertas); ?> itens com estoque baixo:</strong>
            <?php foreach ($alertas as $alerta): ?>
            <div style="margin-top: 0.5rem;">
                - <strong><?php echo htmlspecialchars($alerta['nome']); ?></strong>: 
                <?php echo $alerta['quantidade_estoque']; ?> 
                <?php echo htmlspecialchars($alerta['unidade']); ?> 
                (mínimo: <?php echo $alerta['quantidade_minima']; ?>)
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($acao === 'novo'): ?>
    
    <!-- Formulário Matéria-Prima -->
    <div class="card">
        <h2 style="color: #FF6B9D; margin-bottom: 1.5rem;">
            <i class="fas fa-plus"></i> Adicionar Nova Matéria-Prima
        </h2>

        <form method="POST">
            <input type="hidden" name="acao_form" value="adicionar_materia">

            <div class="form-row">
                <div class="form-group">
                    <label for="nome">Nome da Matéria-Prima *</label>
                    <input type="text" id="nome" name="nome" required placeholder="Ex: Tecido Algodão 100%">
                </div>

                <div class="form-group">
                    <label for="tipo">Tipo *</label>
                    <select id="tipo" name="tipo" required>
                        <option value="">Selecionar tipo</option>
                        <option value="Tecido">Tecido</option>
                        <option value="Renda">Renda</option>
                        <option value="Enchimento">Enchimento</option>
                        <option value="Linha">Linha</option>
                        <option value="Outro">Outro</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="unidade">Unidade de Medida *</label>
                    <select id="unidade" name="unidade" required>
                        <option value="">Selecionar unidade</option>
                        <option value="metro">Metro (m)</option>
                        <option value="kg">Quilograma (kg)</option>
                        <option value="rolo">Rolo</option>
                        <option value="unidade">Unidade</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="preco">Preço Unitário (R$) *</label>
                    <input type="number" id="preco" name="preco" step="0.01" required placeholder="0.00">
                </div>
            </div>

            <div class="form-group">
                <label for="quantidade_minima">Quantidade Mínima (Alerta) *</label>
                <input type="number" id="quantidade_minima" name="quantidade_minima" step="0.01" value="5" required>
            </div>

            <div class="form-row" style="margin-top: 2rem;">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-save"></i> Adicionar Matéria-Prima
                </button>
                <a href="?acao=listar" class="btn btn-secondary btn-block">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>

    <?php else: ?>
    
    <!-- Tabela de Estoque -->
    <div class="card">
        <h2 style="color: #FF6B9D; margin-bottom: 1.5rem;">
            <i class="fas fa-list"></i> Itens de Estoque
        </h2>

        <?php if (count($estoque) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Quantidade</th>
                        <th>Mínimo</th>
                        <th>Preço Unit.</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($estoque as $item): ?>
                    <tr>
                        <td style="font-weight: bold;">
                            <?php echo htmlspecialchars($item['nome']); ?>
                        </td>
                        <td>
                            <span class="badge badge-info">
                                <?php echo htmlspecialchars($item['tipo']); ?>
                            </span>
                        </td>
                        <td>
                            <strong><?php echo $item['quantidade_estoque']; ?></strong> 
                            <span class="text-muted"><?php echo htmlspecialchars($item['unidade']); ?></span>
                        </td>
                        <td><?php echo $item['quantidade_minima']; ?></td>
                        <td><?php echo formatar_moeda($item['preco_unitario']); ?></td>
                        <td>
                            <?php
                            if ($item['quantidade_estoque'] <= $item['quantidade_minima']) {
                                echo '<span class="badge badge-danger">Baixo</span>';
                            } elseif ($item['quantidade_estoque'] <= ($item['quantidade_minima'] * 1.5)) {
                                echo '<span class="badge badge-warning">Atenção</span>';
                            } else {
                                echo '<span class="badge badge-success">Ok</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <a href="?id=<?php echo $item['id']; ?>" class="btn btn-small btn-primary" style="margin-right: 0.5rem;">
                                <i class="fas fa-arrow-up"></i> Entrada
                            </a>
                            <a href="?acao=deletar&id=<?php echo $item['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Tem certeza que deseja deletar esta matéria-prima?')">
                                <i class="fas fa-trash"></i> Deletar
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="text-center text-muted mt-3">Nenhuma matéria-prima cadastrada</p>
        <?php endif; ?>
    </div>

    <?php endif; ?>

    <!-- Registrar Movimentação (Lado Direito) -->
    <?php if ($materia_selecionada): ?>
    <div class="card mt-4">
        <h2 style="color: #FF6B9D; margin-bottom: 1.5rem;">
            <i class="fas fa-exchange-alt"></i> Registrar Movimentação
        </h2>

        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong><?php echo htmlspecialchars($materia_selecionada['nome']); ?></strong> - 
            Estoque atual: <strong><?php echo $materia_selecionada['quantidade_estoque']; ?> <?php echo htmlspecialchars($materia_selecionada['unidade']); ?></strong>
        </div>

        <form method="POST">
            <input type="hidden" name="acao_form" value="registrar_movimentacao">
            <input type="hidden" name="materia_prima_id" value="<?php echo $materia_selecionada['id']; ?>">

            <div class="form-group">
                <label for="tipo">Tipo de Movimentação *</label>
                <select id="tipo" name="tipo" required>
                    <option value="">Selecionar</option>
                    <option value="entrada">Entrada (Compra)</option>
                    <option value="saida">Saída (Uso em Produção)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="quantidade">Quantidade *</label>
                <input type="number" id="quantidade" name="quantidade" step="0.01" required placeholder="0">
            </div>

            <div class="form-group">
                <label for="descricao">Descrição / Observação</label>
                <textarea id="descricao" name="descricao" placeholder="Ex: Chegada do pedido #123 ou Produção do ninho da Maria"></textarea>
            </div>

            <div class="form-row" style="margin-top: 2rem;">
                <button type="submit" class="btn btn-success btn-block">
                    <i class="fas fa-check"></i> Registrar Movimentação
                </button>
                <a href="?acao=listar" class="btn btn-secondary btn-block">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
