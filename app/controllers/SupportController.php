<?php
declare(strict_types=1);

namespace app\controllers;

use app\helpers\View;
use app\helpers\Session;
use app\helpers\Redirect;
use app\helpers\Sanitizer;
use app\models\Support;
use app\models\Collaborator;
use app\models\User;

class SupportController
{
    private Support      $model;
    private Collaborator $collaboratorModel;
    private User         $userModel;

    public function __construct()
    {
        $this->model             = new Support();
        $this->collaboratorModel = new Collaborator();
        $this->userModel         = new User();
    }

    // ── GET /supports ─────────────────────────────────────────────────
    public function index(): void
    {
        $filters = $this->readFilters();

        View::render('supports/index', [
            'pageTitle'     => 'Soportes técnicos',
            'supports'      => $this->model->filter($filters),
            'collaborators' => $this->collaboratorModel->all('name', 'ASC'),
            'filters'       => $filters,
        ]);
    }

    // ── GET /supports/:id ─────────────────────────────────────────────
    public function show(string $id): void
    {
        $support = $this->findOrAbort((int) $id);

        View::render('supports/show', [
            'pageTitle' => 'Detalle del soporte',
            'support'   => $support,
        ]);
    }

    // ── GET /supports/create ──────────────────────────────────────────
    public function create(): void
    {
        // Allow pre-selecting a collaborator via ?collaborator_id=X
        $preSelected = (int) ($_GET['collaborator_id'] ?? 0);

        View::render('supports/create', [
            'pageTitle'     => 'Nuevo ticket',
            'collaborators' => $this->collaboratorModel->all('name', 'ASC'),
            'users'         => $this->userModel->all('name', 'ASC'),
            'old'           => Session::flash('old') ?? ['collaborator_id' => $preSelected ?: ''],
            'errors'        => Session::flash('errors') ?? [],
        ]);
    }

    // ── POST /supports/store ──────────────────────────────────────────
    public function store(): void
    {
        $data   = $this->sanitize($_POST);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $_POST);
            Redirect::to('/supports/create');
        }

        $id = $this->model->insert($this->buildFields($data));

        Session::flash('notice', 'Ticket registrado correctamente.');
        Redirect::to('/supports/' . $id);
    }

    // ── GET /supports/:id/edit ────────────────────────────────────────
    public function edit(string $id): void
    {
        $support = $this->findOrAbort((int) $id);

        View::render('supports/edit', [
            'pageTitle'     => 'Editar ticket',
            'support'       => $support,
            'collaborators' => $this->collaboratorModel->all('name', 'ASC'),
            'users'         => $this->userModel->all('name', 'ASC'),
            'old'           => Session::flash('old') ?? [],
            'errors'        => Session::flash('errors') ?? [],
        ]);
    }

    // ── POST /supports/:id/update ─────────────────────────────────────
    public function update(string $id): void
    {
        $this->findOrAbort((int) $id);

        $data   = $this->sanitize($_POST);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $_POST);
            Redirect::to('/supports/' . $id . '/edit');
        }

        $this->model->update((int) $id, $this->buildFields($data));

        Session::flash('notice', 'Ticket actualizado correctamente.');
        Redirect::to('/supports/' . $id);
    }

    // ── POST /supports/:id/delete ─────────────────────────────────────
    public function delete(string $id): void
    {
        $this->findOrAbort((int) $id);
        $this->model->delete((int) $id);

        Session::flash('notice', 'Ticket eliminado.');
        Redirect::to('/supports');
    }

    // ── Private helpers ───────────────────────────────────────────────

    private function findOrAbort(int $id): array
    {
        $row = $this->model->findWithRelations($id);
        if (!$row) {
            http_response_code(404);
            View::render('errors/404', [], 'auth');
            exit;
        }
        return $row;
    }

    private function readFilters(): array
    {
        return [
            'collaborator_id' => Sanitizer::int($_GET['collaborator_id'] ?? 0) ?: null,
            'level'           => Sanitizer::enum($_GET['level']     ?? '', Support::LEVELS),
            'status'          => Sanitizer::enum($_GET['status']    ?? '', Support::STATUSES),
            'date_from'       => Sanitizer::date($_GET['date_from'] ?? ''),
            'date_to'         => Sanitizer::date($_GET['date_to']   ?? ''),
        ];
    }

    private function sanitize(array $post): array
    {
        return [
            'collaborator_id'  => Sanitizer::int($post['collaborator_id'] ?? 0),
            'user_id'          => Sanitizer::int($post['user_id'] ?? 0),
            'title'            => Sanitizer::string($post['title']       ?? ''),
            'description'      => Sanitizer::text($post['description']   ?? ''),
            'attention_level'  => Sanitizer::enum($post['attention_level'] ?? '', Support::LEVELS),
            'status'           => Sanitizer::enum($post['status']        ?? '', Support::STATUSES),
            'notes'            => Sanitizer::text($post['notes']         ?? ''),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];

        // collaborator
        if (!$data['collaborator_id']) {
            $errors[] = 'El colaborador es requerido.';
        } elseif (!$this->collaboratorModel->find($data['collaborator_id'])) {
            $errors[] = 'El colaborador seleccionado no existe.';
        }

        // user_id is optional — but if provided must exist
        if ($data['user_id'] && !$this->userModel->find($data['user_id'])) {
            $errors[] = 'El usuario seleccionado no existe.';
        }

        // title
        if ($data['title'] === '') {
            $errors[] = 'El título es requerido.';
        } elseif (mb_strlen($data['title']) > 200) {
            $errors[] = 'El título no puede superar 200 caracteres.';
        }

        // attention_level
        if (!in_array($data['attention_level'], Support::LEVELS, true)) {
            $errors[] = 'El nivel de atención seleccionado no es válido.';
        }

        // status
        if (!in_array($data['status'], Support::STATUSES, true)) {
            $errors[] = 'El estado seleccionado no es válido.';
        }

        return $errors;
    }

    private function buildFields(array $data): array
    {
        return [
            'collaborator_id' => $data['collaborator_id'],
            'user_id'         => $data['user_id'] ?: null,
            'title'           => $data['title'],
            'description'     => $data['description'] ?: null,
            'attention_level' => $data['attention_level'],
            'status'          => $data['status'],
            'notes'           => $data['notes'] ?: null,
        ];
    }
}
