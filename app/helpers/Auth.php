<?php
declare(strict_types=1);

namespace app\helpers;

/**
 * Central authority for role permissions.
 *
 * Module access is now stored in the database (role_module_access table).
 * A per-request static cache avoids redundant queries on the same request.
 *
 * The 'admin' role always bypasses every check — it is never queried against
 * role_module_access and always receives all navigation items.
 */
class Auth
{
    public const ADMIN_ROLE = 'admin';

    /** Per-request module key cache, keyed by role name. */
    private static array $moduleCache = [];

    /** Per-request nav item cache, keyed by role name. */
    private static array $navCache = [];

    // ── Public API ────────────────────────────────────────────────────

    /**
     * Check whether $role is allowed to access $moduleKey.
     * Admin bypasses every check.
     */
    public static function roleCanAccess(string $role, string $moduleKey): bool
    {
        if ($role === self::ADMIN_ROLE) {
            return true;
        }

        return in_array($moduleKey, self::getModuleKeys($role), true);
    }

    /**
     * Return the post-login redirect URL for the given role.
     */
    public static function homeFor(string $role): string
    {
        if ($role === self::ADMIN_ROLE) {
            return '/dashboard';
        }

        $keys = self::getModuleKeys($role);

        if (in_array('dashboard', $keys, true)) {
            return '/dashboard';
        }

        return '/profile';
    }

    /**
     * Return the sidebar nav items visible to the given role.
     * Each item: ['label', 'icon', 'url', 'match']
     */
    public static function navFor(string $role): array
    {
        if (isset(self::$navCache[$role])) {
            return self::$navCache[$role];
        }

        try {
            $db = \DB::connect();

            if ($role === self::ADMIN_ROLE) {
                $stmt = $db->query(
                    "SELECT module_key, module_name, icon, route_prefix
                     FROM modules
                     ORDER BY sort_order"
                );
                $rows = $stmt->fetchAll();
            } else {
                $stmt = $db->prepare(
                    "SELECT m.module_key, m.module_name, m.icon, m.route_prefix
                     FROM role_module_access rma
                     JOIN roles   r ON r.idrole   = rma.idrole
                     JOIN modules m ON m.idmodule = rma.idmodule
                     WHERE r.role_name = ?
                     ORDER BY m.sort_order"
                );
                $stmt->execute([$role]);
                $rows = $stmt->fetchAll();
            }

            $items = array_map(fn(array $m) => [
                'label' => $m['module_name'],
                'icon'  => $m['icon'],
                'url'   => $m['route_prefix'],
                'match' => $m['module_key'],
            ], $rows);

        } catch (\Throwable) {
            $items = [];
        }

        self::$navCache[$role] = $items;
        return $items;
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

    // ── Private ───────────────────────────────────────────────────────

    /**
     * Fetch (and cache per request) the module keys accessible to $role.
     */
    private static function getModuleKeys(string $role): array
    {
        if (isset(self::$moduleCache[$role])) {
            return self::$moduleCache[$role];
        }

        try {
            $stmt = \DB::connect()->prepare(
                "SELECT m.module_key
                 FROM role_module_access rma
                 JOIN roles   r ON r.idrole   = rma.idrole
                 JOIN modules m ON m.idmodule = rma.idmodule
                 WHERE r.role_name = ?
                 ORDER BY m.sort_order"
            );
            $stmt->execute([$role]);
            self::$moduleCache[$role] = array_column($stmt->fetchAll(), 'module_key');
        } catch (\Throwable) {
            self::$moduleCache[$role] = [];
        }

        return self::$moduleCache[$role];
    }
}
