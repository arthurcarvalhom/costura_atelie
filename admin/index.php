<?php
require_once __DIR__ . '/../includes/functions.php';

verificar_autenticacao();

// Obter estatísticas
$stats = [
    'total_produtos' => $conn->query("SELECT COUNT(*) as total FROM produtos WHERE ativo = 1")->fetch_assoc()['total'],
    'total_pedidos' => $conn->query("SELECT COUNT(*) as total FROM pedidos WHERE status != 'cancelado'")->fetch_assoc()['total'],
    'pedidos_pendentes' => $conn->query("SELECT COUNT(*) as total FROM pedidos WHERE status = 'pendente' OR status = 'em_producao'")->fetch_assoc()['total'],
    'estoque_baixo' => count(obter_alertas_estoque_baixo()),
];

// Obter últimos pedidos
$pedidos_recentes = obter_pedidos();
$pedidos_recentes = array_slice($pedidos_recentes, 0, 5);

$titulo_pagina = 'Dashboard';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-chart-line"></i> Dashboard
        </h1>
    </div>

    <!-- Estatísticas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-value"><?php echo $stats['total_produtos']; ?></div>
            <div class="stat-label">Total de Produtos</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-value"><?php echo $stats['total_pedidos']; ?></div>
            <div class="stat-label">Total de Pedidos</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="stat-value"><?php echo $stats['pedidos_pendentes']; ?></div>
            <div class="stat-label">Em Produção</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-value"><?php echo $stats['estoque_baixo']; ?></div>
            <div class="stat-label">Itens com Estoque Baixo</div>
        </div>
    </div>

    <!-- Alertas de Estoque Baixo -->
    <?php if ($stats['estoque_baixo'] > 0): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <span><strong><?php echo $stats['estoque_baixo']; ?> itens</strong> com estoque abaixo do mínimo. <a href="<?php echo ADMIN_URL; ?>estoque.php">Verificar estoque</a></span>
    </div>
    <?php endif; ?>

    <!-- Pedidos Recentes -->
    <div class="card mt-3">
        <h2 style="color: #FF6B9D; margin-bottom: 1.5rem;">
            <i class="fas fa-list"></i> Pedidos Recentes
        </h2>

        <?php if (count($pedidos_recentes) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Itens</th>
                        <th>Data Entrega</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos_recentes as $pedido): ?>
                    <tr>
                        <td>#<?php echo $pedido['id']; ?></td>
                        <td><?php echo htmlspecialchars($pedido['cliente_nome']); ?></td>
                        <td><?php echo $pedido['quantidade_itens']; ?></td>
                        <td><?php echo formatar_data($pedido['data_entrega_prevista']); ?></td>
                        <td><?php echo formatar_moeda($pedido['valor_total']); ?></td>
                        <td>
                            <?php
                            $status_colors = [
                                'pendente' => 'warning',
                                'em_producao' => 'info',
                                'pronto' => 'success',
                                'entregue' => 'success',
                                'cancelado' => 'danger'
                            ];
                            $status_label = [
                                'pendente' => 'Pendente',
                                'em_producao' => 'Em Produção',
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
                            <a href="<?php echo ADMIN_URL; ?>pedidos.php?id=<?php echo $pedido['id']; ?>" class="btn btn-small btn-secondary">
                                Ver
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="text-center text-muted mt-3">Nenhum pedido encontrado</p>
        <?php endif; ?>
    </div>

    <!-- Atalhos Rápidos -->
    <div class="card mt-4">
        <h2 style="color: #FF6B9D; margin-bottom: 1.5rem;">
            <i class="fas fa-bolt"></i> Atalhos
        </h2>
        <div class="grid grid-4">
            <a href="<?php echo ADMIN_URL; ?>produtos.php?acao=novo" class="btn btn-primary btn-block">
                <i class="fas fa-plus"></i> Novo Produto
            </a>
            <a href="<?php echo ADMIN_URL; ?>pedidos.php?acao=novo" class="btn btn-primary btn-block">
                <i class="fas fa-plus"></i> Novo Pedido
            </a>
            <a href="<?php echo ADMIN_URL; ?>estoque.php?acao=novo" class="btn btn-primary btn-block">
                <i class="fas fa-plus"></i> Adicionar Estoque
            </a>
            <a href="<?php echo ADMIN_URL; ?>producao.php" class="btn btn-secondary btn-block">
                <i class="fas fa-calendar"></i> Visualizar Agenda
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
