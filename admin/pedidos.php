<?php
require_once __DIR__ . '/../includes/functions.php';

verificar_autenticacao();

$titulo_pagina = 'Pedidos';
$id_pedido = $_GET['id'] ?? null;
$filtro_status = $_GET['status'] ?? '';
$acao = $_GET['acao'] ?? 'listar';
$mensagem = '';
$tipo_mensagem = '';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao_form = $_POST['acao'] ?? '';

    if ($acao_form === 'criar_pedido') {
        $cliente_nome = limpar_entrada($_POST['cliente_nome'] ?? '');
        $cliente_email = limpar_entrada($_POST['cliente_email'] ?? '');
        $cliente_telefone = limpar_entrada($_POST['cliente_telefone'] ?? '');
        $data_entrega = $_POST['data_entrega'] ?? '';
        $descricao = limpar_entrada($_POST['descricao'] ?? '');

        // Criar ou buscar cliente
        $stmt = $conn->prepare("SELECT id FROM clientes WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $cliente_email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $cliente_id = $resultado->fetch_assoc()['id'];
        } else {
            $stmt = $conn->prepare("INSERT INTO clientes (nome, email, telefone) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $cliente_nome, $cliente_email, $cliente_telefone);
            if ($stmt->execute()) {
                $cliente_id = $stmt->insert_id;
            }
        }

        if (isset($cliente_id)) {
            // Calcular valor total
            $valor_total = 0;
            $produtos_array = isset($_POST['produtos']) ? $_POST['produtos'] : [];
            
            foreach ($produtos_array as $produto_id) {
                $produto = obter_produto_por_id($produto_id);
                $valor_total += $produto['preco_venda'];
            }

            $stmt = $conn->prepare("
                INSERT INTO pedidos (cliente_id, data_entrega_prevista, descricao_customizacao, valor_total)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("issd", $cliente_id, $data_entrega, $descricao, $valor_total);
            
            if ($stmt->execute()) {
                $novo_pedido_id = $stmt->insert_id;
                
                // Adicionar produtos ao pedido
                foreach ($produtos_array as $produto_id) {
                    $produto = obter_produto_por_id($produto_id);
                    adicionar_item_pedido($novo_pedido_id, $produto_id, 1, $produto['preco_venda']);
                }
                
                $mensagem = 'Pedido criado com sucesso!';
                $tipo_mensagem = 'success';
                $acao = 'listar';
            }
        }
    } elseif ($acao_form === 'atualizar_status') {
        $pedido_id = intval($_POST['id_pedido'] ?? $id_pedido ?? 0);
        $novo_status = limpar_entrada($_POST['status'] ?? '');
        
        if ($pedido_id && $novo_status && atualizar_status_pedido($pedido_id, $novo_status)) {
            header('Location: ?id=' . $pedido_id);
            exit();
        } else {
            $mensagem = 'Erro ao atualizar status do pedido';
            $tipo_mensagem = 'danger';
        }
    }
}

// Atualizar status via GET (para links rápidos)
if (isset($_GET['status_update']) && $id_pedido) {
    atualizar_status_pedido($id_pedido, $_GET['status_update']);
    header('Location: ?id=' . $id_pedido);
    exit();
}

$pedidos = obter_pedidos($filtro_status ? $filtro_status : null);
$pedido_detalhe = null;
$itens_pedido = [];

if ($id_pedido) {
    $pedido_detalhe = obter_pedido_por_id($id_pedido);
    $itens_pedido = obter_itens_pedido($id_pedido);
}

$categorias = obter_categorias();
$produtos = obter_produtos();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-shopping-cart"></i> Gerenciamento de Pedidos
        </h1>
        <div class="page-actions">
            <?php if (!$id_pedido && $acao !== 'novo'): ?>
            <a href="?acao=novo" class="btn btn-primary">
                <i class="fas fa-plus"></i> Novo Pedido
            </a>
            <?php elseif ($id_pedido || $acao === 'novo'): ?>
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

    <?php if ($acao === 'novo'): ?>
    
    <!-- Formulário Novo Pedido -->
    <div class="card">
        <h2 style="color: #FF6B9D; margin-bottom: 1.5rem;">
            <i class="fas fa-plus"></i> Criar Novo Pedido
        </h2>

        <form method="POST">
            <input type="hidden" name="acao" value="criar_pedido">

            <h3 style="border-bottom: 2px solid var(--cor-borda); padding-bottom: 1rem; margin-bottom: 1rem;">
                Dados do Cliente
            </h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="cliente_nome">Nome do Cliente *</label>
                    <input type="text" id="cliente_nome" name="cliente_nome" required>
                </div>

                <div class="form-group">
                    <label for="cliente_email">Email *</label>
                    <input type="email" id="cliente_email" name="cliente_email" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="cliente_telefone">Telefone</label>
                    <input type="text" id="cliente_telefone" name="cliente_telefone" placeholder="(11) 99999-9999">
                </div>

                <div class="form-group">
                    <label for="data_entrega">Data de Entrega Prevista *</label>
                    <input type="date" id="data_entrega" name="data_entrega" required min="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>

            <h3 style="border-bottom: 2px solid var(--cor-borda); padding-bottom: 1rem; margin: 2rem 0 1rem 0;">
                Produtos
            </h3>

            <div style="max-height: 400px; overflow-y: auto; border: 1px solid var(--cor-borda); border-radius: 6px; padding: 1rem; margin-bottom: 1rem;">
                <?php if (count($produtos) > 0): ?>
                    <?php foreach ($produtos as $produto): ?>
                    <label style="display: flex; align-items: center; margin-bottom: 1rem; padding: 0.75rem; border-bottom: 1px solid var(--cor-borda); cursor: pointer;">
                        <input type="checkbox" name="produtos[]" value="<?php echo $produto['id']; ?>" style="margin-right: 1rem; cursor: pointer;">
                        <div style="flex: 1;">
                            <strong><?php echo htmlspecialchars($produto['nome']); ?></strong>
                            <br>
                            <small class="text-muted">
                                <?php echo htmlspecialchars($produto['categoria_nome']); ?> - 
                                <?php echo formatar_moeda($produto['preco_venda']); ?>
                            </small>
                        </div>
                    </label>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="descricao">Descrição / Customização</label>
                <textarea id="descricao" name="descricao" placeholder="Ex: Padrão específico, cores, etc."></textarea>
            </div>

            <div class="form-row" style="margin-top: 2rem;">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-check"></i> Criar Pedido
                </button>
                <a href="?acao=listar" class="btn btn-secondary btn-block">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>

    <?php elseif ($id_pedido && $pedido_detalhe): ?>
    
    <!-- Detalhes do Pedido -->
    <div class="grid grid-2">
        <!-- Informações Principais -->
        <div class="card">
            <h2 style="color: #FF6B9D; margin-bottom: 1rem;">
                Pedido #<?php echo $pedido_detalhe['id']; ?>
            </h2>

            <div style="margin-bottom: 1.5rem;">
                <small class="text-muted">Status do Pedido</small>
                <form method="POST" style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                    <input type="hidden" name="acao" value="atualizar_status">
                    <input type="hidden" name="id_pedido" value="<?php echo $id_pedido; ?>">
                    <select name="status" id="nova_status" style="flex: 1;" onchange="this.form.submit()">
                        <option value="pendente" <?php if ($pedido_detalhe['status'] === 'pendente') echo 'selected'; ?>>Pendente</option>
                        <option value="em_producao" <?php if ($pedido_detalhe['status'] === 'em_producao') echo 'selected'; ?>>Em Produção</option>
                        <option value="em_caminho" <?php if ($pedido_detalhe['status'] === 'em_caminho') echo 'selected'; ?>>A Caminho</option>
                        <option value="atrasado" <?php if ($pedido_detalhe['status'] === 'atrasado') echo 'selected'; ?>>Atrasado</option>
                        <option value="pronto" <?php if ($pedido_detalhe['status'] === 'pronto') echo 'selected'; ?>>Pronto</option>
                        <option value="entregue" <?php if ($pedido_detalhe['status'] === 'entregue') echo 'selected'; ?>>Entregue</option>
                        <option value="cancelado" <?php if ($pedido_detalhe['status'] === 'cancelado') echo 'selected'; ?>>Cancelado</option>
                    </select>
                </form>
            </div>

            <div style="padding: 1.5rem; background-color: #FFF9F9; border-radius: 6px;">
                <div style="margin-bottom: 1rem;">
                    <small class="text-muted">Cliente</small>
                    <div style="font-weight: bold; margin-top: 0.5rem;">
                        <?php echo htmlspecialchars($pedido_detalhe['cliente_nome']); ?>
                    </div>
                </div>

                <div style="margin-bottom: 1rem;">
                    <small class="text-muted">Email</small>
                    <div style="margin-top: 0.5rem;">
                        <a href="mailto:<?php echo htmlspecialchars($pedido_detalhe['email']); ?>">
                            <?php echo htmlspecialchars($pedido_detalhe['email']); ?>
                        </a>
                    </div>
                </div>

                <div>
                    <small class="text-muted">Telefone</small>
                    <div style="margin-top: 0.5rem;">
                        <a href="tel:<?php echo htmlspecialchars($pedido_detalhe['telefone']); ?>">
                            <?php echo htmlspecialchars($pedido_detalhe['telefone']); ?>
                        </a>
                    </div>
                </div>
            </div>

            <h3 style="margin-top: 2rem; margin-bottom: 1rem; border-bottom: 2px solid var(--cor-borda); padding-bottom: 0.5rem;">
                Datas
            </h3>

            <div style="margin-bottom: 1rem;">
                <small class="text-muted">Pedido em</small>
                <div style="font-weight: bold; margin-top: 0.5rem;">
                    <?php echo formatar_data_hora($pedido_detalhe['data_pedido']); ?>
                </div>
            </div>

            <div>
                <small class="text-muted">Entrega Prevista</small>
                <div style="font-weight: bold; color: var(--cor-primaria); margin-top: 0.5rem;">
                    <?php echo formatar_data($pedido_detalhe['data_entrega_prevista']); ?>
                </div>
            </div>
        </div>

        <!-- Itens do Pedido -->
        <div class="card">
            <h2 style="color: #FF6B9D; margin-bottom: 1rem;">
                Itens do Pedido
            </h2>

            <?php if (count($itens_pedido) > 0): ?>
            <div class="table-container">
                <table style="margin-bottom: 1.5rem;">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Qtd</th>
                            <th>Preço Unit.</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($itens_pedido as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['produto_nome']); ?></td>
                            <td><?php echo $item['quantidade']; ?></td>
                            <td><?php echo formatar_moeda($item['preco_unitario']); ?></td>
                            <td><strong><?php echo formatar_moeda($item['subtotal']); ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="background-color: #FFF9F9; padding: 1rem; border-radius: 6px; border-left: 4px solid var(--cor-primaria);">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span>Subtotal:</span>
                    <span><?php echo formatar_moeda($pedido_detalhe['valor_total']); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.2rem;">
                    <span>Total:</span>
                    <span style="color: var(--cor-primaria);">
                        <?php echo formatar_moeda($pedido_detalhe['valor_total']); ?>
                    </span>
                </div>
            </div>

            <?php
            $lucro_info = calcular_lucro_pedido($id_pedido);
            ?>
            <div style="margin-top: 1rem; padding: 1rem; background-color: #F1F8E9; border-radius: 6px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span>Custo de Material:</span>
                    <span><?php echo formatar_moeda($lucro_info['custo']); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; font-weight: bold; color: var(--cor-sucesso);">
                    <span>Lucro:</span>
                    <span><?php echo formatar_moeda($lucro_info['lucro']); ?> (<?php echo number_format($lucro_info['percentual_lucro'], 1); ?>%)</span>
                </div>
            </div>
            <?php else: ?>
            <p class="text-center text-muted">Nenhum item neste pedido</p>
            <?php endif; ?>
        </div>
    </div>

    <?php else: ?>
    
    <!-- Lista de Pedidos -->
    <div class="card">
        <h2 style="color: #FF6B9D; margin-bottom: 1.5rem;">
            <i class="fas fa-list"></i> Pedidos
        </h2>

        <!-- Filtros -->
        <div class="filter-bar mb-3">
            <select id="filtro_status" onchange="window.location='?status=' + this.value">
                <option value="">Todos os Status</option>
                <option value="pendente" <?php if ($filtro_status === 'pendente') echo 'selected'; ?>>Pendente</option>
                <option value="em_producao" <?php if ($filtro_status === 'em_producao') echo 'selected'; ?>>Em Produção</option>
                <option value="em_caminho" <?php if ($filtro_status === 'em_caminho') echo 'selected'; ?>>A Caminho</option>
                <option value="atrasado" <?php if ($filtro_status === 'atrasado') echo 'selected'; ?>>Atrasado</option>
                <option value="pronto" <?php if ($filtro_status === 'pronto') echo 'selected'; ?>>Pronto</option>
                <option value="entregue" <?php if ($filtro_status === 'entregue') echo 'selected'; ?>>Entregue</option>
                <option value="cancelado" <?php if ($filtro_status === 'cancelado') echo 'selected'; ?>>Cancelado</option>
            </select>
        </div>

        <?php if (count($pedidos) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Data Pedido</th>
                        <th>Entrega</th>
                        <th>Itens</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $pedido): ?>
                    <tr>
                        <td><strong>#<?php echo $pedido['id']; ?></strong></td>
                        <td><?php echo htmlspecialchars($pedido['cliente_nome']); ?></td>
                        <td><?php echo formatar_data($pedido['data_pedido']); ?></td>
                        <td>
                            <strong><?php echo formatar_data($pedido['data_entrega_prevista']); ?></strong>
                            <?php
                            $dias_restantes = (strtotime($pedido['data_entrega_prevista']) - time()) / 86400;
                            if ($dias_restantes < 0) echo '<br><span class="badge badge-danger">Atrasado</span>';
                            elseif ($dias_restantes < 3) echo '<br><span class="badge badge-warning">Urgente</span>';
                            ?>
                        </td>
                        <td><?php echo $pedido['quantidade_itens']; ?></td>
                        <td><?php echo formatar_moeda($pedido['valor_total']); ?></td>
                        <td>
                            <?php
                            $status_colors = [
                                'pendente' => 'warning',
                                'em_producao' => 'info',
                                'em_caminho' => 'secondary',
                                'atrasado' => 'danger',
                                'pronto' => 'success',
                                'entregue' => 'success',
                                'cancelado' => 'danger'
                            ];
                            $status_label = [
                                'pendente' => 'Pendente',
                                'em_producao' => 'Em Produção',
                                'em_caminho' => 'A Caminho',
                                'atrasado' => 'Atrasado',
                                'pronto' => 'Pronto',
                                'entregue' => 'Entregue',
                                'cancelado' => 'Cancelado'
                            ];
                            $color = $status_colors[$pedido['status']] ?? 'info';
                            ?>
                            <span class="badge badge-<?php echo $color; ?>">
                                <?php echo $status_label[$pedido['status']] ?? $pedido['status']; ?>
                            </span>
                        </td>
                        <td>
                            <a href="?id=<?php echo $pedido['id']; ?>" class="btn btn-small btn-primary">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="text-center text-muted mt-3">
            <?php echo $filtro_status ? 'Nenhum pedido com este status' : 'Nenhum pedido encontrado'; ?>
        </p>
        <?php endif; ?>
    </div>

    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
