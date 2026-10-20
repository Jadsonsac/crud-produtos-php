-- Banco de dados do projeto CRUD de Produtos/Estoque
CREATE DATABASE IF NOT EXISTS crud_produtos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE crud_produtos;

CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    categoria VARCHAR(80) NOT NULL,
    preco DECIMAL(10,2) NOT NULL DEFAULT 0,
    quantidade INT NOT NULL DEFAULT 0,
    estoque_minimo INT NOT NULL DEFAULT 5,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Dados utilizados como exemplo
INSERT INTO produtos (nome, categoria, preco, quantidade, estoque_minimo) VALUES
('Teclado mecânico', 'Periféricos', 249.90, 12, 5),
('Mouse sem fio', 'Periféricos', 89.90, 3, 5),
('Monitor 24"', 'Informática', 899.00, 7, 2),
('Cabo HDMI 2m', 'Acessórios', 29.90, 20, 10);
