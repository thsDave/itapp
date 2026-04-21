<?php
declare(strict_types=1);

namespace app\middleware;

use app\helpers\Auth;
use app\helpers\Session;
use app\helpers\View;

/**
 * DB-driven access gate for configurable modules.
 *
 * Usage in routes: ModuleMiddleware::class . ':dashboard'
 *
 * Admin always passes. For all other roles, the module_key is
 * checked against role_module_access via Auth::roleCanAccess().
 */
class ModuleMiddleware
{
    private string $moduleKey;

    public function __construct(string $moduleKey = '')
    {
        $this->moduleKey = $moduleKey;
    }

    public function handle(): void
    {
        if ($this->moduleKey === '') {
            return;
        }

        $role = Session::get('user_role', '');

        if ($role === Auth::ADMIN_ROLE) {
            return;
        }

        if (!Auth::roleCanAccess($role, $this->moduleKey)) {
            http_response_code(403);
            View::render('errors/403', [], 'main');
            exit;
        }
    }
}
