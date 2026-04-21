-- ==========================================
-- DADOS DE EXEMPLO (SEED)
-- ==========================================

USE costura_atelier;

-- Inserir usuário admin
INSERT INTO usuarios (nome, email, senha, ativo) VALUES 
('Administrador', 'admin@costura.com', '$2y$10$92IXUNpkm37Oy7WRRF39He3DlH.d41Ris6sFW9DfV0I5F2H5FKHVS', 1);
-- Senha: admin123 (hash bcrypt)

-- Inserir clientes de exemplo
INSERT INTO clientes (nome, email, telefone) VALUES 
('Maria Silva', 'maria@email.com', '(11) 98765-4321'),
('João Santos', 'joao@email.com', '(11) 97654-3210'),
('Ana Costa', 'ana@email.com', '(11) 96543-2109'),
('Pedro Oliveira', 'pedro@email.com', '(11) 95432-1098');

-- Inserir matérias-primas de exemplo
INSERT INTO materias_primas (nome, tipo, unidade, preco_unitario, quantidade_estoque, quantidade_minima) VALUES 
('Tecido Algodão 100%', 'Tecido', 'metro', 25.00, 50, 10),
('Tecido Tricoline Estampado', 'Tecido', 'metro', 20.00, 45, 10),
('Renda Branca Fina', 'Renda', 'metro', 8.00, 100, 20),
('Renda Rosa Delicada', 'Renda', 'metro', 10.00, 80, 15),
('Enchimento Poliéster 100g', 'Enchimento', 'kg', 15.00, 5, 2),
('Linha Branca 40', 'Linha', 'rolo', 5.00, 30, 5),
('Linha Rosa 40', 'Linha', 'rolo', 5.00, 25, 5),
('Velcro Adesivo', 'Outro', 'metro', 12.00, 20, 5);

-- Inserir produtos de exemplo
INSERT INTO produtos (nome, descricao, categoria_id, preco_venda, custo_producao, material, foto_url, ativo) VALUES 
('Ninho Redutor Clássico', 'Ninho redutor artesanal com acabamento em renda branca. Perfeito para recém-nascidos, oferecendo conforto e segurança. Medidas: 70cm x 40cm.', 1, 150.00, 60.00, 'Algodão 100%, Renda branca, Enchimento poliéster', NULL, 1),
('Ninho Redutor Rosa Delicado', 'Ninho redutor com design feminino em tons de rosa. Tecido tricoline estampado com renda rosa delicada. Ideal para grandes presentes.', 1, 180.00, 75.00, 'Tricoline estampado, Renda rosa, Enchimento poliéster', NULL, 1),
('Kit Berço Completo', 'Kit completo para berço incluindo: protetor de berço, jogo de cama (2 jogo de lençol), manta de segurança e almofada. Padrão único ou personalizado.', 3, 450.00, 180.00, 'Algodão 100%, Renda, Enchimento poliéster, Tricoline', NULL, 1),
('Almofada de Amamentação', 'Almofada anatômica para amamentação, preenchida com poliéster de alta qualidade. Com capa removível e lavável. Tamanho: 60cm x 40cm.', 2, 120.00, 45.00, 'Algodão 100%, Enchimento poliéster sintetizado', NULL, 1),
('Almofada para Berço Pequena', 'Almofada decorativa para berço com acabamento em renda. Enchimento macio e seguro para bebês. 30cm x 30cm', 2, 80.00, 35.00, 'Algodão, Renda branca, Enchimento', NULL, 1),
('Manta para Berço Segura', 'Manta artesanal com desenhos bordados. Tecido macio e hipoalergênico. Tamanho 80cm x 100cm. Perfeita para proteger o bebê.', 4, 200.00, 85.00, 'Algodão 100%, Enchimento fino', NULL, 1);

-- Inserir produto_materiais (Receita - quais materiais cada produto usa)
INSERT INTO produto_materiais (produto_id, materia_prima_id, quantidade_necessaria) VALUES 
-- Ninho Redutor Clássico (id 1)
(1, 1, 2.0),      -- 2 metros de Algodão
(1, 3, 1.5),      -- 1.5 metros de Renda Branca
(1, 5, 0.5),      -- 0.5 kg de Enchimento

-- Ninho Redutor Rosa Delicado (id 2)
(2, 2, 2.0),      -- 2 metros de Tricoline
(2, 4, 1.5),      -- 1.5 metros de Renda Rosa
(2, 5, 0.6),      -- 0.6 kg de Enchimento

-- Kit Berço Completo (id 3)
(3, 1, 3.0),      -- 3 metros de Algodão
(3, 2, 2.0),      -- 2 metros de Tricoline
(3, 3, 2.0),      -- 2 metros de Renda Branca
(3, 5, 1.0),      -- 1 kg de Enchimento

-- Almofada de Amamentação (id 4)
(4, 1, 1.5),      -- 1.5 metros de Algodão
(4, 5, 0.8),      -- 0.8 kg de Enchimento

-- Almofada para Berço Pequena (id 5)
(5, 1, 0.75),     -- 0.75 metros de Algodão
(5, 3, 0.5),      -- 0.5 metros de Renda
(5, 5, 0.2),      -- 0.2 kg de Enchimento

-- Manta para Berço (id 6)
(6, 1, 2.0),      -- 2 metros de Algodão
(6, 5, 0.4);      -- 0.4 kg de Enchimento

-- Inserir pedidos de exemplo
INSERT INTO pedidos (cliente_id, data_pedido, data_entrega_prevista, status, descricao_customizacao, valor_total) VALUES 
(1, NOW() - INTERVAL 5 DAY, NOW() + INTERVAL 3 DAY, 'em_producao', 'Ninho rosa claro, com padrão de borboletas', 180.00),
(2, NOW() - INTERVAL 2 DAY, NOW() + INTERVAL 7 DAY, 'pendente', 'Kit berço completo, tons de azul', 450.00),
(3, NOW() - INTERVAL 10 DAY, NOW() - INTERVAL 2 DAY, 'entregue', 'Almofada de amamentação, com padrão geométrico', 120.00),
(4, NOW() - INTERVAL 1 DAY, NOW() + INTERVAL 14 DAY, 'pendente', 'Manta com aplicação de nome bordado', 200.00);

-- Inserir itens do pedido
INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, preco_unitario) VALUES 
(1, 2, 1, 180.00),        -- Pedido 1: Ninho Rosa
(2, 3, 1, 450.00),        -- Pedido 2: Kit Berço
(3, 4, 1, 120.00),        -- Pedido 3: Almofada Amamentação
(4, 6, 1, 200.00);        -- Pedido 4: Manta

-- Inserir tarefas de produção
INSERT INTO agenda_producao (pedido_id, tipo_tarefa, data_prevista, status, responsavel, notas) VALUES 
(1, 'corte', NOW() - INTERVAL 3 DAY, 'concluida', 'Maria', 'Tecido cortado conforme padrão'),
(1, 'costura', NOW() - INTERVAL 1 DAY, 'em_andamento', 'João', 'Costurando as costuras laterais'),
(1, 'acabamento', NOW() + INTERVAL 2 DAY, 'pendente', 'Ana', 'Acabamento com renda e bordado'),
(2, 'corte', NOW() + INTERVAL 3 DAY, 'pendente', 'Maria', 'Corte dos tecidos do kit'),
(2, 'costura', NOW() + INTERVAL 5 DAY, 'pendente', 'João', 'Costura geral'),
(3, 'corte', NOW() - INTERVAL 8 DAY, 'concluida', 'Maria', NULL),
(3, 'costura', NOW() - INTERVAL 6 DAY, 'concluida', 'João', NULL),
(3, 'acabamento', NOW() - INTERVAL 4 DAY, 'concluida', 'Ana', NULL),
(3, 'embalagem', NOW() - INTERVAL 2 DAY, 'concluida', 'Pedro', 'Embalado e entregue'),
(4, 'corte', NOW() + INTERVAL 5 DAY, 'pendente', NULL, NULL);

-- Inserir movimentações de estoque (exemplos)
INSERT INTO movimentacao_estoque (materia_prima_id, tipo, quantidade, descricao) VALUES 
(1, 'entrada', 50, 'Compra do fornecedor A - 25/03/2026'),
(2, 'entrada', 45, 'Compra do fornecedor B - 22/03/2026'),
(1, 'saida', 2, 'Uso no Pedido #1'),
(2, 'saida', 2, 'Uso no Pedido #2'),
(1, 'saida', 1.5, 'Uso no Pedido #4'),
(3, 'entrada', 100, 'Entrada de renda - fornecedor C'),
(5, 'entrada', 5, 'Compra de enchimento - 20/03/2026'),
(5, 'saida', 1.8, 'Distribuição para produção');
