<?php
declare(strict_types=1);

namespace app\middleware;

use app\helpers\Session;
use app\helpers\Auth;
use app\helpers\View;

class RoleMiddleware
{
    private array $allowed;

    /**
     * @param string $roles  Comma-separated: "admin" | "admin,consultant"
     */
    public function __construct(string $roles = '')
    {
        $this->allowed = $roles !== ''
            ? array_map('trim', explode(',', $roles))
            : [];
    }

    public function handle(): void
    {
        if (empty($this->allowed)) {
            return;
        }

        $role = Session::get('user_role', '');

        // Admin bypasses every role restriction
        if ($role === 'admin') {
            return;
        }

        if (!in_array($role, $this->allowed, true)) {
            http_response_code(403);
            View::render('errors/403', [], 'main');
            exit;
        }
    }
}
