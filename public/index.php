<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

// ── Minimal bootstrap (needed before autoloader) ──────────────────
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';

// ── Global exception handler ──────────────────────────────────────
// Registered immediately so even autoloader failures are caught.
set_exception_handler(function (\Throwable $e): void {
    // Attempt to log — Logger may not be autoloaded yet, so we guard.
    $logFile = BASE_PATH . '/storage/logs/app.log';
    $entry   = sprintf(
        "[%s] CRITICAL ip=%s: %s in %s:%d\n",
        date('Y-m-d H:i:s'),
        $_SERVER['REMOTE_ADDR'] ?? 'cli',
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    );
    if (is_writable(dirname($logFile))) {
        file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    }

    http_response_code(500);

    $exception = $e;
    $refId     = strtoupper(substr(md5(uniqid('', true)), 0, 8));

    require BASE_PATH . '/views/errors/500.php';
    exit;
});

// Convert PHP errors to ErrorException so the exception handler catches them.
set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false; // respect @ operator
    }
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

// ── Autoloader ────────────────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    $map = [
        'core\\' => BASE_PATH . '/core/',
        'app\\'  => BASE_PATH . '/app/',
    ];
    foreach ($map as $prefix => $base) {
        if (str_starts_with($class, $prefix)) {
            $relative = substr($class, strlen($prefix));
            $file     = $base . str_replace('\\', '/', $relative) . '.php';
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }
});

require_once BASE_PATH . '/app/helpers/Session.php';
require_once BASE_PATH . '/app/helpers/Redirect.php';
require_once BASE_PATH . '/app/helpers/View.php';
require_once BASE_PATH . '/app/helpers/Csrf.php';
require_once BASE_PATH . '/app/helpers/Logger.php';
require_once BASE_PATH . '/app/helpers/Sanitizer.php';

// ── Security headers ──────────────────────────────────────────────
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

if (!APP_DEBUG) {
    // Only set HSTS in production (requires HTTPS)
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

// Remove server fingerprint headers
header_remove('X-Powered-By');
header_remove('Server');

// ── Session + router ──────────────────────────────────────────────
\app\helpers\Session::start();

$router = new core\Router();
require_once BASE_PATH . '/routes/web.php';
$router->dispatch();
