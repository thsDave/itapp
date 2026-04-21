-- ============================================================
-- Migration 007 — Link collaborators to their system user
-- Adds user_id FK to collaborators. Existing rows stay NULL
-- (only collaborators created after this migration have a user).
-- Safe to run multiple times (idempotent via stored procedures).
-- ============================================================

USE itapp;

-- ── 1. Add user_id column ─────────────────────────────────────
DROP PROCEDURE IF EXISTS _m007_add_user_id;
DELIMITER $$
CREATE PROCEDURE _m007_add_user_id()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'collaborators'
          AND COLUMN_NAME  = 'user_id'
    ) THEN
        ALTER TABLE collaborators
            ADD COLUMN user_id INT UNSIGNED NULL DEFAULT NULL
            AFTER area_id;
    END IF;
END$$
DELIMITER ;
CALL _m007_add_user_id();
DROP PROCEDURE IF EXISTS _m007_add_user_id;

-- ── 2. Add index ──────────────────────────────────────────────
DROP PROCEDURE IF EXISTS _m007_add_user_id_index;
DELIMITER $$
CREATE PROCEDURE _m007_add_user_id_index()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'collaborators'
          AND INDEX_NAME   = 'idx_collaborators_user_id'
    ) THEN
        ALTER TABLE collaborators
            ADD INDEX idx_collaborators_user_id (user_id);
    END IF;
END$$
DELIMITER ;
CALL _m007_add_user_id_index();
DROP PROCEDURE IF EXISTS _m007_add_user_id_index;

-- ── 3. Add FK collaborators.user_id → users.id ────────────────
DROP PROCEDURE IF EXISTS _m007_add_fk;
DELIMITER $$
CREATE PROCEDURE _m007_add_fk()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
        WHERE TABLE_SCHEMA    = DATABASE()
          AND TABLE_NAME      = 'collaborators'
          AND CONSTRAINT_NAME = 'fk_collaborators_user'
          AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    ) THEN
        ALTER TABLE collaborators
            ADD CONSTRAINT fk_collaborators_user
                FOREIGN KEY (user_id) REFERENCES users (id)
                ON DELETE SET NULL
                ON UPDATE CASCADE;
    END IF;
END$$
DELIMITER ;
CALL _m007_add_fk();
DROP PROCEDURE IF EXISTS _m007_add_fk;
