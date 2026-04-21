-- ============================================================
-- Migration 003 — Status catalog table + users soft delete
-- Run against an existing itapp database (migrations 000–002
-- already applied). Safe to run multiple times (idempotent).
-- ============================================================

USE itapp;

-- ── 1. Create status table ────────────────────────────────────
CREATE TABLE IF NOT EXISTS `status` (
    idstatus   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    status     VARCHAR(50)  NOT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
                                     ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP        NULL DEFAULT NULL,

    PRIMARY KEY (idstatus),
    UNIQUE KEY uq_status_name (status)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ── 2. Seed statuses (idstatus values are fixed) ─────────────
INSERT IGNORE INTO `status` (idstatus, status) VALUES
(1, 'active'),
(2, 'inactive'),
(3, 'deleted');

-- ── 3. Add deleted_at to users ───────────────────────────────
DROP PROCEDURE IF EXISTS _m003_add_deleted_at;
DELIMITER $$
CREATE PROCEDURE _m003_add_deleted_at()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'users'
          AND COLUMN_NAME  = 'deleted_at'
    ) THEN
        ALTER TABLE users
            ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL
            AFTER updated_at;
    END IF;
END$$
DELIMITER ;
CALL _m003_add_deleted_at();
DROP PROCEDURE IF EXISTS _m003_add_deleted_at;

-- ── 4. Add idstatus (nullable first, for data migration) ─────
DROP PROCEDURE IF EXISTS _m003_add_idstatus;
DELIMITER $$
CREATE PROCEDURE _m003_add_idstatus()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'users'
          AND COLUMN_NAME  = 'idstatus'
    ) THEN
        ALTER TABLE users
            ADD COLUMN idstatus INT UNSIGNED NULL DEFAULT NULL
            AFTER role;
    END IF;
END$$
DELIMITER ;
CALL _m003_add_idstatus();
DROP PROCEDURE IF EXISTS _m003_add_idstatus;

-- ── 5. Migrate old status text values to idstatus ────────────
-- Only touches rows where idstatus is still NULL (first run).
UPDATE users SET idstatus = 1 WHERE `status` = 'active'   AND idstatus IS NULL;
UPDATE users SET idstatus = 2 WHERE `status` = 'inactive' AND idstatus IS NULL;
-- Any row still NULL (unexpected old value) defaults to active.
UPDATE users SET idstatus = 1 WHERE idstatus IS NULL;

-- ── 6. Make idstatus NOT NULL DEFAULT 1 ──────────────────────
DROP PROCEDURE IF EXISTS _m003_notnull_idstatus;
DELIMITER $$
CREATE PROCEDURE _m003_notnull_idstatus()
BEGIN
    DECLARE v_nullable VARCHAR(3);
    SELECT IS_NULLABLE INTO v_nullable
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'users'
      AND COLUMN_NAME  = 'idstatus';

    IF v_nullable = 'YES' THEN
        ALTER TABLE users
            MODIFY COLUMN idstatus INT UNSIGNED NOT NULL DEFAULT 1;
    END IF;
END$$
DELIMITER ;
CALL _m003_notnull_idstatus();
DROP PROCEDURE IF EXISTS _m003_notnull_idstatus;

-- ── 7. Add FK users.idstatus → status.idstatus ───────────────
DROP PROCEDURE IF EXISTS _m003_add_fk_idstatus;
DELIMITER $$
CREATE PROCEDURE _m003_add_fk_idstatus()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
        WHERE TABLE_SCHEMA    = DATABASE()
          AND TABLE_NAME      = 'users'
          AND CONSTRAINT_NAME = 'fk_users_status'
          AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    ) THEN
        ALTER TABLE users
            ADD CONSTRAINT fk_users_status
                FOREIGN KEY (idstatus) REFERENCES `status` (idstatus)
                ON DELETE RESTRICT
                ON UPDATE CASCADE,
            ADD INDEX idx_users_idstatus (idstatus);
    END IF;
END$$
DELIMITER ;
CALL _m003_add_fk_idstatus();
DROP PROCEDURE IF EXISTS _m003_add_fk_idstatus;

-- ── 8. Drop legacy status column + its index ─────────────────
DROP PROCEDURE IF EXISTS _m003_drop_status_col;
DELIMITER $$
CREATE PROCEDURE _m003_drop_status_col()
BEGIN
    -- Drop old index first (required before dropping indexed column).
    IF EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'users'
          AND INDEX_NAME   = 'idx_users_status'
    ) THEN
        ALTER TABLE users DROP INDEX idx_users_status;
    END IF;

    IF EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'users'
          AND COLUMN_NAME  = 'status'
    ) THEN
        ALTER TABLE users DROP COLUMN `status`;
    END IF;
END$$
DELIMITER ;
CALL _m003_drop_status_col();
DROP PROCEDURE IF EXISTS _m003_drop_status_col;
