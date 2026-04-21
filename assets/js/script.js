/**
 * Script principal do Sistema Costura Ateliê
 */

// ==========================================
// UTILIDADES
// ==========================================

function mostrar_mensagem(tipo, mensagem) {
    const alert_div = document.createElement('div');
    alert_div.className = `alert alert-${tipo}`;
    alert_div.innerHTML = `
        <i class="fas fa-${tipo === 'success' ? 'check-circle' : tipo === 'danger' ? 'times-circle' : 'info-circle'}"></i>
        <span>${mensagem}</span>
    `;
    
    const container = document.querySelector('.main-content') || document.body;
    container.insertBefore(alert_div, container.firstChild);
    
    // Remover após 5 segundos
    setTimeout(() => {
        alert_div.style.transition = 'opacity 0.3s ease';
        alert_div.style.opacity = '0';
        setTimeout(() => alert_div.remove(), 300);
    }, 5000);
}

function formatar_moeda(valor) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(valor);
}

function formatar_data(data) {
    const opcoes = { day: '2-digit', month: '2-digit', year: 'numeric' };
    return new Date(data).toLocaleDateString('pt-BR', opcoes);
}

// ==========================================
// MODAIS
// ==========================================

function abrir_modal(modal_id) {
    const modal = document.getElementById(modal_id);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function fechar_modal(modal_id) {
    const modal = document.getElementById(modal_id);
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = 'auto';
    }
}

// Fechar modal ao clicar fora dele
document.addEventListener('click', function(evento) {
    if (evento.target.classList.contains('modal')) {
        const modal_id = evento.target.id;
        fechar_modal(modal_id);
    }
});

// Fechar modal com botão de fechar
document.addEventListener('click', function(evento) {
    if (evento.target.classList.contains('modal-close')) {
        const modal = evento.target.closest('.modal');
        if (modal) {
            fechar_modal(modal.id);
        }
    }
});

// ==========================================
// FORMULÁRIOS
// ==========================================

function validar_formulario(formulario_id) {
    const form = document.getElementById(formulario_id);
    if (!form) return false;

    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let valido = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = '#F44336';
            valido = false;
        } else {
            input.style.borderColor = '';
        }
    });

    return valido;
}

function limpar_formulario(formulario_id) {
    const form = document.getElementById(formulario_id);
    if (form) {
        form.reset();
        form.querySelectorAll('input, select, textarea').forEach(elem => {
            elem.style.borderColor = '';
        });
    }
}

// ==========================================
// BUSCA E FILTROS
// ==========================================

function filtrar_produtos() {
    const busca = document.getElementById('filtro_busca')?.value.toLowerCase() || '';
    const categoria = document.getElementById('filtro_categoria')?.value || '';
    const cards = document.querySelectorAll('.product-card');

    cards.forEach(card => {
        const nome = card.querySelector('.product-name')?.textContent.toLowerCase() || '';
        const desc = card.querySelector('.product-description')?.textContent.toLowerCase() || '';
        const cat = card.dataset.categoria || '';

        const nome_match = nome.includes(busca) || desc.includes(busca);
        const cat_match = !categoria || cat === categoria;

        card.style.display = (nome_match && cat_match) ? '' : 'none';
    });
}

// Eventos de filtro
document.getElementById('filtro_busca')?.addEventListener('keyup', filtrar_produtos);
document.getElementById('filtro_categoria')?.addEventListener('change', filtrar_produtos);

// ==========================================
// UPLOAD DE IMAGENS
// ==========================================

function preview_imagem(input_id) {
    const input = document.getElementById(input_id);
    const preview_container = document.querySelector('.image-preview');

    if (!input.files[0]) return;

    const file = input.files[0];
    const reader = new FileReader();

    reader.onload = function(e) {
        if (preview_container) {
            preview_container.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 300px; border-radius: 8px;">`;
        }
    };

    reader.readAsDataURL(file);
}

// ==========================================
// TRANSAÇÕES COM O SERVIDOR
// ==========================================

async function fazer_requisicao(url, metodo = 'GET', dados = null) {
    try {
        const opcoes = {
            method: metodo,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        if (dados && metodo !== 'GET') {
            if (dados instanceof FormData) {
                opcoes.body = dados;
            } else {
                opcoes.headers['Content-Type'] = 'application/json';
                opcoes.body = JSON.stringify(dados);
            }
        }

        const resposta = await fetch(url, opcoes);
        const resultado = await resposta.json();

        return resultado;
    } catch (erro) {
        console.error('Erro na requisição:', erro);
        mostrar_mensagem('danger', 'Erro ao comunicar com servidor');
        return null;
    }
}

// ==========================================
// AÇÕES DE PRODUTOS
// ==========================================

function deletar_produto(produto_id) {
    if (confirm('Tem certeza que deseja deletar este produto?')) {
        fazer_requisicao(`admin/api/produtos.php?acao=deletar&id=${produto_id}`, 'POST')
            .then(resultado => {
                if (resultado.sucesso) {
                    mostrar_mensagem('success', 'Produto deletado com sucesso');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    mostrar_mensagem('danger', resultado.mensagem || 'Erro ao deletar');
                }
            });
    }
}

function editar_produto(produto_id) {
    // Buscar dados do produto e abrir modal de edição
    fazer_requisicao(`admin/api/produtos.php?acao=obter&id=${produto_id}`)
        .then(resultado => {
            if (resultado.sucesso) {
                const produto = resultado.dados;
                // Preencher formulário com dados
                document.getElementById('edit_nome').value = produto.nome;
                document.getElementById('edit_descricao').value = produto.descricao;
                document.getElementById('edit_categoria').value = produto.categoria_id;
                document.getElementById('edit_preco').value = produto.preco_venda;
                document.getElementById('edit_custo').value = produto.custo_producao;
                document.getElementById('edit_material').value = produto.material;
                
                document.getElementById('edit_produto_id').value = produto_id;
                abrir_modal('modalEditarProduto');
            }
        });
}

// ==========================================
// AÇÕES DE ESTOQUE
// ==========================================

function registrar_movimentacao() {
    const materia_id = document.getElementById('materia_prima_id').value;
    const tipo = document.getElementById('tipo_movimentacao').value;
    const quantidade = document.getElementById('quantidade').value;
    const descricao = document.getElementById('descricao').value;

    if (!materia_id || !tipo || !quantidade) {
        mostrar_mensagem('warning', 'Preencha todos os campos obrigatórios');
        return;
    }

    fazer_requisicao('admin/api/estoque.php?acao=registrar', 'POST', {
        materia_prima_id: materia_id,
        tipo: tipo,
        quantidade: quantidade,
        descricao: descricao
    }).then(resultado => {
        if (resultado.sucesso) {
            mostrar_mensagem('success', 'Movimentação registrada');
            limpar_formulario('formMovimentacao');
            setTimeout(() => location.reload(), 1500);
        } else {
            mostrar_mensagem('danger', resultado.mensagem);
        }
    });
}

// ==========================================
// AÇÕES DE PEDIDOS
// ==========================================

function atualizar_status_pedido(pedido_id, novo_status) {
    fazer_requisicao(`admin/api/pedidos.php?acao=atualizar_status`, 'POST', {
        pedido_id: pedido_id,
        status: novo_status
    }).then(resultado => {
        if (resultado.sucesso) {
            mostrar_mensagem('success', 'Status atualizado');
            setTimeout(() => location.reload(), 1000);
        } else {
            mostrar_mensagem('danger', resultado.mensagem);
        }
    });
}

// ==========================================
// GRÁFICOS E VISUALIZAÇÕES
// ==========================================

function criar_grafico_pizza(canvas_id, labels, dados, cores = null) {
    const canvas = document.getElementById(canvas_id);
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    
    // Cores padrão se não fornecidas
    if (!cores) {
        cores = [
            '#FF6B9D',
            '#FEC8D8',
            '#FFDDC1',
            '#FFB6C1',
            '#FF85B3'
        ];
    }

    // Aqui você poderia usar Chart.js se desejado
    // Por enquanto, apenas um aviso de que seria implementado com uma biblioteca
    console.log('Gráfico de pizza:', { labels, dados });
}

// ==========================================
// INICIALIZAÇÃO
// ==========================================

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips, popovers, etc.
    console.log('Sistema Costura Ateliê carregado');

    // Validar apenas formulários que têm campos required
    document.querySelectorAll('form[id]').forEach(form => {
        // Verificar se o formulário tem campos required
        const temCamposObrigatorios = form.querySelectorAll('input[required], select[required], textarea[required]').length > 0;
        
        if (temCamposObrigatorios) {
            form.addEventListener('submit', function(e) {
                if (!validar_formulario(form.id)) {
                    e.preventDefault();
                    mostrar_mensagem('warning', 'Preencha todos os campos obrigatórios');
                }
            });
        }
    });
});
