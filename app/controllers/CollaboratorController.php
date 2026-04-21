<?php
declare(strict_types=1);

namespace app\controllers;

use app\helpers\View;
use app\helpers\Session;
use app\helpers\Redirect;
use app\helpers\Sanitizer;
use app\models\Collaborator;
use app\models\Area;
use app\models\Support;
use app\models\User;

class CollaboratorController
{
    private Collaborator $model;
    private Area         $areaModel;
    private Support      $supportModel;
    private User         $userModel;

    private const COUNTRIES = [
        'México',
        'Argentina', 'Bolivia', 'Brasil', 'Canadá', 'Chile',
        'Colombia', 'Costa Rica', 'Cuba', 'Ecuador', 'El Salvador',
        'España', 'Estados Unidos', 'Guatemala', 'Honduras', 'Nicaragua',
        'Panamá', 'Paraguay', 'Perú', 'Puerto Rico',
        'República Dominicana', 'Uruguay', 'Venezuela', 'Otro',
    ];

    private const DELETE_WORDS = [
        'BORRAR', 'ELIMINAR', 'CONFIRMAR', 'SUPRIMIR', 'ACEPTAR', 'PROCEDER',
    ];

    public function __construct()
    {
        $this->model        = new Collaborator();
        $this->areaModel    = new Area();
        $this->supportModel = new Support();
        $this->userModel    = new User();
    }

    // ── GET /collaborators ────────────────────────────────────────────
    public function index(): void
    {
        $collaborators = $this->model->allWithStatus();

        $deleteTokens = [];
        foreach ($collaborators as $c) {
            $deleteTokens[(int) $c['id']] = $this->generateDeleteToken();
        }

        View::render('collaborators/index', [
            'pageTitle'     => 'Colaboradores',
            'collaborators' => $collaborators,
            'deleteTokens'  => $deleteTokens,
        ]);
    }

    // ── GET /collaborators/:id ────────────────────────────────────────
    public function show(string $id): void
    {
        $collaborator = $this->findOrAbort((int) $id);

        View::render('collaborators/show', [
            'pageTitle'    => 'Detalle del colaborador',
            'collaborator' => $collaborator,
            'tickets'      => $this->supportModel->byCollaborator((int) $id),
            'deleteToken'  => $this->generateDeleteToken(),
        ]);
    }

    // ── GET /collaborators/create ─────────────────────────────────────
    public function create(): void
    {
        View::render('collaborators/create', [
            'pageTitle' => 'Nuevo colaborador',
            'areas'     => $this->areaModel->all('name', 'ASC'),
            'countries' => self::COUNTRIES,
            'old'       => Session::flash('old') ?? [],
            'errors'    => Session::flash('errors') ?? [],
        ]);
    }

    // ── POST /collaborators/store ─────────────────────────────────────
    public function store(): void
    {
        $data   = $this->sanitize($_POST);
        $errors = $this->validate($data, isCreate: true);

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            // Never repopulate the password field — strip it before flashing
            Session::flash('old', array_diff_key($_POST, ['password' => '']));
            Redirect::to('/collaborators/create');
        }

        try {
            $this->model->createWithUser(
                $this->buildFields($data) + ['idstatus' => Collaborator::STATUS_ACTIVE],
                $data['email'],
                $data['password']
            );
        } catch (\PDOException $e) {
            // Duplicate email at DB level (race condition after the emailExists check)
            $isDuplicate = str_contains($e->getMessage(), '1062')
                        || $e->getCode() == 23000;

            Session::flash('errors', [
                $isDuplicate
                    ? 'El correo electrónico ya está registrado en el sistema.'
                    : 'Error al guardar los datos. Por favor, inténtalo de nuevo.',
            ]);
            Session::flash('old', array_diff_key($_POST, ['password' => '']));
            Redirect::to('/collaborators/create');
        }

        Session::flash('notice', 'Colaborador y usuario creados correctamente.');
        Redirect::to('/collaborators');
    }

    // ── GET /collaborators/:id/edit ───────────────────────────────────
    public function edit(string $id): void
    {
        $collaborator = $this->findOrAbort((int) $id);

        View::render('collaborators/edit', [
            'pageTitle'    => 'Editar colaborador',
            'collaborator' => $collaborator,
            'areas'        => $this->areaModel->all('name', 'ASC'),
            'countries'    => self::COUNTRIES,
            'old'          => Session::flash('old') ?? [],
            'errors'       => Session::flash('errors') ?? [],
        ]);
    }

    // ── POST /collaborators/:id/update ────────────────────────────────
    public function update(string $id): void
    {
        $this->findOrAbort((int) $id);

        $data   = $this->sanitize($_POST);
        $errors = $this->validate($data, isCreate: false);

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $_POST);
            Redirect::to('/collaborators/' . $id . '/edit');
        }

        $this->model->update((int) $id, $this->buildFields($data));

        Session::flash('notice', 'Colaborador actualizado correctamente.');
        Redirect::to('/collaborators');
    }

    // ── POST /collaborators/:id/delete ────────────────────────────────
    public function delete(string $id): void
    {
        $this->findOrAbort((int) $id);

        $submittedWord = strtoupper(trim($_POST['_confirm_word']  ?? ''));
        $token         = trim($_POST['_confirm_token'] ?? '');
        $from          = $_POST['_from'] ?? 'list';
        $errorRedirect = $from === 'show'
            ? '/collaborators/' . $id
            : '/collaborators';

        if (!$this->validateDeleteToken($submittedWord, $token)) {
            Session::flash('error', 'La palabra de confirmación no es correcta. El colaborador no fue eliminado.');
            Redirect::to($errorRedirect);
        }

        $this->model->softDelete((int) $id);

        Session::flash('notice', 'Colaborador eliminado correctamente.');
        Redirect::to('/collaborators');
    }

    // ── Private helpers ───────────────────────────────────────────────

    private function findOrAbort(int $id): array
    {
        $row = $this->model->findWithArea($id);
        if (!$row) {
            http_response_code(404);
            View::render('errors/404', [], 'auth');
            exit;
        }
        return $row;
    }

    private function generateDeleteToken(): array
    {
        $word = self::DELETE_WORDS[array_rand(self::DELETE_WORDS)];
        $sig  = hash_hmac('sha256', $word, session_id() . APP_URL);
        return [
            'word'  => $word,
            'token' => base64_encode($word . '|' . $sig),
        ];
    }

    private function validateDeleteToken(string $submittedWord, string $token): bool
    {
        if ($submittedWord === '' || $token === '') {
            return false;
        }

        $decoded = base64_decode($token, strict: true);
        if ($decoded === false) {
            return false;
        }

        $parts = explode('|', $decoded, 2);
        if (count($parts) !== 2) {
            return false;
        }

        [$expectedWord, $sig] = $parts;

        $expectedSig = hash_hmac('sha256', $expectedWord, session_id() . APP_URL);

        return hash_equals($expectedSig, $sig)
            && hash_equals($expectedWord, $submittedWord);
    }

    private function sanitize(array $post): array
    {
        return [
            'name'               => Sanitizer::string($post['name']               ?? ''),
            'position'           => Sanitizer::string($post['position']           ?? ''),
            'country'            => Sanitizer::string($post['country']            ?? ''),
            'area_id'            => Sanitizer::int($post['area_id']               ?? 0),
            'entry_date'         => Sanitizer::date($post['entry_date']           ?? ''),
            'exit_date'          => Sanitizer::date($post['exit_date']            ?? ''),
            'assigned_equipment' => Sanitizer::text($post['assigned_equipment']   ?? ''),
            'email'              => Sanitizer::email($post['email']               ?? ''),
            'password'           => $post['password'] ?? '',
        ];
    }

    private function validate(array $data, bool $isCreate): array
    {
        $errors = [];

        if ($data['name'] === '') {
            $errors[] = 'El nombre es requerido.';
        } elseif (mb_strlen($data['name']) > 100) {
            $errors[] = 'El nombre no puede superar 100 caracteres.';
        }

        if ($data['position'] === '') {
            $errors[] = 'El puesto es requerido.';
        } elseif (mb_strlen($data['position']) > 100) {
            $errors[] = 'El puesto no puede superar 100 caracteres.';
        }

        if ($data['entry_date'] === '') {
            $errors[] = 'La fecha de ingreso es requerida.';
        } elseif (!$this->isValidDate($data['entry_date'])) {
            $errors[] = 'La fecha de ingreso no es válida.';
        }

        if ($data['exit_date'] !== '') {
            if (!$this->isValidDate($data['exit_date'])) {
                $errors[] = 'La fecha de egreso no es válida.';
            } elseif ($data['entry_date'] !== '' && $data['exit_date'] < $data['entry_date']) {
                $errors[] = 'La fecha de egreso no puede ser anterior a la de ingreso.';
            }
        }

        // Credentials — only required and validated on creation
        if ($isCreate) {
            if ($data['email'] === '') {
                $errors[] = 'El correo institucional es requerido.';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'El correo institucional no tiene un formato válido.';
            } elseif ($this->userModel->emailExists($data['email'])) {
                $errors[] = 'El correo institucional ya está registrado en el sistema.';
            }

            if ($data['password'] === '') {
                $errors[] = 'La contraseña es requerida.';
            } elseif (strlen($data['password']) < 8) {
                $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
            }
        }

        return $errors;
    }

    private function buildFields(array $data): array
    {
        return [
            'name'               => $data['name'],
            'position'           => $data['position'],
            'country'            => $data['country']            ?: null,
            'area_id'            => $data['area_id']            ?: null,
            'entry_date'         => $data['entry_date'],
            'exit_date'          => $data['exit_date']          ?: null,
            'assigned_equipment' => $data['assigned_equipment'] ?: null,
        ];
    }

    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
