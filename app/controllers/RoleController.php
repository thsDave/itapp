<?php
declare(strict_types=1);

namespace app\controllers;

use app\helpers\View;
use app\helpers\Session;
use app\helpers\Redirect;
use app\helpers\Sanitizer;
use app\models\Role;

class RoleController
{
    private Role $model;

    public function __construct()
    {
        $this->model = new Role();
    }

    // ── GET /roles ────────────────────────────────────────────────────
    public function index(): void
    {
        View::render('roles/index', [
            'pageTitle' => 'Roles y Accesos',
            'roles'     => $this->model->all(),
        ]);
    }

    // ── GET /roles/create ─────────────────────────────────────────────
    public function create(): void
    {
        View::render('roles/create', [
            'pageTitle' => 'Nuevo rol',
            'modules'   => $this->model->getConfigurableModulesWithAccess(0),
            'old'       => Session::flash('old')    ?? [],
            'errors'    => Session::flash('errors') ?? [],
        ]);
    }

    // ── POST /roles/store ─────────────────────────────────────────────
    public function store(): void
    {
        $data   = $this->sanitize($_POST);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $_POST);
            Redirect::to('/roles/create');
        }

        $idrole = $this->model->insert([
            'role_name'   => $data['role_name'],
            'description' => $data['description'],
        ]);

        $this->model->syncModuleAccess(
            $idrole,
            array_map('intval', $_POST['modules'] ?? [])
        );

        Session::flash('notice', "Rol '{$data['role_name']}' creado correctamente.");
        Redirect::to('/roles');
    }

    // ── GET /roles/:id/edit ───────────────────────────────────────────
    public function edit(string $id): void
    {
        $role = $this->findOrAbort((int) $id);

        if ($role['role_name'] === Role::PROTECTED_ROLE) {
            Session::flash('error', 'El rol administrador no puede editarse.');
            Redirect::to('/roles');
        }

        View::render('roles/edit', [
            'pageTitle' => 'Editar rol',
            'role'      => $role,
            'modules'   => $this->model->getConfigurableModulesWithAccess((int) $id),
            'old'       => Session::flash('old')    ?? [],
            'errors'    => Session::flash('errors') ?? [],
        ]);
    }

    // ── POST /roles/:id/update ────────────────────────────────────────
    public function update(string $id): void
    {
        $role = $this->findOrAbort((int) $id);

        if ($role['role_name'] === Role::PROTECTED_ROLE) {
            Session::flash('error', 'El rol administrador no puede editarse.');
            Redirect::to('/roles');
        }

        $data   = $this->sanitize($_POST);
        $errors = $this->validate($data, (int) $id);

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $_POST);
            Redirect::to('/roles/' . $id . '/edit');
        }

        $this->model->update((int) $id, [
            'role_name'   => $data['role_name'],
            'description' => $data['description'],
        ]);

        $this->model->syncModuleAccess(
            (int) $id,
            array_map('intval', $_POST['modules'] ?? [])
        );

        Session::flash('notice', "Rol '{$data['role_name']}' actualizado correctamente.");
        Redirect::to('/roles');
    }

    // ── POST /roles/:id/delete ────────────────────────────────────────
    public function delete(string $id): void
    {
        $role = $this->findOrAbort((int) $id);

        if ($role['role_name'] === Role::PROTECTED_ROLE) {
            Session::flash('error', 'El rol administrador no puede eliminarse.');
            Redirect::to('/roles');
        }

        if ($this->model->userCount((int) $id) > 0) {
            Session::flash('error', "No se puede eliminar: hay usuarios asignados al rol '{$role['role_name']}'.");
            Redirect::to('/roles');
        }

        $this->model->delete((int) $id);

        Session::flash('notice', "Rol '{$role['role_name']}' eliminado.");
        Redirect::to('/roles');
    }

    // ── Private helpers ───────────────────────────────────────────────

    private function findOrAbort(int $id): array
    {
        $role = $this->model->find($id);

        if (!$role) {
            http_response_code(404);
            View::render('errors/404', [], 'auth');
            exit;
        }

        return $role;
    }

    private function sanitize(array $post): array
    {
        return [
            'role_name'   => strtolower(Sanitizer::string($post['role_name']   ?? '')),
            'description' => Sanitizer::string($post['description'] ?? ''),
        ];
    }

    private function validate(array $data, int $excludeId = 0): array
    {
        $errors = [];

        if ($data['role_name'] === '') {
            $errors[] = 'El nombre del rol es requerido.';
        } elseif (!preg_match('/^[a-z][a-z0-9_]{1,49}$/', $data['role_name'])) {
            $errors[] = 'El nombre del rol solo puede contener letras minúsculas, números y guiones bajos, y debe comenzar con una letra (máx. 50 caracteres).';
        } elseif ($data['role_name'] === Role::PROTECTED_ROLE) {
            $errors[] = 'No se puede usar "admin" como nombre de rol.';
        } elseif ($this->model->nameExists($data['role_name'], $excludeId ?: null)) {
            $errors[] = 'Ya existe un rol con ese nombre.';
        }

        if (mb_strlen($data['description']) > 255) {
            $errors[] = 'La descripción no puede superar 255 caracteres.';
        }

        return $errors;
    }
}
