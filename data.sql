CREATE DATABASE gestao_financeira;
USE gestao_financeira;



CREATE TABLE transacoes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tipo ENUM('receita','despesa') NOT NULL,
  descricao VARCHAR(255) NOT NULL,
  valor DECIMAL(10,2) NOT NULL,
  data DATE NOT NULL,
  user_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  senha VARCHAR(255) NOT NULL,
  tipo ENUM('admin', 'comum') NOT NULL DEFAULT 'comum',
  estado ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO users (nome, email, senha, tipo, estado)
VALUES ('Administrador', 'admin@teste.com', '$2y$10$Uhg04cuIOto5Ma1TYSJb6OBdVi8pNmWOBh2IpvGAQ7Hjutq8hYNey', 'admin', 'ativo');
