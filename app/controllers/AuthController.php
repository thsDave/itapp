<?php
declare(strict_types=1);

namespace app\controllers;

use app\helpers\Session;
use app\helpers\Redirect;
use app\helpers\View;
use app\helpers\Auth;
use app\helpers\Csrf;
use app\helpers\Logger;
use app\helpers\Sanitizer;
use app\models\User;

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function showLogin(): void
    {
        if (Session::has('user_id')) {
            Redirect::to(Auth::homeFor(Session::get('user_role', '')));
        }

        View::render('auth/login', [
            'error'  => Session::flash('error'),
            'notice' => Session::flash('notice'),
        ], 'auth');
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Redirect::to('/login');
        }

        $email    = Sanitizer::email($_POST['email']    ?? '');
        $password = Sanitizer::string($_POST['password'] ?? '');

        // ── Input validation ──────────────────────────────────────────
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Ingresa un correo electrónico válido.');
            Redirect::to('/login');
        }

        if ($password === '' || strlen($password) < 6) {
            Session::flash('error', 'La contraseña debe tener al menos 6 caracteres.');
            Redirect::to('/login');
        }

        // ── Database lookup ───────────────────────────────────────────
        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            Logger::warning('Failed login — unknown email', ['email' => $email]);
            Session::flash('error', 'Credenciales incorrectas.');
            Redirect::to('/login');
        }

        if ($user['status_name'] !== 'active') {
            Logger::warning('Failed login — non-active account', ['user_id' => $user['id']]);
            Session::flash('error', 'Tu cuenta está desactivada. Contacta al administrador.');
            Redirect::to('/login');
        }

        if (!password_verify($password, $user['password'])) {
            Logger::warning('Failed login — wrong password', ['user_id' => $user['id']]);
            Session::flash('error', 'Credenciales incorrectas.');
            Redirect::to('/login');
        }

        // ── Session hardening ─────────────────────────────────────────
        Session::regenerate();
        Csrf::regenerate(); // bind fresh CSRF token to the new authenticated session

        Session::set('user_id',    (int) $user['id']);
        Session::set('user_name',  $user['name']);
        Session::set('user_email', $user['email']);
        Session::set('user_role',  $user['role']);
        Session::set('_fingerprint', self::fingerprint());

        Logger::info('Login successful', ['user_id' => $user['id'], 'role' => $user['role']]);

        Redirect::to(Auth::homeFor($user['role']));
    }

    public function logout(): void
    {
        $userId = Session::get('user_id');
        Session::destroy();
        Session::start();

        if ($userId) {
            Logger::info('Logout', ['user_id' => $userId]);
        }

        Session::flash('notice', 'Sesión cerrada correctamente.');
        Redirect::to('/login');
    }

    // ── Private helpers ───────────────────────────────────────────────

    private static function fingerprint(): string
    {
        return hash('sha256',
            ($_SERVER['HTTP_USER_AGENT']      ?? '') .
            ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '') .
            ($_SERVER['REMOTE_ADDR']          ?? '')
        );
    }

    public static function validateFingerprint(): bool
    {
        $stored  = Session::get('_fingerprint');
        $current = self::fingerprint();
        return $stored !== null && hash_equals($stored, $current);
    }
}
