-- ============================================================
-- Migration 005 — Add idstatus to collaborators
-- Run against an existing itapp database (migrations 000–004
-- already applied). Safe to run multiple times (idempotent).
-- Requires: status table seeded with idstatus 1=active, 3=deleted
-- ============================================================

USE itapp;

-- ── 1. Add idstatus column (nullable first for data migration) ─
DROP PROCEDURE IF EXISTS _m005_add_idstatus;
DELIMITER $$
CREATE PROCEDURE _m005_add_idstatus()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'collaborators'
          AND COLUMN_NAME  = 'idstatus'
    ) THEN
        ALTER TABLE collaborators
            ADD COLUMN idstatus INT UNSIGNED NULL DEFAULT NULL
            AFTER area_id;
    END IF;
END$$
DELIMITER ;
CALL _m005_add_idstatus();
DROP PROCEDURE IF EXISTS _m005_add_idstatus;

-- ── 2. Assign idstatus based on deleted_at ────────────────────
-- Rows already soft-deleted via deleted_at → mark as deleted (3).
-- All others → active (1).
UPDATE collaborators SET idstatus = 3 WHERE deleted_at IS NOT NULL AND idstatus IS NULL;
UPDATE collaborators SET idstatus = 1 WHERE idstatus IS NULL;

-- ── 3. Make idstatus NOT NULL DEFAULT 1 ──────────────────────
DROP PROCEDURE IF EXISTS _m005_notnull_idstatus;
DELIMITER $$
CREATE PROCEDURE _m005_notnull_idstatus()
BEGIN
    DECLARE v_nullable VARCHAR(3);
    SELECT IS_NULLABLE INTO v_nullable
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'collaborators'
      AND COLUMN_NAME  = 'idstatus';

    IF v_nullable = 'YES' THEN
        ALTER TABLE collaborators
            MODIFY COLUMN idstatus INT UNSIGNED NOT NULL DEFAULT 1;
    END IF;
END$$
DELIMITER ;
CALL _m005_notnull_idstatus();
DROP PROCEDURE IF EXISTS _m005_notnull_idstatus;

-- ── 4. Add FK and index ────────────────────────────────────────
DROP PROCEDURE IF EXISTS _m005_add_fk;
DELIMITER $$
CREATE PROCEDURE _m005_add_fk()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
        WHERE TABLE_SCHEMA    = DATABASE()
          AND TABLE_NAME      = 'collaborators'
          AND CONSTRAINT_NAME = 'fk_collaborators_status'
          AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    ) THEN
        ALTER TABLE collaborators
            ADD CONSTRAINT fk_collaborators_status
                FOREIGN KEY (idstatus) REFERENCES `status` (idstatus)
                ON DELETE RESTRICT
                ON UPDATE CASCADE,
            ADD INDEX idx_collaborators_idstatus (idstatus);
    END IF;
END$$
DELIMITER ;
CALL _m005_add_fk();
DROP PROCEDURE IF EXISTS _m005_add_fk;
