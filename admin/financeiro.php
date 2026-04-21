<?php
require_once __DIR__ . '/../includes/functions.php';

verificar_autenticacao();

$titulo_pagina = 'Relatório Financeiro';

// Data de filtro
$data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
$data_fim = $_GET['data_fim'] ?? date('Y-m-d');

// Obter relatório
$pedidos_relatorio = obter_relatorio_financeiro($data_inicio, $data_fim);

// Calcular totais
$total_vendas = 0;
$total_custo = 0;
$total_lucro = 0;

foreach ($pedidos_relatorio as $pedido) {
    $total_vendas += $pedido['valor_total'];
    $total_custo += $pedido['custo_material'];
    $total_lucro += $pedido['lucro'];
}

// Percentual de lucro
$percentual_lucro = ($total_vendas > 0) ? ($total_lucro / $total_vendas) * 100 : 0;
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-chart-pie"></i> Relatório Financeiro
        </h1>
    </div>

    <!-- Filtro de Datas -->
    <div class="card mb-3">
        <form method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
            <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
                <label for="data_inicio" style="margin-bottom: 0.3rem;">Data Inicial</label>
                <input type="date" id="data_inicio" name="data_inicio" value="<?php echo $data_inicio; ?>">
            </div>

            <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
                <label for="data_fim" style="margin-bottom: 0.3rem;">Data Final</label>
                <input type="date" id="data_fim" name="data_fim" value="<?php echo $data_fim; ?>">
            </div>

            <button type="submit" class="btn btn-primary" style="margin-bottom: 0;">
                <i class="fas fa-search"></i> Filtrar
            </button>

            <a href="?data_inicio=<?php echo date('Y-m-01'); ?>&data_fim=<?php echo date('Y-m-d'); ?>" class="btn btn-secondary" style="margin-bottom: 0;">
                <i class="fas fa-redo"></i> Este Mês
            </a>

            <a href="?data_inicio=<?php echo date('Y-01-01'); ?>&data_fim=<?php echo date('Y-m-d'); ?>" class="btn btn-secondary" style="margin-bottom: 0;">
                <i class="fas fa-redo"></i> Este Ano
            </a>
        </form>
    </div>

    <!-- Resumo Financeiro -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="color: var(--cor-primaria);">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-value" style="color: var(--cor-primaria);">
                <?php echo formatar_moeda($total_vendas); ?>
            </div>
            <div class="stat-label">Total de Vendas</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="color: #FF9800;">
                <i class="fas fa-factory"></i>
            </div>
            <div class="stat-value" style="color: #FF9800;">
                <?php echo formatar_moeda($total_custo); ?>
            </div>
            <div class="stat-label">Custo de Produção</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="color: #4CAF50;">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-value" style="color: #4CAF50;">
                <?php echo formatar_moeda($total_lucro); ?>
            </div>
            <div class="stat-label">Total de Lucro</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="color: #2196F3;">
                <i class="fas fa-percent"></i>
            </div>
            <div class="stat-value" style="color: #2196F3;">
                <?php echo number_format($percentual_lucro, 1); ?>%
            </div>
            <div class="stat-label">Margem de Lucro</div>
        </div>
    </div>

    <!-- Detalhes -->
    <div class="card mt-3">
        <h2 style="color: #FF6B9D; margin-bottom: 1.5rem;">
            <i class="fas fa-list"></i> Detalhes dos Pedidos
        </h2>

        <?php if (count($pedidos_relatorio) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Data</th>
                        <th>Valor Venda</th>
                        <th>Custo Material</th>
                        <th>Lucro</th>
                        <th>Margem</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos_relatorio as $pedido): ?>
                    <tr>
                        <td>
                            <a href="<?php echo ADMIN_URL; ?>pedidos.php?id=<?php echo $pedido['id']; ?>" style="color: var(--cor-primaria); font-weight: bold; text-decoration: none;">
                                #<?php echo $pedido['id']; ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($pedido['cliente_nome']); ?></td>
                        <td><?php echo formatar_data($pedido['data_pedido']); ?></td>
                        <td><?php echo formatar_moeda($pedido['valor_total']); ?></td>
                        <td><?php echo formatar_moeda($pedido['custo_material']); ?></td>
                        <td>
                            <strong style="color: #4CAF50;">
                                <?php echo formatar_moeda($pedido['lucro']); ?>
                            </strong>
                        </td>
                        <td>
                            <?php
                            $margem = ($pedido['valor_total'] > 0) ? ($pedido['lucro'] / $pedido['valor_total']) * 100 : 0;
                            echo number_format($margem, 1) . '%';
                            ?>
                        </td>
                        <td>
                            <?php
                            $status_colors = [
                                'pendente' => 'warning',
                                'em_producao' => 'info',
                                'pronto' => 'info',
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
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="text-center text-muted" style="padding: 2rem;">
            Nenhum dado para o período selecionado
        </p>
        <?php endif; ?>
    </div>

    <!-- Informações Adicionais -->
    <div class="grid grid-2 mt-3">
        <div class="card">
            <h3 style="color: #FF6B9D; margin-bottom: 1rem;">
                <i class="fas fa-info-circle"></i> Análise
            </h3>

            <?php if (count($pedidos_relatorio) > 0): ?>
            <div style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span>Número de Pedidos:</span>
                    <strong><?php echo count($pedidos_relatorio); ?></strong>
                </div>

                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span>Ticket Médio:</span>
                    <strong><?php echo formatar_moeda($total_vendas / count($pedidos_relatorio)); ?></strong>
                </div>

                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span>Lucro Médio por Pedido:</span>
                    <strong style="color: #4CAF50;">
                        <?php echo formatar_moeda($total_lucro / count($pedidos_relatorio)); ?>
                    </strong>
                </div>

                <div style="display: flex; justify-content: space-between;">
                    <span>Período:</span>
                    <strong><?php echo formatar_data($data_inicio); ?> a <?php echo formatar_data($data_fim); ?></strong>
                </div>
            </div>
            <?php else: ?>
            <p class="text-muted">Selecione um período com dados para análise</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3 style="color: #FF6B9D; margin-bottom: 1rem;">
                <i class="fas fa-lightbulb"></i> Dicas
            </h3>

            <ul style="list-style: none; padding: 0;">
                <li style="margin-bottom: 0.75rem;">
                    <i class="fas fa-check" style="color: #4CAF50; margin-right: 0.5rem;"></i>
                    Monitore regularmente sua margem de lucro
                </li>
                <li style="margin-bottom: 0.75rem;">
                    <i class="fas fa-check" style="color: #4CAF50; margin-right: 0.5rem;"></i>
                    Procure reduzir custos de matéria-prima
                </li>
                <li style="margin-bottom: 0.75rem;">
                    <i class="fas fa-check" style="color: #4CAF50; margin-right: 0.5rem;"></i>
                    Acompanhe os pedidos entregues versus em atraso
                </li>
                <li>
                    <i class="fas fa-check" style="color: #4CAF50; margin-right: 0.5rem;"></i>
                    Analise qual tipo de produto gera mais lucro
                </li>
            </ul>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
