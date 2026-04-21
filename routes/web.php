<?php

use app\controllers\AuthController;
use app\controllers\DashboardController;
use app\controllers\ProfileController;
use app\middleware\AuthMiddleware;
use app\middleware\CsrfMiddleware;
use app\middleware\RoleMiddleware;

// ┌─────────────────────────────────────────────────────────────────────────────┐
// │  Role matrix                                                                │
// │  admin      → everything (RoleMiddleware always passes admin through)       │
// │  consultant → dashboard, profile, collaborators, supports                  │
// │  user       → profile only                                                 │
// └─────────────────────────────────────────────────────────────────────────────┘

// ── Public ────────────────────────────────────────────────────────────────────
$router->get( '/',      [AuthController::class, 'showLogin']);
$router->get( '/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login'],  [CsrfMiddleware::class]);
$router->get( '/logout',[AuthController::class, 'logout'], [AuthMiddleware::class]);

// ── Profile  (all authenticated roles) ───────────────────────────────────────
$router->get( '/profile',        [ProfileController::class, 'index'],  [AuthMiddleware::class]);
$router->post('/profile/update', [ProfileController::class, 'update'], [AuthMiddleware::class, CsrfMiddleware::class]);

// ── Dashboard  (admin + consultant) ──────────────────────────────────────────
$router->get('/dashboard', [DashboardController::class, 'index'], [
    AuthMiddleware::class,
    RoleMiddleware::class . ':admin,consultant',
]);

// ── Collaborators  (admin + consultant) ──────────────────────────────────────
$router->get( '/collaborators',             ['app\controllers\CollaboratorController', 'index'],  [AuthMiddleware::class, RoleMiddleware::class . ':admin,consultant']);
$router->get( '/collaborators/create',      ['app\controllers\CollaboratorController', 'create'], [AuthMiddleware::class, RoleMiddleware::class . ':admin,consultant']);
$router->get( '/collaborators/:id',         ['app\controllers\CollaboratorController', 'show'],   [AuthMiddleware::class, RoleMiddleware::class . ':admin,consultant']);
$router->post('/collaborators/store',       ['app\controllers\CollaboratorController', 'store'],  [AuthMiddleware::class, CsrfMiddleware::class, RoleMiddleware::class . ':admin,consultant']);
$router->get( '/collaborators/:id/edit',    ['app\controllers\CollaboratorController', 'edit'],   [AuthMiddleware::class, RoleMiddleware::class . ':admin,consultant']);
$router->post('/collaborators/:id/update',  ['app\controllers\CollaboratorController', 'update'], [AuthMiddleware::class, CsrfMiddleware::class, RoleMiddleware::class . ':admin,consultant']);
$router->post('/collaborators/:id/delete',  ['app\controllers\CollaboratorController', 'delete'], [AuthMiddleware::class, CsrfMiddleware::class, RoleMiddleware::class . ':admin']);

// ── Supports  (admin + consultant) ───────────────────────────────────────────
$router->get( '/supports',            ['app\controllers\SupportController', 'index'],  [AuthMiddleware::class, RoleMiddleware::class . ':admin,consultant']);
$router->get( '/supports/create',     ['app\controllers\SupportController', 'create'], [AuthMiddleware::class, RoleMiddleware::class . ':admin,consultant']);
$router->get( '/supports/:id',        ['app\controllers\SupportController', 'show'],   [AuthMiddleware::class, RoleMiddleware::class . ':admin,consultant']);
$router->post('/supports/store',      ['app\controllers\SupportController', 'store'],  [AuthMiddleware::class, CsrfMiddleware::class, RoleMiddleware::class . ':admin,consultant']);
$router->get( '/supports/:id/edit',   ['app\controllers\SupportController', 'edit'],   [AuthMiddleware::class, RoleMiddleware::class . ':admin,consultant']);
$router->post('/supports/:id/update', ['app\controllers\SupportController', 'update'], [AuthMiddleware::class, CsrfMiddleware::class, RoleMiddleware::class . ':admin,consultant']);
$router->post('/supports/:id/delete', ['app\controllers\SupportController', 'delete'], [AuthMiddleware::class, CsrfMiddleware::class, RoleMiddleware::class . ':admin']);

// ── Areas  (admin only) ──────────────────────────────────────────────────────
$router->get( '/areas',            ['app\controllers\AreaController', 'index'],  [AuthMiddleware::class, RoleMiddleware::class . ':admin']);
$router->get( '/areas/create',     ['app\controllers\AreaController', 'create'], [AuthMiddleware::class, RoleMiddleware::class . ':admin']);
$router->post('/areas/store',      ['app\controllers\AreaController', 'store'],  [AuthMiddleware::class, CsrfMiddleware::class, RoleMiddleware::class . ':admin']);
$router->get( '/areas/:id/edit',   ['app\controllers\AreaController', 'edit'],   [AuthMiddleware::class, RoleMiddleware::class . ':admin']);
$router->post('/areas/:id/update', ['app\controllers\AreaController', 'update'], [AuthMiddleware::class, CsrfMiddleware::class, RoleMiddleware::class . ':admin']);
$router->post('/areas/:id/delete', ['app\controllers\AreaController', 'delete'], [AuthMiddleware::class, CsrfMiddleware::class, RoleMiddleware::class . ':admin']);

// ── Users  (admin only) ───────────────────────────────────────────────────────
$router->get( '/users',                    ['app\controllers\UserController', 'index'],        [AuthMiddleware::class, RoleMiddleware::class . ':admin']);
$router->get( '/users/create',             ['app\controllers\UserController', 'create'],       [AuthMiddleware::class, RoleMiddleware::class . ':admin']);
$router->post('/users/store',              ['app\controllers\UserController', 'store'],        [AuthMiddleware::class, CsrfMiddleware::class, RoleMiddleware::class . ':admin']);
$router->get( '/users/:id/edit',           ['app\controllers\UserController', 'edit'],         [AuthMiddleware::class, RoleMiddleware::class . ':admin']);
$router->post('/users/:id/update',         ['app\controllers\UserController', 'update'],       [AuthMiddleware::class, CsrfMiddleware::class, RoleMiddleware::class . ':admin']);
$router->post('/users/:id/delete',         ['app\controllers\UserController', 'delete'],       [AuthMiddleware::class, CsrfMiddleware::class, RoleMiddleware::class . ':admin']);
$router->post('/users/:id/toggle-status',  ['app\controllers\UserController', 'toggleStatus'], [AuthMiddleware::class, CsrfMiddleware::class, RoleMiddleware::class . ':admin']);
