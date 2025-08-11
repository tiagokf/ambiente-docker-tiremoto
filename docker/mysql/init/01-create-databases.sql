-- Criar bancos de dados para desenvolvimento
CREATE DATABASE IF NOT EXISTS desenvolvimento CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS testes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS projeto1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS projeto2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Criar usuário de desenvolvimento
CREATE USER IF NOT EXISTS 'dev_user'@'%' IDENTIFIED BY 'dev_pass';

-- Conceder privilégios
GRANT ALL PRIVILEGES ON desenvolvimento.* TO 'dev_user'@'%';
GRANT ALL PRIVILEGES ON testes.* TO 'dev_user'@'%';
GRANT ALL PRIVILEGES ON projeto1.* TO 'dev_user'@'%';
GRANT ALL PRIVILEGES ON projeto2.* TO 'dev_user'@'%';

-- Aplicar mudanças
FLUSH PRIVILEGES;

-- Inserir dados de exemplo no banco desenvolvimento
USE desenvolvimento;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO usuarios (nome, email, senha) VALUES 
('Administrador', 'admin@exemplo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Usuário Teste', 'teste@exemplo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    estoque INT DEFAULT 0,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO produtos (nome, descricao, preco, estoque) VALUES 
('Produto Exemplo 1', 'Descrição do produto exemplo 1', 29.99, 100),
('Produto Exemplo 2', 'Descrição do produto exemplo 2', 49.99, 50);
