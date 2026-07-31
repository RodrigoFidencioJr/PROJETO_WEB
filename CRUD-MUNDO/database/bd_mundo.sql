DROP DATABASE IF EXISTS bd_mundo;
CREATE DATABASE bd_mundo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bd_mundo;

CREATE TABLE continentes (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome          VARCHAR(100) NOT NULL UNIQUE,
    populacao     BIGINT UNSIGNED NOT NULL DEFAULT 0,
    area_km2      DECIMAL(15,2) NOT NULL DEFAULT 0,
    total_paises  INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE governantes (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome                VARCHAR(150) NOT NULL,
    partido_politico    VARCHAR(100),
    data_nascimento     DATE NOT NULL,
    data_inicio_mandato DATE NOT NULL,
    data_fim_mandato    DATE NULL,
    CHECK (data_fim_mandato IS NULL OR data_fim_mandato >= data_inicio_mandato)
) ENGINE=InnoDB;

CREATE TABLE paises (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome             VARCHAR(150) NOT NULL UNIQUE,
    continente_id    INT UNSIGNED NOT NULL,
    populacao        BIGINT UNSIGNED NOT NULL DEFAULT 0,
    area_km2         DECIMAL(15,2) NOT NULL DEFAULT 0,
    idioma           VARCHAR(100),
    governante_id    INT UNSIGNED NULL,
    clima            VARCHAR(100),
    regime_politico  VARCHAR(100),
    moeda            VARCHAR(50),
    FOREIGN KEY (continente_id) REFERENCES continentes(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (governante_id) REFERENCES governantes(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_pais_continente ON paises(continente_id);
CREATE INDEX idx_pais_governante ON paises(governante_id);

CREATE TABLE cidades (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome           VARCHAR(150) NOT NULL,
    pais_id        INT UNSIGNED NOT NULL,
    populacao      BIGINT UNSIGNED NOT NULL DEFAULT 0,
    area_km2       DECIMAL(15,2) NOT NULL DEFAULT 0,
    clima          VARCHAR(100),
    governante_id  INT UNSIGNED NULL,
    data_fundacao  DATE,
    UNIQUE (nome, pais_id),
    FOREIGN KEY (pais_id) REFERENCES paises(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (governante_id) REFERENCES governantes(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_cidade_pais ON cidades(pais_id);
CREATE INDEX idx_cidade_governante ON cidades(governante_id);

DELIMITER $$

CREATE TRIGGER trg_pais_insert AFTER INSERT ON paises
FOR EACH ROW
BEGIN
    UPDATE continentes SET total_paises = total_paises + 1 WHERE id = NEW.continente_id;
END$$

CREATE TRIGGER trg_pais_delete AFTER DELETE ON paises
FOR EACH ROW
BEGIN
    UPDATE continentes SET total_paises = total_paises - 1 WHERE id = OLD.continente_id;
END$$

CREATE TRIGGER trg_pais_update AFTER UPDATE ON paises
FOR EACH ROW
BEGIN
    IF NEW.continente_id <> OLD.continente_id THEN
        UPDATE continentes SET total_paises = total_paises - 1 WHERE id = OLD.continente_id;
        UPDATE continentes SET total_paises = total_paises + 1 WHERE id = NEW.continente_id;
    END IF;
END$$

DELIMITER ;

INSERT INTO continentes (nome, populacao, area_km2) VALUES
('América do Sul', 434000000, 17840000.00),
('Europa', 746000000, 10180000.00),
('Ásia', 4700000000, 44579000.00);

INSERT INTO governantes (nome, partido_politico, data_nascimento, data_inicio_mandato, data_fim_mandato) VALUES
('Governante Exemplo A', 'Partido A', '1970-05-12', '2023-01-01', NULL),
('Governante Exemplo B', 'Partido B', '1965-11-03', '2022-01-01', NULL),
('Prefeito Exemplo C', 'Partido C', '1980-02-20', '2021-01-01', '2024-12-31');

INSERT INTO paises (nome, continente_id, populacao, area_km2, idioma, governante_id, clima, regime_politico, moeda) VALUES
('Brasil', 1, 214300000, 8515767.00, 'Português', 1, 'Tropical', 'República Presidencialista', 'Real'),
('França', 2, 67750000, 551695.00, 'Francês', 2, 'Temperado', 'República Semipresidencialista', 'Euro'),
('Japão', 3, 125700000, 377975.00, 'Japonês', NULL, 'Temperado', 'Monarquia Parlamentarista', 'Iene');

INSERT INTO cidades (nome, pais_id, populacao, area_km2, clima, governante_id, data_fundacao) VALUES
('São Paulo', 1, 12300000, 1521.11, 'Tropical de Altitude', 3, '1554-01-25'),
('Paris', 2, 2148000, 105.40, 'Temperado Oceânico', NULL, '0508-01-01'),
('Tóquio', 3, 13960000, 2194.07, 'Subtropical Úmido', NULL, '1457-01-01');