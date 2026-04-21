<?php
declare(strict_types=1);

namespace app\helpers;

class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    /** Return the current token, generating one if none exists. */
    public static function token(): string
    {
        $token = Session::get(self::SESSION_KEY);

        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set(self::SESSION_KEY, $token);
        }

        return $token;
    }

    /**
     * Validate a submitted token against the one stored in session.
     * Uses hash_equals to prevent timing attacks.
     */
    public static function verify(string $submitted): bool
    {
        $stored = Session::get(self::SESSION_KEY);

        if (!$stored || !$submitted) {
            return false;
        }

        return hash_equals($stored, $submitted);
    }

    /**
     * Discard current token and issue a fresh one.
     * Call after login/logout to bind the token to the new session context.
     */
    public static function regenerate(): string
    {
        Session::remove(self::SESSION_KEY);
        return self::token();
    }

    /** Render a hidden input ready to embed inside any <form>. */
    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="'
             . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8')
             . '">';
    }
}
