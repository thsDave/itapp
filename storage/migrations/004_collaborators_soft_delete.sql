-- ============================================================
-- Migration 004 — Collaborators soft delete
-- Adds deleted_at to collaborators for existing databases.
-- Safe to run multiple times (idempotent).
-- ============================================================

USE itapp;

DROP PROCEDURE IF EXISTS _m004_add_collab_deleted_at;
DELIMITER $$
CREATE PROCEDURE _m004_add_collab_deleted_at()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'collaborators'
          AND COLUMN_NAME  = 'deleted_at'
    ) THEN
        ALTER TABLE collaborators
            ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL
            AFTER updated_at;
    END IF;
END$$
DELIMITER ;
CALL _m004_add_collab_deleted_at();
DROP PROCEDURE IF EXISTS _m004_add_collab_deleted_at;
