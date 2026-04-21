-- ============================================================
-- Migration 001 — Add updated_at audit column (idempotent)
-- Safe to run on both:
--   • Fresh installs (000_initial_schema.sql already includes
--     updated_at — this migration detects that and skips)
--   • Legacy databases that were created without updated_at
-- Covers: users, collaborators, supports
-- ============================================================

USE itapp;

-- ── Idempotent helper: adds updated_at only if absent ────────
DROP PROCEDURE IF EXISTS _add_updated_at;

DELIMITER $$
CREATE PROCEDURE _add_updated_at(IN p_table VARCHAR(64))
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM   information_schema.COLUMNS
        WHERE  TABLE_SCHEMA = DATABASE()
          AND  TABLE_NAME   = p_table
          AND  COLUMN_NAME  = 'updated_at'
    ) THEN
        SET @sql = CONCAT(
            'ALTER TABLE `', p_table, '` ',
            'ADD COLUMN updated_at TIMESTAMP NOT NULL ',
            'DEFAULT CURRENT_TIMESTAMP ',
            'ON UPDATE CURRENT_TIMESTAMP ',
            'AFTER created_at'
        );
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$
DELIMITER ;

-- ── Apply to all three tables ─────────────────────────────────
CALL _add_updated_at('users');
CALL _add_updated_at('collaborators');
CALL _add_updated_at('supports');

-- ── Cleanup ───────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS _add_updated_at;
