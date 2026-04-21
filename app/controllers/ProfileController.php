<?php
declare(strict_types=1);

namespace app\controllers;

use app\helpers\View;
use app\helpers\Session;
use app\helpers\Redirect;
use app\models\User;

class ProfileController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function index(): void
    {
        $user = $this->userModel->find((int) Session::get('user_id'));

        if (!$user) {
            Session::destroy();
            Redirect::to('/login');
        }

        View::render('profile/index', [
            'pageTitle' => 'Mi Perfil',
            'user'      => $user,
        ]);
    }

    public function update(): void
    {
        $id   = (int) Session::get('user_id');
        $name = trim($_POST['name'] ?? '');

        if ($name === '') {
            Session::flash('error', 'El nombre no puede estar vacío.');
            Redirect::to('/profile');
        }

        $data = ['name' => $name];

        // Password change is optional
        $newPassword = trim($_POST['password'] ?? '');
        if ($newPassword !== '') {
            if (strlen($newPassword) < 6) {
                Session::flash('error', 'La nueva contraseña debe tener al menos 6 caracteres.');
                Redirect::to('/profile');
            }
            $data['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
        }

        $this->userModel->update($id, $data);
        Session::set('user_name', $name);

        Session::flash('notice', 'Perfil actualizado correctamente.');
        Redirect::to('/profile');
    }
}
