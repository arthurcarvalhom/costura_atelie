<?php
require_once __DIR__ . '/../includes/functions.php';

$titulo_pagina = 'Catálogo';
$categorias = obter_categorias();
$filtro_categoria = $_GET['categoria'] ?? '';
$busca = $_GET['busca'] ?? '';

$produtos = obter_produtos($filtro_categoria);

// Filtrar por busca
if ($busca) {
    $busca_lower = strtolower($busca);
    $produtos = array_filter($produtos, function($p) use ($busca_lower) {
        return stripos($p['nome'], $busca_lower) !== false || 
               stripos($p['descricao'], $busca_lower) !== false;
    });
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Navbar para Cliente -->
<style>
    .client-navbar {
        background: white;
        border-bottom: 1px solid #eee;
        padding: 1rem 0;
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .client-navbar .max-width {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .client-navbar-left {
        display: flex;
        gap: 2rem;
        align-items: center;
        flex: 1;
    }

    .client-navbar-right {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .client-navbar a {
        text-decoration: none;
        color: #333;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .client-navbar a:hover {
        color: var(--cor-primaria);
    }

    .client-navbar .btn {
        margin: 0;
    }
</style>

<div class="client-navbar">
    <div class="max-width">
        <div class="client-navbar-left">
            <a href="<?php echo SITE_URL; ?>" style="font-size: 1.1rem; color: var(--cor-primaria);">
                <i class="fas fa-needle"></i> Costura Ateliê
            </a>
            <a href="<?php echo SITE_URL; ?>public/#catalogo">
                <i class="fas fa-th-large"></i> Catálogo
            </a>
            <a href="<?php echo SITE_URL; ?>public/#sobre">
                <i class="fas fa-info-circle"></i> Sobre
            </a>
            <a href="<?php echo SITE_URL; ?>public/#contato">
                <i class="fas fa-envelope"></i> Contato
            </a>
        </div>
        <div class="client-navbar-right">
            <?php if (isset($_SESSION['cliente_id'])): ?>
                <a href="<?php echo SITE_URL; ?>cliente/carrinho.php" style="position: relative;">
                    <i class="fas fa-shopping-cart"></i> Carrinho
                </a>
                <span style="color: #999;">|</span>
                <span style="color: #666;">
                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['cliente_nome']); ?>
                </span>
                <a href="<?php echo SITE_URL; ?>cliente/sair.php" style="color: #f44336;">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            <?php else: ?>
                <a href="<?php echo SITE_URL; ?>cliente/login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <a href="<?php echo SITE_URL; ?>criar_usuario.php?tipo=cliente" class="btn btn-secondary">
                    <i class="fas fa-user-plus"></i> Cadastro
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Catálogo Público -->
<style>
    .catalog-hero {
        background: linear-gradient(135deg, var(--cor-primaria), #FF85B3);
        color: white;
        padding: 5rem 2rem;
        text-align: center;
        margin-bottom: 3rem;
    }

    .catalog-hero h1 {
        font-size: 3rem;
        margin-bottom: 1rem;
    }

    .catalog-hero p {
        font-size: 1.2rem;
        opacity: 0.95;
    }

    .catalog-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
        margin: 2rem 0 3rem;
    }

    .catalog-action {
        background: white;
        border-radius: 14px;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 10px 22px rgba(0, 0, 0, 0.08);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .catalog-action:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 28px rgba(0, 0, 0, 0.12);
    }

    .catalog-action h3 {
        margin: 0.5rem 0 0.75rem;
        font-size: 1.1rem;
    }

    .catalog-action p {
        font-size: 0.95rem;
        color: #555;
        margin-bottom: 1rem;
    }

    .catalog-action a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.85rem 1rem;
        border-radius: 999px;
        background: var(--cor-primaria);
        color: white;
        text-decoration: none;
        font-weight: 600;
    }
</style>

<div class="catalog-hero">
    <h1>
        <i class="fas fa-baby"></i> Costura Ateliê
    </h1>
    <p>Produtos artesanais e personalizados para o enxoval do seu bebê</p>
</div>



<div class="main-content">
    <!-- Filtros -->
    <div class="card mb-3">
        <form method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <input type="text" name="busca" placeholder="Buscar produto..." value="<?php echo htmlspecialchars($busca); ?>" style="flex: 1; min-width: 200px;">
            
            <select name="categoria">
                <option value="">Todas as categorias</option>
                <?php foreach ($categorias as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" <?php if ($filtro_categoria == $cat['id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($cat['nome']); ?>
                </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Buscar
            </button>

            <?php if ($filtro_categoria || $busca): ?>
            <a href="<?php echo SITE_URL; ?>" class="btn btn-secondary">
                <i class="fas fa-redo"></i> Limpar Filtros
            </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Grid de Produtos -->
    <div class="product-grid" id="catalogo">
        <?php if (count($produtos) > 0): ?>
            <?php foreach ($produtos as $produto): ?>
            <div class="product-card" data-categoria="<?php echo $produto['categoria_id']; ?>" onclick="openProductModal(<?php echo $produto['id']; ?>)">
                <div style="cursor: pointer;">
                    <?php if ($produto['foto_url']): ?>
                        <img src="<?php echo ASSETS_URL; ?>uploads/produtos/<?php echo $produto['foto_url']; ?>" 
                             alt="<?php echo htmlspecialchars($produto['nome']); ?>" 
                             class="product-image">
                    <?php else: ?>
                        <div class="product-image" style="display: flex; align-items: center; justify-content: center; background-color: #f0f0f0;">
                            <i class="fas fa-image" style="font-size: 3rem; color: #ccc;"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="product-info">
                        <div class="product-category"><?php echo htmlspecialchars($produto['categoria_nome']); ?></div>
                        <h3 class="product-name"><?php echo htmlspecialchars($produto['nome']); ?></h3>
                        <p class="product-description"><?php echo mb_strimwidth(htmlspecialchars($produto['descricao']), 0, 100, '...'); ?></p>
                        <div class="product-price"><?php echo formatar_moeda($produto['preco_venda']); ?></div>
                        
                        <div class="product-actions" style="margin-top: 1rem;">
                            <button class="btn btn-primary" onclick="event.stopPropagation(); abrir_modal('modalProduto<?php echo $produto['id']; ?>')">
                                <i class="fas fa-eye"></i> Ver Detalhes
                            </button>
                            <button class="btn btn-secondary" onclick="event.stopPropagation(); adicionar_ao_carrinho(<?php echo $produto['id']; ?>, '<?php echo htmlspecialchars($produto['nome']); ?>')">
                                <i class="fas fa-shopping-cart"></i> Carrinho
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Produto -->
            <div id="modalProduto<?php echo $produto['id']; ?>" class="modal">
                <div class="modal-content" style="max-width: 700px;">
                    <div class="modal-header">
                        <h2 class="modal-title"><?php echo htmlspecialchars($produto['nome']); ?></h2>
                        <button class="modal-close" onclick="fechar_modal('modalProduto<?php echo $produto['id']; ?>')">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="modal-content-body">
                        <?php if ($produto['foto_url']): ?>
                        <img src="<?php echo ASSETS_URL; ?>uploads/produtos/<?php echo $produto['foto_url']; ?>" 
                             alt="<?php echo htmlspecialchars($produto['nome']); ?>" 
                             style="width: 100%; border-radius: 6px; margin-bottom: 1rem; max-height: 400px; object-fit: cover;">
                        <?php endif; ?>

                        <div style="margin-bottom: 1.5rem;">
                            <span class="badge badge-info"><?php echo htmlspecialchars($produto['categoria_nome']); ?></span>
                        </div>

                        <h3 style="margin-bottom: 1rem;">Descrição</h3>
                        <p style="margin-bottom: 1.5rem; line-height: 1.6;">
                            <?php echo htmlspecialchars($produto['descricao']); ?>
                        </p>

                        <h3 style="margin-bottom: 1rem;">Materiais Utilizados</h3>
                        <p style="margin-bottom: 1.5rem; line-height: 1.6;">
                            <?php echo htmlspecialchars($produto['material']); ?>
                        </p>

                        <div style="background-color: #FFF9F9; padding: 1.5rem; border-radius: 6px; border-left: 4px solid var(--cor-primaria);">
                            <div style="font-size: 0.9rem; color: #999; margin-bottom: 0.5rem;">Preço</div>
                            <div style="font-size: 1.8rem; font-weight: bold; color: var(--cor-primaria);">
                                <?php echo formatar_moeda($produto['preco_venda']); ?>
                            </div>
                        </div>

                        <p style="text-align: center; margin-top: 2rem; color: #999; font-size: 0.9rem;">
                            Interessado? Escolha como deseja fazer seu pedido
                        </p>

                        <div style="display: flex; gap: 1rem; margin-top: 1.5rem; margin-bottom: 1.5rem;">
                            <button class="btn btn-secondary btn-block" onclick="mostrar_interface_carrinho(<?php echo $produto['id']; ?>, '<?php echo htmlspecialchars($produto['nome']); ?>', <?php echo $produto['preco_venda']; ?>)">
                                <i class="fas fa-shopping-cart"></i> Adicionar ao Carrinho
                            </button>
                            <a href="https://wa.me/5511999999999?text=Olá! Gostaria de encomendar o produto: <?php echo urlencode($produto['nome']); ?>" 
                               target="_blank" class="btn btn-success btn-block">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
            <i class="fas fa-search" style="font-size: 3rem; color: #ddd; margin-bottom: 1rem;"></i>
            <p class="text-muted" style="font-size: 1.1rem;">
                <?php if ($busca || $filtro_categoria): ?>
                    Nenhum produto encontrado com estes filtros
                <?php else: ?>
                    Nenhum produto disponível no momento
                <?php endif; ?>
            </p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Informações -->
    <div class="card mt-4" id="sobre" style="background: linear-gradient(135deg, var(--cor-primaria), #FF85B3); color: white;">
        <h2 style="color: white; margin-bottom: 1rem;">
            <i class="fas fa-info-circle"></i> Sobre Nós
        </h2>
        <p style="margin-bottom: 1rem;">
            Somos um ateliê especializado em enxoval de bebê, criando produtos artesanais e personalizados com muito cuidado e qualidade.
        </p>
        <p style="margin-bottom: 1rem;">
            Cada peça é feita sob encomenda, permitindo customização de cores, padrões e materiais de acordo com suas preferências.
        </p>
        <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
            <div>
                <strong style="font-size: 1.1rem;">📞 Telefone:</strong><br>
                <a href="tel:+5511999999999" style="color: white; text-decoration: none;">(61) 9 9999-9999</a>
            </div>
            <div>
                <strong style="font-size: 1.1rem;">📧 Email:</strong><br>
                <a href="mailto:contato@costura.com" style="color: white; text-decoration: none;">contato@costura.com</a>
            </div>
            <div>
                <strong style="font-size: 1.1rem;">📍 Local:</strong><br>
                Brasília - DF
            </div>
        </div>
    </div>

    <div class="card mt-4" id="contato" style="background: #ffffff; color: #333;">
        <h2 style="margin-bottom: 1rem; color: var(--cor-primaria);">
            <i class="fas fa-envelope"></i> Contato
        </h2>
        <p style="margin-bottom: 1rem;">
            Envie sua mensagem para fazer encomendas, pedir orçamentos ou tirar dúvidas sobre nossos produtos.
        </p>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="mailto:contato@costura.com?subject=Contato%20via%20Catálogo" class="btn btn-primary" style="padding: 0.9rem 1.2rem;">
                <i class="fas fa-envelope"></i> Enviar Email
            </a>
            <a href="https://wa.me/5511999999999?text=Olá!%20Gostaria%20de%20receber%20informações%20sobre%20os%20produtos." target="_blank" class="btn btn-secondary" style="padding: 0.9rem 1.2rem;">
                <i class="fab fa-whatsapp"></i> WhatsApp
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
function openProductModal(productId) {
    abrir_modal('modalProduto' + productId);
}

function adicionar_ao_carrinho(produto_id, produto_nome) {
    <?php if (isset($_SESSION['cliente_id'])): ?>
        const quantidade = prompt('Quantos itens deseja adicionar?', '1');
        if (quantidade && parseInt(quantidade) > 0) {
            const formData = new FormData();
            formData.append('produto_id', produto_id);
            formData.append('quantidade', quantidade);

            fetch('<?php echo SITE_URL; ?>cliente/api/carrinho.php?acao=adicionar', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.sucesso) {
                    alert('✅ ' + produto_nome + ' adicionado ao carrinho!');
                    window.location.href = '<?php echo SITE_URL; ?>cliente/carrinho.php';
                } else {
                    alert('❌ ' + data.mensagem);
                }
            })
            .catch(error => console.error('Erro:', error));
        }
    <?php else: ?>
        alert('Você precisa estar logado para fazer isso. Será redirecionado para login!');
        window.location.href = '<?php echo SITE_URL; ?>cliente/login.php';
    <?php endif; ?>
}

function mostrar_interface_carrinho(produto_id, produto_nome, preco) {
    <?php if (isset($_SESSION['cliente_id'])): ?>
        const quantidade = prompt('Quantos itens deseja adicionar?', '1');
        if (quantidade && parseInt(quantidade) > 0) {
            const formData = new FormData();
            formData.append('produto_id', produto_id);
            formData.append('quantidade', quantidade);

            fetch('<?php echo SITE_URL; ?>cliente/api/carrinho.php?acao=adicionar', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.sucesso) {
                    fechar_modal('modalProduto' + produto_id);
                    alert('✅ ' + produto_nome + ' adicionado ao carrinho!');
                } else {
                    alert('❌ ' + data.mensagem);
                }
            })
            .catch(error => console.error('Erro:', error));
        }
    <?php else: ?>
        alert('Você precisa estar logado para fazer isso. Será redirecionado para registro de cliente!');
        window.location.href = '<?php echo SITE_URL; ?>criar_usuario.php?tipo=cliente';
    <?php endif; ?>
}
</script>
