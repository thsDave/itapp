<?php
declare(strict_types=1);

namespace app\controllers;

use app\helpers\View;
use app\helpers\Session;
use app\helpers\Redirect;
use app\helpers\Sanitizer;
use app\models\User;
use app\models\Status;
use app\models\Role;

class UserController
{
    private const VALID_STATUSES = [User::STATUS_ACTIVE, User::STATUS_INACTIVE];

    private User   $model;
    private Status $statusModel;
    private Role   $roleModel;

    /** Available role names fetched once per request from the DB. */
    private array $availableRoles;

    public function __construct()
    {
        $this->model          = new User();
        $this->statusModel    = new Status();
        $this->roleModel      = new Role();
        $this->availableRoles = $this->roleModel->getRoleNames();
    }

    // ── GET /users ────────────────────────────────────────────────────
    public function index(): void
    {
        View::render('users/index', [
            'pageTitle' => 'Usuarios',
            'users'     => $this->model->all('created_at', 'DESC'),
        ]);
    }

    // ── GET /users/create ─────────────────────────────────────────────
    public function create(): void
    {
        View::render('users/create', [
            'pageTitle' => 'Nuevo usuario',
            'roles'     => $this->availableRoles,
            'statuses'  => $this->statusModel->forUsers(),
            'old'       => Session::flash('old') ?? [],
            'errors'    => Session::flash('errors') ?? [],
        ]);
    }

    // ── POST /users/store ─────────────────────────────────────────────
    public function store(): void
    {
        $data   = $this->sanitize($_POST);
        $errors = $this->validate($data, isCreate: true);

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $_POST);
            Redirect::to('/users/create');
        }

        $this->model->insert([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'role'     => $data['role'],
            'idstatus' => $data['idstatus'],
        ]);

        Session::flash('notice', 'Usuario creado correctamente.');
        Redirect::to('/users');
    }

    // ── GET /users/:id/edit ───────────────────────────────────────────
    public function edit(string $id): void
    {
        $user = $this->findOrAbort((int) $id);

        View::render('users/edit', [
            'pageTitle' => 'Editar usuario',
            'user'      => $user,
            'roles'     => $this->availableRoles,
            'statuses'  => $this->statusModel->forUsers(),
            'old'       => Session::flash('old') ?? [],
            'errors'    => Session::flash('errors') ?? [],
        ]);
    }

    // ── POST /users/:id/update ────────────────────────────────────────
    public function update(string $id): void
    {
        $this->findOrAbort((int) $id);
        $data   = $this->sanitize($_POST);
        $errors = $this->validate($data, isCreate: false, excludeId: (int) $id);

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $_POST);
            Redirect::to('/users/' . $id . '/edit');
        }

        $fields = [
            'name'     => $data['name'],
            'email'    => $data['email'],
            'role'     => $data['role'],
            'idstatus' => $data['idstatus'],
        ];

        if ($data['password'] !== '') {
            $fields['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        // Prevent admin from locking themselves out
        $selfId = (int) Session::get('user_id');
        if ((int) $id === $selfId) {
            unset($fields['role'], $fields['idstatus']); // own role/status is immutable
        }

        $this->model->update((int) $id, $fields);

        if ((int) $id === $selfId) {
            Session::set('user_name', $data['name']);
        }

        Session::flash('notice', 'Usuario actualizado correctamente.');
        Redirect::to('/users');
    }

    // ── POST /users/:id/delete ────────────────────────────────────────
    public function delete(string $id): void
    {
        $this->findOrAbort((int) $id);

        if ((int) $id === (int) Session::get('user_id')) {
            Session::flash('error', 'No puedes eliminar tu propia cuenta.');
            Redirect::to('/users');
        }

        $this->model->softDelete((int) $id);

        Session::flash('notice', 'Usuario eliminado.');
        Redirect::to('/users');
    }

    // ── POST /users/:id/toggle-status ────────────────────────────────
    public function toggleStatus(string $id): void
    {
        $this->findOrAbort((int) $id);

        if ((int) $id === (int) Session::get('user_id')) {
            Session::flash('error', 'No puedes desactivar tu propia cuenta.');
            Redirect::to('/users');
        }

        $this->model->toggleStatus((int) $id);

        Session::flash('notice', 'Estado del usuario actualizado.');
        Redirect::to('/users');
    }

    // ── Private helpers ───────────────────────────────────────────────

    private function findOrAbort(int $id): array
    {
        $user = $this->model->find($id);
        if (!$user) {
            http_response_code(404);
            View::render('errors/404', [], 'auth');
            exit;
        }
        return $user;
    }

    private function sanitize(array $post): array
    {
        return [
            'name'     => Sanitizer::string($post['name']     ?? ''),
            'email'    => Sanitizer::email($post['email']      ?? ''),
            'password' => $post['password'] ?? '',
            'role'     => Sanitizer::enum($post['role'] ?? '', $this->availableRoles),
            'idstatus' => Sanitizer::int($post['idstatus']     ?? 0),
        ];
    }

    private function validate(array $data, bool $isCreate, int $excludeId = 0): array
    {
        $errors = [];

        if ($data['name'] === '') {
            $errors[] = 'El nombre es requerido.';
        } elseif (mb_strlen($data['name']) > 100) {
            $errors[] = 'El nombre no puede superar 100 caracteres.';
        }

        if ($data['email'] === '') {
            $errors[] = 'El correo electrónico es requerido.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo electrónico no tiene un formato válido.';
        } elseif ($this->model->emailExists($data['email'], $excludeId ?: null)) {
            $errors[] = 'El correo electrónico ya está en uso.';
        }

        if ($isCreate) {
            if ($data['password'] === '') {
                $errors[] = 'La contraseña es requerida.';
            } elseif (strlen($data['password']) < 6) {
                $errors[] = 'La contraseña debe tener al menos 6 caracteres.';
            }
        } elseif ($data['password'] !== '' && strlen($data['password']) < 6) {
            $errors[] = 'La nueva contraseña debe tener al menos 6 caracteres.';
        }

        if (!in_array($data['role'], $this->availableRoles, true)) {
            $errors[] = 'El rol seleccionado no es válido.';
        }

        if (!in_array($data['idstatus'], self::VALID_STATUSES, true)) {
            $errors[] = 'El estado seleccionado no es válido.';
        }

        return $errors;
    }
}
