<?php
declare(strict_types=1);

namespace core;

class Router
{
    private array $routes = [];

    public function get(string $path, array $action, array $middleware = []): void
    {
        $this->add('GET', $path, $action, $middleware);
    }

    public function post(string $path, array $action, array $middleware = []): void
    {
        $this->add('POST', $path, $action, $middleware);
    }

    private function add(string $method, string $path, array $action, array $middleware): void
    {
        $pattern = preg_replace('#:([a-zA-Z0-9_]+)#', '(?P<$1>[^/]+)', $path);
        $this->routes[] = compact('method', 'pattern', 'action', 'middleware');
    }

    public function dispatch(): void
    {
        $url    = '/' . trim($_GET['url'] ?? '/', '/');
        $method = $_SERVER['REQUEST_METHOD'];

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match('#^' . $route['pattern'] . '$#', $url, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run middleware pipeline
                foreach ($route['middleware'] as $mw) {
                    // Support "ClassName:arg1,arg2" syntax
                    if (str_contains($mw, ':')) {
                        [$class, $args] = explode(':', $mw, 2);
                    } else {
                        $class = $mw;
                        $args  = '';
                    }
                    (new $class($args))->handle();
                }

                [$controllerClass, $controllerMethod] = $route['action'];
                $controller = new $controllerClass();
                $controller->$controllerMethod(...array_values($params));
                return;
            }
        }

        http_response_code(404);
        \app\helpers\View::render('errors/404', [], 'auth');
    }
}
