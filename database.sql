-- ============================================
-- AUTOESCOLA FINANCEIRO — Base de Dados v2.0
-- ============================================

CREATE DATABASE IF NOT EXISTS autoescola_financeiro
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE autoescola_financeiro;

-- Tabela: escolas
CREATE TABLE IF NOT EXISTS escolas (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nome       VARCHAR(150) NOT NULL,
    nif        VARCHAR(20)  NOT NULL UNIQUE,
    email      VARCHAR(150) NOT NULL UNIQUE,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela: utilizadores
CREATE TABLE IF NOT EXISTS utilizadores (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(150) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('superadmin','admin_escola','funcionario') NOT NULL DEFAULT 'funcionario',
    escola_id   INT NULL,
    ativo       TINYINT(1)   NOT NULL DEFAULT 1,
    permissoes  JSON         NULL COMMENT 'Permissões personalizadas para funcionários',
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (escola_id) REFERENCES escolas(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela: alunos
CREATE TABLE IF NOT EXISTS alunos (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    escola_id   INT            NOT NULL,
    nome        VARCHAR(150)   NOT NULL,
    pacote      VARCHAR(100)   NOT NULL,
    preco_total DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    created_at  TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (escola_id) REFERENCES escolas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela: receitas
CREATE TABLE IF NOT EXISTS receitas (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    escola_id     INT           NOT NULL,
    aluno_id      INT           NULL,
    tipo          ENUM('inscricao','aulas','exame','prestacao','outro') NOT NULL,
    valor         DECIMAL(10,2) NOT NULL,
    data          DATE          NOT NULL,
    metodo        ENUM('numerario','transferencia','mbway','multibanco') NOT NULL DEFAULT 'numerario',
    descricao     TEXT          NULL,
    eliminado     TINYINT(1)    NOT NULL DEFAULT 0,
    eliminado_em  TIMESTAMP     NULL,
    created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (escola_id) REFERENCES escolas(id) ON DELETE CASCADE,
    FOREIGN KEY (aluno_id)  REFERENCES alunos(id)  ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela: despesas
CREATE TABLE IF NOT EXISTS despesas (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    escola_id     INT           NOT NULL,
    categoria     ENUM('combustivel','manutencao','salarios','renda','seguros','impostos','outros') NOT NULL,
    valor         DECIMAL(10,2) NOT NULL,
    data          DATE          NOT NULL,
    descricao     TEXT          NOT NULL,
    eliminado     TINYINT(1)    NOT NULL DEFAULT 0,
    eliminado_em  TIMESTAMP     NULL,
    created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (escola_id) REFERENCES escolas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- ÍNDICES
-- ============================================
CREATE INDEX IF NOT EXISTS idx_alunos_escola        ON alunos(escola_id);
CREATE INDEX IF NOT EXISTS idx_receitas_escola_data  ON receitas(escola_id, data);
CREATE INDEX IF NOT EXISTS idx_receitas_eliminado    ON receitas(eliminado);
CREATE INDEX IF NOT EXISTS idx_despesas_escola_data  ON despesas(escola_id, data);
CREATE INDEX IF NOT EXISTS idx_despesas_eliminado    ON despesas(eliminado);
CREATE INDEX IF NOT EXISTS idx_utilizadores_escola   ON utilizadores(escola_id);

-- ============================================
-- SUPERADMIN padrão
-- email:    superadmin@autoescola.pt
-- password: password
-- ⚠️  Altere após o primeiro login
-- ============================================
INSERT INTO utilizadores (nome, email, password, role, escola_id, ativo)
VALUES (
    'Super Administrador',
    'superadmin@autoescola.pt',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'superadmin',
    NULL,
    1
) ON DUPLICATE KEY UPDATE id=id;

-- ============================================
-- DADOS DE DEMONSTRAÇÃO (descomente para testar)
-- ============================================
/*
INSERT INTO escolas (nome, nif, email) VALUES
    ('AutoEscola Central', '500000001', 'central@autoescola.pt');

INSERT INTO utilizadores (nome, email, password, role, escola_id, ativo) VALUES
    ('Ana Ferreira',  'ana@central.pt',   '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin_escola', 1, 1),
    ('Carlos Santos', 'carlos@central.pt','$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'funcionario',  1, 1);

INSERT INTO alunos (escola_id, nome, pacote, preco_total) VALUES
    (1, 'João Silva',    'Categoria B',     900.00),
    (1, 'Maria Costa',   'Categoria B',     900.00),
    (1, 'Pedro Nunes',   'Categoria A',     700.00),
    (1, 'Sofia Lopes',   'Categoria B + A', 1400.00),
    (1, 'Rui Martins',   'Categoria B',     900.00);

INSERT INTO receitas (escola_id, aluno_id, tipo, valor, data, metodo) VALUES
    (1, 1, 'inscricao',  80.00,  CURDATE(), 'mbway'),
    (1, 1, 'aulas',     300.00,  CURDATE(), 'transferencia'),
    (1, 2, 'exame',     120.00,  CURDATE(), 'numerario'),
    (1, 3, 'prestacao', 200.00,  CURDATE(), 'multibanco'),
    (1, 4, 'aulas',     400.00,  CURDATE(), 'mbway');

INSERT INTO despesas (escola_id, categoria, valor, data, descricao) VALUES
    (1, 'combustivel', 180.00,  CURDATE(), 'Combustível viaturas'),
    (1, 'salarios',   1200.00,  CURDATE(), 'Salário instrutor'),
    (1, 'renda',       450.00,  CURDATE(), 'Renda instalações'),
    (1, 'seguros',      95.00,  CURDATE(), 'Seguro viatura');
*/
