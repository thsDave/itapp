<?php
declare(strict_types=1);

namespace app\helpers;

class Redirect
{
    public static function to(string $path): never
    {
        header('Location: ' . APP_URL . $path);
        exit;
    }

    public static function back(): never
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? APP_URL . '/dashboard';
        header('Location: ' . $ref);
        exit;
    }
}
