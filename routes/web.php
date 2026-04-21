<?php

use app\controllers\AuthController;
use app\controllers\DashboardController;
use app\controllers\ProfileController;
use app\middleware\AuthMiddleware;
use app\middleware\CsrfMiddleware;
use app\middleware\ModuleMiddleware;
use app\middleware\RoleMiddleware;

// ┌─────────────────────────────────────────────────────────────────────────────┐
// │  Access layers                                                              │
// │                                                                             │
// │  ModuleMiddleware  — DB-driven; checks role_module_access for the given     │
// │                      module key. Admin always passes.                       │
// │                      Applied to: dashboard, collaborators, supports.        │
// │                                                                             │
// │  RoleMiddleware    — Hardcoded to ':admin'. Used for system-management      │
// │                      routes (users, areas, roles) that are never delegated. │
// └─────────────────────────────────────────────────────────────────────────────┘

// ── Public ────────────────────────────────────────────────────────────────────
$router->get( '/',       [AuthController::class, 'showLogin']);
$router->get( '/login',  [AuthController::class, 'showLogin']);
$router->post('/login',  [AuthController::class, 'login'],  [CsrfMiddleware::class]);
$router->get( '/logout', [AuthController::class, 'logout'], [AuthMiddleware::class]);

// ── Profile  (all authenticated users) ───────────────────────────────────────
$router->get( '/profile',        [ProfileController::class, 'index'],  [AuthMiddleware::class]);
$router->post('/profile/update', [ProfileController::class, 'update'], [AuthMiddleware::class, CsrfMiddleware::class]);

// ── Dashboard  (module-driven) ────────────────────────────────────────────────
$router->get('/dashboard', [DashboardController::class, 'index'], [
    AuthMiddleware::class,
    ModuleMiddleware::class . ':dashboard',
]);

// ── Collaborators  (module-driven) ────────────────────────────────────────────
$router->get( '/collaborators',            ['app\controllers\CollaboratorController', 'index'],  [AuthMiddleware::class, ModuleMiddleware::class . ':collaborators']);
$router->get( '/collaborators/create',     ['app\controllers\CollaboratorController', 'create'], [AuthMiddleware::class, ModuleMiddleware::class . ':collaborators']);
$router->get( '/collaborators/:id',        ['app\controllers\CollaboratorController', 'show'],   [AuthMiddleware::class, ModuleMiddleware::class . ':collaborators']);
$router->post('/collaborators/store',      ['app\controllers\CollaboratorController', 'store'],  [AuthMiddleware::class, CsrfMiddleware::class, ModuleMiddleware::class . ':collaborators']);
$router->get( '/collaborators/:id/edit',   ['app\controllers\CollaboratorController', 'edit'],   [AuthMiddleware::class, ModuleMiddleware::class . ':collaborators']);
$router->post('/collaborators/:id/update', ['app\controllers\CollaboratorController', 'update'], [AuthMiddleware::class, CsrfMiddleware::class, ModuleMiddleware::class . ':collaborators']);
$router->post('/collaborators/:id/delete', ['app\controllers\CollaboratorController', 'delete'], [AuthMiddleware::class, CsrfMiddleware::class, RoleMiddleware::class . ':admin']);

// ── Supports  (module-driven) ─────────────────────────────────────────────────
$router->get( '/supports',            ['app\controllers\SupportController', 'index'],  [AuthMiddleware::class, ModuleMiddleware::class . ':supports']);
$router->get( '/supports/create',     ['app\controllers\SupportController', 'create'], [AuthMiddleware::class, ModuleMiddleware::class . ':supports']);
$router->get( '/supports/:id',        ['app\controllers\SupportController', 'show'],   [AuthMiddleware::class, ModuleMiddleware::class . ':supports']);
$router->post('/supports/store',      ['app\controllers\SupportController', 'store'],  [AuthMiddleware::class, CsrfMiddleware::class, ModuleMiddleware::class . ':supports']);
$router->get( '/supports/:id/edit',   ['app\controllers\SupportController', 'edit'],   [AuthMiddleware::class, ModuleMiddleware::class . ':supports']);
$router->post('/supports/:id/update', ['app\controllers\SupportController', 'update'], [AuthMiddleware::class, CsrfMiddleware::class, ModuleMiddleware::class . ':supports']);
$router->post('/supports/:id/delete', ['app\controllers\SupportController', 'delete'], [AuthMiddleware::class, CsrfMiddleware::class, RoleMiddleware::class . ':admin']);

// ── Areas  (admin only — not configurable) ────────────────────────────────────
$router->get( '/areas',            ['app\controllers\AreaController', 'index'],  [AuthMiddleware::class, RoleMiddleware::class . ':admin']);
$router->get( '/areas/create',     ['app\controllers\AreaController', 'create'], [AuthMiddleware::class, RoleMiddleware::class . ':admin']);
$router->post('/areas/store',      ['app\controllers\AreaController', 'store'],  [AuthMiddleware::class, CsrfMiddleware::class, RoleMiddleware::class . ':admin']);
$router->get( '/areas/:id/edit',   ['app\controllers\AreaController', 'edit'],   [AuthMiddleware::class, RoleMiddleware::class . ':admin']);
$router->post('/areas/:id/update', ['app\controllers\AreaController', 'update'], [AuthMiddleware::class, CsrfMiddleware::class, RoleMiddleware::class . ':admin']);
$router->post('/areas/:id/delete', ['app\controllers\AreaController', 'delete'], [AuthMiddleware::class, CsrfMiddleware::class, RoleMiddleware::class . ':admin']);

// ── Users  (admin only — not configurable) ────────────────────────────────────
$router->get( '/users',                   ['app\controllers\UserController', 'index'],        [AuthMiddleware::class, RoleMiddleware::class . ':admin']);
$router->get( '/users/create',            ['app\controllers\UserController', 'create'],       [AuthMiddleware::class, RoleMiddleware::class . ':admin']);
$router->post('/users/store',             ['app\controllers\UserController', 'store'],        [AuthMiddleware::class, CsrfMiddleware::class, RoleMiddleware::class . ':admin']);
$router->get( '/users/:id/edit',          ['app\controllers\UserController', 'edit'],         [AuthMiddleware::class, RoleMiddleware::class . ':admin']);
$router->post('/users/:id/update',        ['app\controllers\UserController', 'update'],       [AuthMiddleware::class, CsrfMiddleware::class, RoleMiddleware::class . ':admin']);
$router->post('/users/:id/delete',        ['app\controllers\UserController', 'delete'],       [AuthMiddleware::class, CsrfMiddleware::class, RoleMiddleware::class . ':admin']);
$router->post('/users/:id/toggle-status', ['app\controllers\UserController', 'toggleStatus'], [AuthMiddleware::class, CsrfMiddleware::class, RoleMiddleware::class . ':admin']);

// ── Roles & Access  (admin only — not configurable) ───────────────────────────
$router->get( '/roles',            ['app\controllers\RoleController', 'index'],  [AuthMiddleware::class, RoleMiddleware::class . ':admin']);
$router->get( '/roles/create',     ['app\controllers\RoleController', 'create'], [AuthMiddleware::class, RoleMiddleware::class . ':admin']);
$router->post('/roles/store',      ['app\controllers\RoleController', 'store'],  [AuthMiddleware::class, CsrfMiddleware::class, RoleMiddleware::class . ':admin']);
$router->get( '/roles/:id/edit',   ['app\controllers\RoleController', 'edit'],   [AuthMiddleware::class, RoleMiddleware::class . ':admin']);
$router->post('/roles/:id/update', ['app\controllers\RoleController', 'update'], [AuthMiddleware::class, CsrfMiddleware::class, RoleMiddleware::class . ':admin']);
$router->post('/roles/:id/delete', ['app\controllers\RoleController', 'delete'], [AuthMiddleware::class, CsrfMiddleware::class, RoleMiddleware::class . ':admin']);
