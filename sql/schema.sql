-- ==========================================
-- SISTEMA DE GESTÃO ATELIÊ ENXOVAL BEBÊ
-- ==========================================

-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS costura_atelier;
USE costura_atelier;

-- ==========================================
-- Tabela: Usuários (Administradores)
-- ==========================================
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    cpf VARCHAR(11) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT TRUE
);

-- ==========================================
-- Tabela: Categoria de Produtos
-- ==========================================
CREATE TABLE categorias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inserir categorias padrão
INSERT INTO categorias (nome, descricao) VALUES
('Ninhos Redutores', 'Ninhos redutores para berços'),
('Almofadas', 'Almofadas e apoiadores'),
('Kits Berço', 'Kits completos para berços'),
('Manta', 'Mantas e cobertores'),
('Acessórios', 'Acessórios diversos');

-- ==========================================
-- Tabela: Produtos
-- ==========================================
CREATE TABLE produtos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT,
    categoria_id INT NOT NULL,
    preco_venda DECIMAL(10, 2) NOT NULL,
    custo_producao DECIMAL(10, 2) DEFAULT 0,
    material VARCHAR(255),
    foto_url VARCHAR(255),
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

-- ==========================================
-- Tabela: Matéria-prima / Insumos
-- ==========================================
CREATE TABLE materias_primas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL UNIQUE,
    tipo ENUM('Tecido', 'Renda', 'Enchimento', 'Linha', 'Outro') NOT NULL,
    unidade ENUM('metro', 'kg', 'rolo', 'unidade') NOT NULL,
    preco_unitario DECIMAL(10, 2) NOT NULL,
    quantidade_estoque DECIMAL(10, 2) DEFAULT 0,
    quantidade_minima DECIMAL(10, 2) DEFAULT 5,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ==========================================
-- Tabela: Movimentação de Estoque
-- ==========================================
CREATE TABLE movimentacao_estoque (
    id INT PRIMARY KEY AUTO_INCREMENT,
    materia_prima_id INT NOT NULL,
    tipo ENUM('entrada', 'saida') NOT NULL,
    quantidade DECIMAL(10, 2) NOT NULL,
    data_movimentacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    descricao TEXT,
    FOREIGN KEY (materia_prima_id) REFERENCES materias_primas(id)
);

-- ==========================================
-- Tabela: Produto - Insumo (Receita)
-- ==========================================
CREATE TABLE produto_materiais (
    id INT PRIMARY KEY AUTO_INCREMENT,
    produto_id INT NOT NULL,
    materia_prima_id INT NOT NULL,
    quantidade_necessaria DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    FOREIGN KEY (materia_prima_id) REFERENCES materias_primas(id),
    UNIQUE KEY unique_produto_material (produto_id, materia_prima_id)
);

-- ==========================================
-- Tabela: Clientes
-- ==========================================
CREATE TABLE clientes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    telefone VARCHAR(20),
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- Tabela: Pedidos
-- ==========================================
CREATE TABLE pedidos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cliente_id INT NOT NULL,
    data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_entrega_prevista DATE NOT NULL,
    data_entrega_real DATE NULL,
    status ENUM('pendente', 'em_producao', 'pronto', 'entregue', 'cancelado') DEFAULT 'pendente',
    descricao_customizacao TEXT,
    valor_total DECIMAL(10, 2) NOT NULL,
    observacoes TEXT,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id)
);

-- ==========================================
-- Tabela: Itens do Pedido
-- ==========================================
CREATE TABLE pedido_itens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    preco_unitario DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) GENERATED ALWAYS AS (quantidade * preco_unitario) STORED,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
);

-- ==========================================
-- Tabela: Agenda / Produção
-- ==========================================
CREATE TABLE agenda_producao (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pedido_id INT NOT NULL,
    tipo_tarefa ENUM('corte', 'costura', 'acabamento', 'embalagem') NOT NULL,
    data_prevista DATE NOT NULL,
    data_conclusao DATE NULL,
    status ENUM('pendente', 'em_andamento', 'concluida') DEFAULT 'pendente',
    responsavel VARCHAR(100),
    notas TEXT,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE
);

-- ==========================================
-- Tabela: Relatório Financeiro
-- ==========================================
CREATE TABLE relatorio_financeiro (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pedido_id INT,
    tipo ENUM('venda', 'custo_material', 'despesa') NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    data_transacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id)
);

-- ==========================================
-- Índices para Melhor Performance
-- ==========================================
CREATE INDEX idx_produto_categoria ON produtos(categoria_id);
CREATE INDEX idx_pedido_cliente ON pedidos(cliente_id);
CREATE INDEX idx_pedido_data ON pedidos(data_pedido);
CREATE INDEX idx_agenda_data ON agenda_producao(data_prevista);
CREATE INDEX idx_materia_prima_tipo ON materias_primas(tipo);
CREATE INDEX idx_movimentacao_data ON movimentacao_estoque(data_movimentacao);

-- ==========================================
-- View: Custo Total dos Pedidos
-- ==========================================
CREATE VIEW view_custo_pedidos AS
SELECT 
    p.id,
    p.cliente_id,
    c.nome AS cliente_nome,
    p.data_pedido,
    p.data_entrega_prevista,
    p.status,
    p.valor_total,
    COALESCE(pm.custo_material_total, 0) AS custo_material,
    (p.valor_total - COALESCE(pm.custo_material_total, 0)) AS lucro
FROM pedidos p
LEFT JOIN clientes c ON p.cliente_id = c.id
LEFT JOIN (
    SELECT pi.pedido_id, SUM(pm.preco_unitario * COALESCE(produto_materiais.quantidade_necessaria, 1)) AS custo_material_total
    FROM pedido_itens pi
    JOIN produtos prod ON pi.produto_id = prod.id
    LEFT JOIN produto_materiais ON prod.id = produto_materiais.produto_id
    LEFT JOIN materias_primas pm ON produto_materiais.materia_prima_id = pm.id
    GROUP BY pi.pedido_id
) pm ON p.id = pm.pedido_id;
