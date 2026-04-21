-- ==========================================
-- ATUALIZAÇÕES PARA SISTEMA DE CLIENTE
-- ==========================================

USE costura_atelier;
-- Atualizar tabela clientes para incluir senha
ALTER TABLE clientes ADD COLUMN senha VARCHAR(255) DEFAULT NULL AFTER telefone;
ALTER TABLE clientes ADD COLUMN ativo BOOLEAN DEFAULT TRUE AFTER senha;

-- Tabela de carrinho do cliente
CREATE TABLE IF NOT EXISTS carrinho (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cliente_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    preco_unitario DECIMAL(10, 2) NOT NULL,
    data_adicao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_carrinho (cliente_id, produto_id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
);

-- Índices para melhor performance
CREATE INDEX idx_carrinho_cliente ON carrinho(cliente_id);
