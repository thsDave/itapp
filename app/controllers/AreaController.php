<?php
declare(strict_types=1);

namespace app\controllers;

use app\helpers\View;
use app\helpers\Session;
use app\helpers\Redirect;
use app\helpers\Sanitizer;
use app\models\Area;

class AreaController
{
    private Area $model;

    public function __construct()
    {
        $this->model = new Area();
    }

    // ── GET /areas ────────────────────────────────────────────────────
    public function index(): void
    {
        View::render('areas/index', [
            'pageTitle' => 'Áreas institucionales',
            'areas'     => $this->model->all('name', 'ASC'),
        ]);
    }

    // ── GET /areas/create ─────────────────────────────────────────────
    public function create(): void
    {
        View::render('areas/create', [
            'pageTitle' => 'Nueva área',
            'old'       => Session::flash('old') ?? [],
            'errors'    => Session::flash('errors') ?? [],
        ]);
    }

    // ── POST /areas/store ─────────────────────────────────────────────
    public function store(): void
    {
        $data   = $this->sanitize($_POST);
        $errors = $this->validate($data, isCreate: true);

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $_POST);
            Redirect::to('/areas/create');
        }

        $this->model->insert(['name' => $data['name']]);

        Session::flash('notice', 'Área registrada correctamente.');
        Redirect::to('/areas');
    }

    // ── GET /areas/:id/edit ───────────────────────────────────────────
    public function edit(string $id): void
    {
        $area = $this->findOrAbort((int) $id);

        View::render('areas/edit', [
            'pageTitle' => 'Editar área',
            'area'      => $area,
            'old'       => Session::flash('old') ?? [],
            'errors'    => Session::flash('errors') ?? [],
        ]);
    }

    // ── POST /areas/:id/update ────────────────────────────────────────
    public function update(string $id): void
    {
        $this->findOrAbort((int) $id);

        $data   = $this->sanitize($_POST);
        $errors = $this->validate($data, isCreate: false, excludeId: (int) $id);

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $_POST);
            Redirect::to('/areas/' . $id . '/edit');
        }

        $this->model->update((int) $id, ['name' => $data['name']]);

        Session::flash('notice', 'Área actualizada correctamente.');
        Redirect::to('/areas');
    }

    // ── POST /areas/:id/delete ────────────────────────────────────────
    public function delete(string $id): void
    {
        $this->findOrAbort((int) $id);
        $this->model->delete((int) $id);

        Session::flash('notice', 'Área eliminada.');
        Redirect::to('/areas');
    }

    // ── Private helpers ───────────────────────────────────────────────

    private function findOrAbort(int $id): array
    {
        $area = $this->model->find($id);
        if (!$area) {
            http_response_code(404);
            View::render('errors/404', [], 'auth');
            exit;
        }
        return $area;
    }

    private function sanitize(array $post): array
    {
        return [
            'name' => Sanitizer::string($post['name'] ?? ''),
        ];
    }

    private function validate(array $data, bool $isCreate, int $excludeId = 0): array
    {
        $errors = [];

        if ($data['name'] === '') {
            $errors[] = 'El nombre del área es requerido.';
        } elseif (mb_strlen($data['name']) > 100) {
            $errors[] = 'El nombre no puede superar 100 caracteres.';
        } elseif ($this->model->nameExists($data['name'], $isCreate ? null : $excludeId)) {
            $errors[] = 'Ya existe un área con ese nombre.';
        }

        return $errors;
    }
}
