<?php
declare(strict_types=1);

namespace app\middleware;

use app\helpers\Csrf;
use app\helpers\Logger;
use app\helpers\Session;

class CsrfMiddleware
{
    public function __construct(string $args = '') {}

    public function handle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $submitted = $_POST['_csrf'] ?? '';

        if (!Csrf::verify($submitted)) {
            Logger::warning('CSRF token mismatch', [
                'uri'    => $_SERVER['REQUEST_URI'] ?? '',
                'method' => 'POST',
            ]);

            http_response_code(419);
            Session::flash('error', 'Solicitud inválida o expirada. Por favor, intenta nuevamente.');

            // Redirect back to the referring page, or home
            $ref = $_SERVER['HTTP_REFERER'] ?? APP_URL . '/dashboard';
            header('Location: ' . $ref);
            exit;
        }

        // Rotate token after each validated POST to limit replay window
        Csrf::regenerate();
    }
}
