<?php
require_once __DIR__ . '/../includes/functions.php';

verificar_autenticacao();

$titulo_pagina = 'Produtos';
$acao = $_GET['acao'] ?? 'listar';
$id = $_GET['id'] ?? null;
$mensagem = '';
$tipo_mensagem = '';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = limpar_entrada($_POST['nome'] ?? '');
    $descricao = limpar_entrada($_POST['descricao'] ?? '');
    $categoria_id = intval($_POST['categoria_id'] ?? 0);
    $preco = floatval($_POST['preco'] ?? 0);
    $custo = floatval($_POST['custo'] ?? 0);
    $material = limpar_entrada($_POST['material'] ?? '');
    $foto_url = '';

    // Upload de imagem
    if (isset($_FILES['foto']) && $_FILES['foto']['size'] > 0) {
        $resultado_upload = upload_imagem_produto($_FILES['foto']);
        if ($resultado_upload['sucesso']) {
            $foto_url = $resultado_upload['arquivo'];
        }
    } else if ($acao === 'editar' && $id) {
        $produto_atual = obter_produto_por_id($id);
        $foto_url = $produto_atual['foto_url'] ?? '';
    }

    if ($acao === 'novo') {
        if (adicionar_produto($nome, $descricao, $categoria_id, $preco, $custo, $material, $foto_url)) {
            $mensagem = 'Produto adicionado com sucesso!';
            $tipo_mensagem = 'success';
            $acao = 'listar';
        } else {
            $mensagem = 'Erro ao adicionar produto';
            $tipo_mensagem = 'danger';
        }
    } elseif ($acao === 'editar' && $id) {
        if (atualizar_produto($id, $nome, $descricao, $categoria_id, $preco, $custo, $material)) {
            $mensagem = 'Produto atualizado com sucesso!';
            $tipo_mensagem = 'success';
            $acao = 'listar';
        } else {
            $mensagem = 'Erro ao atualizar produto';
            $tipo_mensagem = 'danger';
        }
    }
}

// Deletar produto
if ($acao === 'deletar' && $id) {
    if (deletar_produto($id)) {
        $mensagem = 'Produto deletado com sucesso!';
        $tipo_mensagem = 'success';
    }
    $acao = 'listar';
}

$categorias = obter_categorias();
$filtro_categoria = $_GET['categoria'] ?? '';
$produtos = obter_produtos($filtro_categoria);
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-box"></i> Produtos
        </h1>
        <div class="page-actions">
            <?php if ($acao === 'listar'): ?>
            <a href="?acao=novo" class="btn btn-primary">
                <i class="fas fa-plus"></i> Novo Produto
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

    <?php if ($acao === 'listar'): ?>
    
    <!-- Filtros -->
    <div class="card mb-3">
        <div class="filter-bar">
            <input type="text" id="filtro_busca" placeholder="Buscar produto..." style="flex: 1;">
            <select id="filtro_categoria" onchange="filtrar_produtos_admin()">
                <option value="">Todas as categorias</option>
                <?php foreach ($categorias as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" <?php if ($filtro_categoria == $cat['id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($cat['nome']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Grid de Produtos -->
    <div class="product-grid">
        <?php if (count($produtos) > 0): ?>
            <?php foreach ($produtos as $produto): ?>
            <div class="product-card" data-categoria="<?php echo $produto['categoria_id']; ?>">
                <?php if ($produto['foto_url']): ?>
                    <img src="<?php echo ASSETS_URL; ?>uploads/produtos/<?php echo $produto['foto_url']; ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>" class="product-image">
                <?php else: ?>
                    <div class="product-image" style="display: flex; align-items: center; justify-content: center; background-color: #f0f0f0;">
                        <i class="fas fa-image" style="font-size: 3rem; color: #ccc;"></i>
                    </div>
                <?php endif; ?>
                
                <div class="product-info">
                    <div class="product-category"><?php echo htmlspecialchars($produto['categoria_nome']); ?></div>
                    <h3 class="product-name"><?php echo htmlspecialchars($produto['nome']); ?></h3>
                    <p class="product-description"><?php echo mb_strimwidth(htmlspecialchars($produto['descricao']), 0, 100, '...'); ?></p>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                        <div>
                            <small style="color: #999;">Preço</small>
                            <div class="product-price"><?php echo formatar_moeda($produto['preco_venda']); ?></div>
                        </div>
                        <div>
                            <small style="color: #999;">Custo</small>
                            <div class="product-price" style="font-size: 1rem; color: #FF9800;">
                                <?php echo formatar_moeda($produto['custo_producao']); ?>
                            </div>
                        </div>
                        <div>
                            <small style="color: #999;">Lucro</small>
                            <div class="product-price" style="font-size: 1rem; color: #4CAF50;">
                                <?php $lucro = $produto['preco_venda'] - $produto['custo_producao']; echo formatar_moeda($lucro); ?>
                            </div>
                        </div>
                    </div>

                    <div class="product-actions">
                        <a href="?acao=editar&id=<?php echo $produto['id']; ?>" class="btn btn-secondary" style="flex: 1; justify-content: center;">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="?acao=deletar&id=<?php echo $produto['id']; ?>" class="btn btn-danger" style="flex: 1; justify-content: center;" onclick="return confirm('Tem certeza?')">
                            <i class="fas fa-trash"></i> Deletar
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
        <p class="text-center text-muted" style="grid-column: 1 / -1; padding: 2rem;">
            Nenhum produto encontrado
        </p>
        <?php endif; ?>
    </div>

    <?php elseif ($acao === 'novo' || $acao === 'editar'): ?>
    
    <!-- Formulário -->
    <div class="card">
        <h2 style="color: #FF6B9D; margin-bottom: 1.5rem;">
            <i class="fas fa-<?php echo $acao === 'novo' ? 'plus' : 'edit'; ?>"></i> 
            <?php echo $acao === 'novo' ? 'Novo Produto' : 'Editar Produto'; ?>
        </h2>

        <?php $produto = ($acao === 'editar' && $id) ? obter_produto_por_id($id) : null; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="nome">Nome do Produto *</label>
                    <input type="text" id="nome" name="nome" required value="<?php echo $produto ? htmlspecialchars($produto['nome']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="categoria_id">Categoria *</label>
                    <select id="categoria_id" name="categoria_id" required>
                        <option value="">Selecionar categoria</option>
                        <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php if ($produto && $produto['categoria_id'] == $cat['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($cat['nome']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="descricao">Descrição</label>
                <textarea id="descricao" name="descricao"><?php echo $produto ? htmlspecialchars($produto['descricao']) : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label for="material">Materiais Utilizados</label>
                <textarea id="material" name="material" style="min-height: 80px;"><?php echo $produto ? htmlspecialchars($produto['material']) : ''; ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="preco">Preço de Venda (R$) *</label>
                    <input type="number" id="preco" name="preco" step="0.01" required value="<?php echo $produto ? $produto['preco_venda'] : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="custo">Custo de Produção (R$) *</label>
                    <input type="number" id="custo" name="custo" step="0.01" required value="<?php echo $produto ? $produto['custo_producao'] : ''; ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="foto">Foto do Produto</label>
                <input type="file" id="foto" name="foto" accept="image/*" onchange="preview_imagem('foto')">
                <small style="color: #999; display: block; margin-top: 0.5rem;">Máximo 5MB. Formatos: JPG, PNG, GIF, WebP</small>
                
                <?php if ($produto && $produto['foto_url']): ?>
                <div style="margin-top: 1rem;">
                    <img src="<?php echo ASSETS_URL; ?>uploads/produtos/<?php echo $produto['foto_url']; ?>" alt="Foto atual" style="max-width: 200px; border-radius: 6px;">
                </div>
                <?php endif; ?>
                
                <div class="image-preview" style="margin-top: 1rem;"></div>
            </div>

            <div class="form-row" style="margin-top: 2rem;">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-save"></i> <?php echo $acao === 'novo' ? 'Adicionar' : 'Atualizar'; ?>  Produto
                </button>
                <a href="?acao=listar" class="btn btn-secondary btn-block">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>

    <?php endif; ?>
</div>

<script>
function filtrar_produtos_admin() {
    const busca = document.getElementById('filtro_busca').value.toLowerCase();
    const categoria = document.getElementById('filtro_categoria').value;
    const url = new URL(window.location);
    
    if (busca) {
        url.searchParams.set('busca', busca);
    }
    if (categoria) {
        url.searchParams.set('categoria', categoria);
    }
    
    // Recarregar com filtro (simples implementação)
    const cards = document.querySelectorAll('.product-card');
    cards.forEach(card => {
        const nome = card.querySelector('.product-name')?.textContent.toLowerCase() || '';
        const cat = card.dataset.categoria || '';
        
        const nome_match = nome.includes(busca);
        const cat_match = !categoria || cat === categoria;
        
        card.style.display = (nome_match && cat_match) ? '' : 'none';
    });
}

// Listener para busca
document.getElementById('filtro_busca')?.addEventListener('keyup', filtrar_produtos_admin);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
