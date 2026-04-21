-- ============================================================
-- Migration 002 — Areas table + collaborators field swap
-- Run this against an existing itapp database that already
-- has migrations 000 and 001 applied.
-- Safe to run: checks for existing columns before altering.
-- ============================================================

USE itapp;

-- ── 1. Create areas table ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS areas (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name       VARCHAR(100) NOT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
                                     ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_areas_name (name)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ── 2. Seed default areas ─────────────────────────────────────
INSERT IGNORE INTO areas (name) VALUES
('Tecnología'),
('Recursos Humanos'),
('Contabilidad'),
('Administración'),
('Dirección General'),
('Marketing'),
('Operaciones');

-- ── 3. Collaborators: drop address, add country + area_id ─────
-- Drop address (safe only if it exists)
DROP PROCEDURE IF EXISTS _drop_address_col;
DELIMITER $$
CREATE PROCEDURE _drop_address_col()
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'collaborators'
          AND COLUMN_NAME  = 'address'
    ) THEN
        ALTER TABLE collaborators DROP COLUMN address;
    END IF;
END$$
DELIMITER ;
CALL _drop_address_col();
DROP PROCEDURE IF EXISTS _drop_address_col;

-- Add country (safe)
DROP PROCEDURE IF EXISTS _add_country_col;
DELIMITER $$
CREATE PROCEDURE _add_country_col()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'collaborators'
          AND COLUMN_NAME  = 'country'
    ) THEN
        ALTER TABLE collaborators
            ADD COLUMN country VARCHAR(100) NULL DEFAULT NULL
            AFTER position;
    END IF;
END$$
DELIMITER ;
CALL _add_country_col();
DROP PROCEDURE IF EXISTS _add_country_col;

-- Add area_id (safe)
DROP PROCEDURE IF EXISTS _add_area_id_col;
DELIMITER $$
CREATE PROCEDURE _add_area_id_col()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'collaborators'
          AND COLUMN_NAME  = 'area_id'
    ) THEN
        ALTER TABLE collaborators
            ADD COLUMN area_id INT UNSIGNED NULL DEFAULT NULL
            AFTER country;
    END IF;
END$$
DELIMITER ;
CALL _add_area_id_col();
DROP PROCEDURE IF EXISTS _add_area_id_col;

-- Add FK (safe: only if constraint does not already exist)
DROP PROCEDURE IF EXISTS _add_area_fk;
DELIMITER $$
CREATE PROCEDURE _add_area_fk()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
        WHERE TABLE_SCHEMA     = DATABASE()
          AND TABLE_NAME       = 'collaborators'
          AND CONSTRAINT_NAME  = 'fk_collaborators_area'
    ) THEN
        ALTER TABLE collaborators
            ADD CONSTRAINT fk_collaborators_area
                FOREIGN KEY (area_id) REFERENCES areas(id)
                ON DELETE SET NULL
                ON UPDATE CASCADE,
            ADD INDEX idx_collaborators_area_id (area_id);
    END IF;
END$$
DELIMITER ;
CALL _add_area_fk();
DROP PROCEDURE IF EXISTS _add_area_fk;
