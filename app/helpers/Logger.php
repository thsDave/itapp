<?php
declare(strict_types=1);

namespace app\helpers;

class Logger
{
    private const LOG_FILE = BASE_PATH . '/storage/logs/app.log';

    // ── Public API ────────────────────────────────────────────────────

    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('WARNING', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    public static function critical(string $message, array $context = []): void
    {
        self::write('CRITICAL', $message, $context);
    }

    /**
     * Log an exception at ERROR level.
     * Hides the full trace in production.
     */
    public static function exception(\Throwable $e, string $prefix = ''): void
    {
        $context = [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'code' => $e->getCode(),
        ];

        if (defined('APP_DEBUG') && APP_DEBUG) {
            $context['trace'] = $e->getTraceAsString();
        }

        self::error(($prefix ? $prefix . ': ' : '') . $e->getMessage(), $context);
    }

    // ── Private ───────────────────────────────────────────────────────

    private static function write(string $level, string $message, array $context): void
    {
        $dir = dirname(self::LOG_FILE);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $ip      = $_SERVER['REMOTE_ADDR'] ?? 'cli';
        $userId  = '';

        // Access session without triggering a start if not yet running
        if (session_status() === PHP_SESSION_ACTIVE) {
            $uid    = $_SESSION['user_id'] ?? null;
            $userId = $uid ? " user={$uid}" : '';
        }

        $ctx  = $context ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
        $line = sprintf(
            "[%s] %s ip=%s%s: %s%s\n",
            date('Y-m-d H:i:s'),
            str_pad($level, 8),
            $ip,
            $userId,
            $message,
            $ctx
        );

        file_put_contents(self::LOG_FILE, $line, FILE_APPEND | LOCK_EX);
    }
}
