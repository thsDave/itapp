-- ============================================================
-- Migration 006 — Roles, Modules, and Access Control
-- Introduces: roles, modules, role_module_access tables.
-- Converts users.role from ENUM to VARCHAR(50) with FK.
-- Safe to run on a fresh DB (from 000) or an existing one.
-- ============================================================

USE itapp;

-- ── 1. Create roles table ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS `roles` (
    idrole      INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    role_name   VARCHAR(50)   NOT NULL,
    description VARCHAR(255)  NULL DEFAULT NULL,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
                                       ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (idrole),
    UNIQUE KEY uq_roles_name (role_name)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ── 2. Create modules table ───────────────────────────────────
-- is_configurable = 1 → can be assigned to non-admin roles.
-- is_configurable = 0 → admin-only; not shown in role edit form.
CREATE TABLE IF NOT EXISTS `modules` (
    idmodule       INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    module_key     VARCHAR(50)   NOT NULL,
    module_name    VARCHAR(100)  NOT NULL,
    icon           VARCHAR(100)  NOT NULL DEFAULT 'fas fa-circle',
    route_prefix   VARCHAR(100)  NOT NULL,
    sort_order     INT UNSIGNED  NOT NULL DEFAULT 0,
    is_configurable TINYINT(1)   NOT NULL DEFAULT 1,
    created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
                                          ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (idmodule),
    UNIQUE KEY uq_modules_key (module_key)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ── 3. Create role_module_access pivot ────────────────────────
CREATE TABLE IF NOT EXISTS `role_module_access` (
    idrole   INT UNSIGNED NOT NULL,
    idmodule INT UNSIGNED NOT NULL,

    PRIMARY KEY (idrole, idmodule),

    CONSTRAINT fk_rma_role
        FOREIGN KEY (idrole)   REFERENCES `roles`   (idrole)
        ON DELETE CASCADE ON UPDATE CASCADE,

    CONSTRAINT fk_rma_module
        FOREIGN KEY (idmodule) REFERENCES `modules` (idmodule)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ── 4. Seed roles ─────────────────────────────────────────────
INSERT IGNORE INTO `roles` (idrole, role_name, description) VALUES
(1, 'admin',      'Administrador del sistema con acceso total'),
(2, 'consultant', 'Consultor con acceso a dashboard, colaboradores y soportes'),
(3, 'user',       'Usuario básico con acceso solo a su perfil');

-- ── 5. Seed modules ───────────────────────────────────────────
-- is_configurable=0 for admin-only modules (users, areas, roles).
INSERT IGNORE INTO `modules`
    (idmodule, module_key, module_name, icon, route_prefix, sort_order, is_configurable)
VALUES
(1, 'dashboard',     'Dashboard',       'fas fa-tachometer-alt', '/dashboard',    1, 1),
(2, 'collaborators', 'Colaboradores',   'fas fa-users',          '/collaborators',2, 1),
(3, 'supports',      'Soportes',        'fas fa-headset',        '/supports',     3, 1),
(4, 'users',         'Usuarios',        'fas fa-user-cog',       '/users',        4, 0),
(5, 'areas',         'Áreas',           'fas fa-sitemap',        '/areas',        5, 0),
(6, 'roles',         'Roles y Accesos', 'fas fa-shield-alt',     '/roles',        6, 0),
(7, 'profile',       'Mi Perfil',       'fas fa-user-circle',    '/profile',      7, 1);

-- ── 6. Seed role_module_access ────────────────────────────────
-- admin → all modules
INSERT IGNORE INTO `role_module_access` (idrole, idmodule)
SELECT 1, idmodule FROM `modules`;

-- consultant → dashboard, collaborators, supports, profile
INSERT IGNORE INTO `role_module_access` (idrole, idmodule)
SELECT 2, idmodule FROM `modules`
WHERE module_key IN ('dashboard', 'collaborators', 'supports', 'profile');

-- user → profile only
INSERT IGNORE INTO `role_module_access` (idrole, idmodule)
SELECT 3, idmodule FROM `modules`
WHERE module_key = 'profile';

-- ── 7. Convert users.role from ENUM to VARCHAR(50) ────────────
-- Preserves all existing data (ENUM values map directly to VARCHAR).
DROP PROCEDURE IF EXISTS _m006_modify_role_column;
DELIMITER $$
CREATE PROCEDURE _m006_modify_role_column()
BEGIN
    DECLARE v_type VARCHAR(255) DEFAULT '';
    SELECT COLUMN_TYPE INTO v_type
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'users'
      AND COLUMN_NAME  = 'role';

    IF v_type LIKE 'enum%' THEN
        ALTER TABLE users MODIFY COLUMN role VARCHAR(50) NOT NULL DEFAULT 'user';
    END IF;
END$$
DELIMITER ;
CALL _m006_modify_role_column();
DROP PROCEDURE IF EXISTS _m006_modify_role_column;

-- ── 8. Add FK users.role → roles.role_name ────────────────────
DROP PROCEDURE IF EXISTS _m006_add_role_fk;
DELIMITER $$
CREATE PROCEDURE _m006_add_role_fk()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
        WHERE TABLE_SCHEMA    = DATABASE()
          AND TABLE_NAME      = 'users'
          AND CONSTRAINT_NAME = 'fk_users_role'
          AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    ) THEN
        ALTER TABLE users
            ADD CONSTRAINT fk_users_role
                FOREIGN KEY (role) REFERENCES `roles` (role_name)
                ON DELETE RESTRICT
                ON UPDATE CASCADE;
    END IF;
END$$
DELIMITER ;
CALL _m006_add_role_fk();
DROP PROCEDURE IF EXISTS _m006_add_role_fk;
