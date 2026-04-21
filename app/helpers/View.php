<?php
declare(strict_types=1);

namespace app\helpers;

class View
{
    public static function render(string $view, array $data = [], string $layout = 'main'): void
    {
        extract($data, EXTR_SKIP);

        $viewFile   = BASE_PATH . '/views/' . $view . '.php';
        $layoutFile = BASE_PATH . '/views/layouts/' . $layout . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: {$viewFile}");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require $layoutFile;
    }
}
