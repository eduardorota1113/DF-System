
CREATE DATABASE IF NOT EXISTS df_system
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE df_system;

CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefone VARCHAR(20) DEFAULT NULL,

    PRIMARY KEY (id_usuario),
    UNIQUE KEY uq_usuario_email (email)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS diagnostico (
    id_diagnostico INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    descricao TEXT NOT NULL,
    categoria VARCHAR(24) DEFAULT NULL,
    perigo VARCHAR(12) DEFAULT NULL,
    contexto_importado TEXT DEFAULT NULL,
    origem_importacao CHAR(64) DEFAULT NULL,
    data_diagnostico DATETIME NOT NULL
        DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id_diagnostico),
    UNIQUE KEY uq_diagnostico_origem (origem_importacao),

    INDEX idx_diagnostico_usuario (id_usuario),

    CONSTRAINT fk_diagnostico_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuario (id_usuario)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
