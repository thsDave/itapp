<?php
declare(strict_types=1);

namespace app\helpers;

/**
 * Central authority for role permissions.
 *
 * Each role lists the route "areas" it may access.
 * Admin uses the '*' wildcard — it never needs to be listed explicitly.
 */
class Auth
{
    private const PERMISSIONS = [
        'admin'      => ['*'],
        'consultant' => ['dashboard', 'profile', 'collaborators', 'supports'],
        'user'       => ['profile'],
    ];

    /**
     * Where each role lands right after a successful login.
     */
    private const HOME = [
        'admin'      => '/dashboard',
        'consultant' => '/dashboard',
        'user'       => '/profile',
    ];

    /**
     * Sidebar navigation items.
     * 'roles' lists every role that can see the link.
     */
    public const NAV = [
        [
            'label' => 'Dashboard',
            'icon'  => 'fas fa-tachometer-alt',
            'url'   => '/dashboard',
            'match' => 'dashboard',
            'roles' => ['admin', 'consultant'],
        ],
        [
            'label' => 'Colaboradores',
            'icon'  => 'fas fa-users',
            'url'   => '/collaborators',
            'match' => 'collaborators',
            'roles' => ['admin', 'consultant'],
        ],
        [
            'label' => 'Soportes',
            'icon'  => 'fas fa-headset',
            'url'   => '/supports',
            'match' => 'supports',
            'roles' => ['admin', 'consultant'],
        ],
        [
            'label' => 'Usuarios',
            'icon'  => 'fas fa-user-cog',
            'url'   => '/users',
            'match' => 'users',
            'roles' => ['admin'],
        ],
        [
            'label' => 'Áreas',
            'icon'  => 'fas fa-sitemap',
            'url'   => '/areas',
            'match' => 'areas',
            'roles' => ['admin'],
        ],
        [
            'label' => 'Mi Perfil',
            'icon'  => 'fas fa-user-circle',
            'url'   => '/profile',
            'match' => 'profile',
            'roles' => ['admin', 'consultant', 'user'],
        ],
    ];

    // ── Public API ────────────────────────────────────────────────────

    /**
     * Check whether $role is allowed to access $area.
     * Admin bypasses every check.
     */
    public static function roleCanAccess(string $role, string $area): bool
    {
        $perms = self::PERMISSIONS[$role] ?? [];

        if (in_array('*', $perms, true)) {
            return true;
        }

        return in_array($area, $perms, true);
    }

    /**
     * Return the home URL for the given role.
     */
    public static function homeFor(string $role): string
    {
        return self::HOME[$role] ?? '/profile';
    }

    /**
     * Return the nav items visible to the given role.
     */
    public static function navFor(string $role): array
    {
        return array_values(array_filter(
            self::NAV,
            fn(array $item) => in_array($role, $item['roles'], true)
        ));
    }

    /**
     * Convenience: return the current session role.
     */
    public static function role(): string
    {
        return Session::get('user_role', '');
    }

    /**
     * True if the current session user has the given role(s).
     *
     * @param string|array $roles
     */
    public static function is(string|array $roles): bool
    {
        $roles = (array) $roles;
        return in_array(self::role(), $roles, true);
    }
}
