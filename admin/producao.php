<?php
require_once __DIR__ . '/../includes/functions.php';

verificar_autenticacao();

$titulo_pagina = 'Agenda de Produção';

// Obter data de filtro
$mes = $_GET['mes'] ?? date('m');
$ano = $_GET['ano'] ?? date('Y');
$data_inicio = "$ano-$mes-01";
$data_fim = date('Y-m-t', strtotime($data_inicio));

// Obter tarefas do mês
$agenda = obter_agenda_producao($data_inicio, $data_fim);

// Agrupar por data
$agenda_por_data = [];
foreach ($agenda as $tarefa) {
    $data = $tarefa['data_prevista'];
    if (!isset($agenda_por_data[$data])) {
        $agenda_por_data[$data] = [];
    }
    $agenda_por_data[$data][] = $tarefa;
}

ksort($agenda_por_data);
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-calendar-alt"></i> Agenda de Produção
        </h1>
        <div class="page-actions">
            <a href="<?php echo ADMIN_URL; ?>pedidos.php" class="btn btn-secondary">
                <i class="fas fa-list"></i> Pedidos
            </a>
        </div>
    </div>

    <!-- Seletor de Mês -->
    <div class="card mb-3">
        <div style="display: flex; gap: 1rem; align-items: center;">
            <label for="mes_select">Mês:</label>
            <select id="mes_select" onchange="window.location='?mes=' + this.value + '&ano=' + document.getElementById('ano_select').value">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?php echo str_pad($m, 2, '0', STR_PAD_LEFT); ?>" <?php if ($m == $mes) echo 'selected'; ?>>
                    <?php echo strftime('%B', mktime(0, 0, 0, $m, 1)); ?>
                </option>
                <?php endfor; ?>
            </select>

            <label for="ano_select" style="margin-left: 1rem;">Ano:</label>
            <select id="ano_select" onchange="window.location='?mes=' + document.getElementById('mes_select').value + '&ano=' + this.value">
                <?php for ($y = (int)date('Y') - 1; $y <= (int)date('Y') + 1; $y++): ?>
                <option value="<?php echo $y; ?>" <?php if ($y == $ano) echo 'selected'; ?>>
                    <?php echo $y; ?>
                </option>
                <?php endfor; ?>
            </select>
        </div>
    </div>

    <!-- Cronograma -->
    <div class="card">
        <h2 style="color: #FF6B9D; margin-bottom: 1.5rem;">
            Tarefas Programadas
        </h2>

        <?php if (count($agenda_por_data) > 0): ?>
            <?php foreach ($agenda_por_data as $data => $tarefas): ?>
            <div class="card" style="margin-bottom: 1.5rem; border-left: 4px solid var(--cor-primaria);">
                <h3 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                    <i class="fas fa-calendar"></i>
                    <?php
                    $data_obj = new DateTime($data);
                    $data_formatada = $data_obj->format('d/m/Y');
                    $dias_faltando = (strtotime($data) - time()) / 86400;
                    echo $data_formatada;
                    if ($dias_faltando < 0) {
                        echo ' <span class="badge badge-danger">Atrasado ' . abs(floor($dias_faltando)) . ' dias</span>';
                    } elseif ($dias_faltando == 0) {
                        echo ' <span class="badge badge-warning">HOJE</span>';
                    } elseif ($dias_faltando < 3) {
                        echo ' <span class="badge badge-warning">Urgente</span>';
                    } else {
                        echo ' <span class="badge badge-info">' . floor($dias_faltando) . ' dias</span>';
                    }
                    ?>
                </h3>

                <table style="width: 100%;">
                    <thead>
                        <tr style="background-color: #f5f5f5; border-bottom: 2px solid var(--cor-borda);">
                            <th style="padding: 0.75rem; text-align: left;">Pedido</th>
                            <th style="padding: 0.75rem; text-align: left;">Cliente</th>
                            <th style="padding: 0.75rem; text-align: left;">Tarefa</th>
                            <th style="padding: 0.75rem; text-align: left;">Status</th>
                            <th style="padding: 0.75rem; text-align: left;">Responsável</th>
                            <th style="padding: 0.75rem; text-align: left;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tarefas as $tarefa): ?>
                        <tr style="border-bottom: 1px solid var(--cor-borda); padding: 0.75rem;">
                            <td style="padding: 0.75rem;">
                                <a href="<?php echo ADMIN_URL; ?>pedidos.php?id=<?php echo $tarefa['pedido_id']; ?>" style="color: var(--cor-primaria); font-weight: bold; text-decoration: none;">
                                    #<?php echo $tarefa['pedido_id']; ?>
                                </a>
                            </td>
                            <td style="padding: 0.75rem;">
                                <?php echo htmlspecialchars($tarefa['cliente_nome']); ?>
                            </td>
                            <td style="padding: 0.75rem;">
                                <strong><?php echo ucfirst(str_replace('_', ' ', $tarefa['tipo_tarefa'])); ?></strong>
                                <br>
                                <small class="text-muted"><?php echo htmlspecialchars($tarefa['notas']); ?></small>
                            </td>
                            <td style="padding: 0.75rem;">
                                <?php
                                $status_colors = [
                                    'pendente' => 'warning',
                                    'em_andamento' => 'info',
                                    'concluida' => 'success'
                                ];
                                $status_labels = [
                                    'pendente' => 'Pendente',
                                    'em_andamento' => 'Em Andamento',
                                    'concluida' => 'Concluída'
                                ];
                                $color = $status_colors[$tarefa['status']] ?? 'info';
                                ?>
                                <span class="badge badge-<?php echo $color; ?>">
                                    <?php echo $status_labels[$tarefa['status']] ?? $tarefa['status']; ?>
                                </span>
                            </td>
                            <td style="padding: 0.75rem;">
                                <?php echo htmlspecialchars($tarefa['responsavel'] ?? '-'); ?>
                            </td>
                            <td style="padding: 0.75rem;">
                                <?php if ($tarefa['status'] !== 'concluida'): ?>
                                <a href="<?php echo ADMIN_URL; ?>pedidos.php?id=<?php echo $tarefa['pedido_id']; ?>" class="btn btn-small btn-primary">
                                    <i class="fas fa-edit"></i> Atualizar
                                </a>
                                <?php else: ?>
                                <span class="badge badge-success">Concluída</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
        <p class="text-center text-muted" style="padding: 2rem;">
            <i class="fas fa-check-circle" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
            <br>
            Nenhuma tarefa programada para este período
        </p>
        <?php endif; ?>
    </div>

    <!-- Resumo -->
    <div class="stats-grid mt-4">
        <?php
        $pendentes = 0;
        $em_andamento = 0;
        $concluidas = 0;
        
        foreach ($agenda as $tarefa) {
            if ($tarefa['status'] === 'pendente') $pendentes++;
            elseif ($tarefa['status'] === 'em_andamento') $em_andamento++;
            elseif ($tarefa['status'] === 'concluida') $concluidas++;
        }
        ?>
        <div class="stat-card">
            <div class="stat-icon" style="color: #FFC107;">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="stat-value"><?php echo $pendentes; ?></div>
            <div class="stat-label">Pendentes</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="color: #2196F3;">
                <i class="fas fa-spinner"></i>
            </div>
            <div class="stat-value"><?php echo $em_andamento; ?></div>
            <div class="stat-label">Em Andamento</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="color: #4CAF50;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-value"><?php echo $concluidas; ?></div>
            <div class="stat-label">Concluídas</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="color: #FF6B9D;">
                <i class="fas fa-tasks"></i>
            </div>
            <div class="stat-value"><?php echo count($agenda); ?></div>
            <div class="stat-label">Total</div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
