<?php
namespace Core;

class Router
{
    private array $routes = [];

    public function add(string $method, string $path, callable|array $handler): void
    {
        // Convert path with params like {id} to regex
        $pathRegex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[a-zA-Z0-9_-]+)', $path);
        
        // Escape forward slashes 
        $pathRegex = str_replace('/', '\/', $pathRegex);

        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => '/^' . $pathRegex . '$/',
            'handler' => $handler
        ];
    }

    public function get(string $path, callable|array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function dispatch(string $uri, string $method): void
    {
        // Strip query string
        $uri = parse_url($uri, PHP_URL_PATH);
        
        // Remove app base path if needed (e.g. /order-dashboard)
        $basePath = parse_url(APP_URL, PHP_URL_PATH);
        if ($basePath && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }
        if (empty($uri)) {
            $uri = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $matches)) {
                // Extract named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                $handler = $route['handler'];
                
                if (is_array($handler)) {
                    $controller = new $handler[0]();
                    $action = $handler[1];
                    call_user_func_array([$controller, $action], $params);
                } else {
                    call_user_func_array($handler, $params);
                }
                return;
            }
        }

        // If no route matches
        $this->sendNotFound();
    }

    private function sendNotFound(): void
    {
        http_response_code(404);
        if (str_starts_with($_SERVER['REQUEST_URI'], parse_url(APP_URL, PHP_URL_PATH) . '/api/')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Not Found', 'code' => 404]);
        } else {
            require __DIR__ . '/../views/errors/404.php';
        }
    }
}
