<?php
namespace Core;

class Controller
{
    protected function view(string $viewPath, array $data = []): void
    {
        extract($data);
        $file = __DIR__ . '/../views/' . $viewPath . '.php';
        if (file_exists($file)) {
            require $file;
        } else {
            die("View file not found: {$viewPath}");
        }
    }

    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function redirect(string $path): void
    {
        header("Location: " . APP_URL . $path);
        exit;
    }

    protected function requireAjax(): void
    {
        if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            http_response_code(403);
            die(json_encode(['success' => false, 'error' => 'AJAX request required', 'code' => 403]));
        }
    }
}
