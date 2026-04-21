<?php
declare(strict_types=1);

namespace app\middleware;

use app\helpers\Session;
use app\helpers\Redirect;
use app\helpers\Auth;
use app\controllers\AuthController;

class AuthMiddleware
{
    public function handle(): void
    {
        // Not logged in
        if (!Session::has('user_id')) {
            Session::flash('error', 'Debes iniciar sesión para continuar.');
            Redirect::to('/login');
        }

        // Fingerprint mismatch → possible session hijack
        if (!AuthController::validateFingerprint()) {
            Session::destroy();
            Session::start();
            Session::flash('error', 'Tu sesión expiró por seguridad. Por favor inicia sesión nuevamente.');
            Redirect::to('/login');
        }

        // Confirm user is still active in DB (catches deactivated accounts mid-session)
        $userId = (int) Session::get('user_id');
        $user   = (new \app\models\User())->findActive($userId);

        if (!$user) {
            Session::destroy();
            Session::start();
            Session::flash('error', 'Tu cuenta ha sido desactivada.');
            Redirect::to('/login');
        }

        // Refresh name/role in case an admin changed them
        Session::set('user_name', $user['name']);
        Session::set('user_role', $user['role']);
    }
}
