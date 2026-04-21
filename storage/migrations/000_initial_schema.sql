-- ============================================================
-- Migration 000 — Initial schema
-- Database: itapp
-- Engine:   InnoDB | Charset: utf8mb4 | Collation: utf8mb4_unicode_ci
-- ============================================================

CREATE DATABASE IF NOT EXISTS itapp
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE itapp;

-- ── status ───────────────────────────────────────────────────
-- Must be created before `users` due to FK dependency.
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


-- ── users ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name       VARCHAR(100)    NOT NULL,
    email      VARCHAR(150)    NOT NULL,
    password   VARCHAR(255)    NOT NULL,
    role       ENUM('admin','consultant','user') NOT NULL DEFAULT 'user',
    idstatus   INT UNSIGNED    NOT NULL DEFAULT 1,
    created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                        ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP           NULL DEFAULT NULL,

    PRIMARY KEY (id),
    UNIQUE  KEY uq_users_email    (email),
    INDEX       idx_users_role    (role),
    INDEX       idx_users_idstatus (idstatus),

    CONSTRAINT fk_users_status
        FOREIGN KEY (idstatus) REFERENCES `status` (idstatus)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ── areas ─────────────────────────────────────────────────────
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


-- ── collaborators ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS collaborators (
    id                 INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    name               VARCHAR(100)  NOT NULL,
    position           VARCHAR(100)  NOT NULL,
    country            VARCHAR(100)      NULL DEFAULT NULL,
    area_id            INT UNSIGNED      NULL DEFAULT NULL,
    idstatus           INT UNSIGNED  NOT NULL DEFAULT 1,
    entry_date         DATE          NOT NULL,
    exit_date          DATE              NULL DEFAULT NULL,
    assigned_equipment VARCHAR(255)      NULL DEFAULT NULL,
    created_at         TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
                                             ON UPDATE CURRENT_TIMESTAMP,
    deleted_at         TIMESTAMP         NULL DEFAULT NULL,

    PRIMARY KEY (id),

    CONSTRAINT fk_collaborators_area
        FOREIGN KEY (area_id) REFERENCES areas(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_collaborators_status
        FOREIGN KEY (idstatus) REFERENCES `status` (idstatus)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    INDEX idx_collaborators_area_id    (area_id),
    INDEX idx_collaborators_idstatus   (idstatus),
    INDEX idx_collaborators_entry_date (entry_date),
    INDEX idx_collaborators_exit_date  (exit_date)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ── supports ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS supports (
    id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    collaborator_id   INT UNSIGNED NOT NULL,
    user_id           INT UNSIGNED     NULL DEFAULT NULL,
    title             VARCHAR(200) NOT NULL,
    description       TEXT             NULL DEFAULT NULL,
    attention_level   ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
    status            ENUM('open','in_progress','closed')    NOT NULL DEFAULT 'open',
    notes             TEXT             NULL DEFAULT NULL,
    created_at        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
                                           ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    CONSTRAINT fk_supports_collaborator
        FOREIGN KEY (collaborator_id)
        REFERENCES  collaborators (id)
        ON DELETE   RESTRICT
        ON UPDATE   CASCADE,

    CONSTRAINT fk_supports_user
        FOREIGN KEY (user_id)
        REFERENCES  users (id)
        ON DELETE   SET NULL
        ON UPDATE   CASCADE,

    INDEX idx_supports_collaborator_id (collaborator_id),
    INDEX idx_supports_user_id         (user_id),
    INDEX idx_supports_status          (status),
    INDEX idx_supports_attention_level (attention_level),
    INDEX idx_supports_created_at      (created_at)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
