<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/mail.php';

// Composer autoloader (PHPMailer)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Simple internal autoloader for our namespaces (Core, Models, Controllers)
spl_autoload_register(function ($class) {
    // Map prefix to directories
    $prefixMap = [
        'Core\\' => 'core/',
        'Models\\' => 'models/',
        'Controllers\\' => 'controllers/'
    ];

    foreach ($prefixMap as $prefix => $base_dir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }
        $relative_class = substr($class, $len);
        $file = __DIR__ . '/' . $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});

use Core\Router;

$router = new Router();

// HTTP Security Headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Content-Security-Policy: object-src 'none';");

// Authentication 
$router->get('/login', ['Controllers\AuthController', 'index']);
$router->post('/login', ['Controllers\AuthController', 'postLogin']);
$router->get('/logout', ['Controllers\AuthController', 'logout']);
$router->post('/logout', ['Controllers\AuthController', 'logout']);
$router->get('/forgot-password', ['Controllers\AuthController', 'forgotView']);
$router->post('/forgot-password', ['Controllers\AuthController', 'forgotPost']);
$router->get('/reset-password', ['Controllers\AuthController', 'resetView']);
$router->post('/reset-password', ['Controllers\AuthController', 'resetPost']);

// Orders
$router->get('/orders', ['Controllers\OrderController', 'index']);
$router->get('/orders/{id}', ['Controllers\OrderController', 'detail']);
$router->get('/orders/{id}/status', ['Controllers\OrderController', 'status']);

// Reports
$router->get('/reports', ['Controllers\ReportController', 'index']);

// Profile
$router->get('/profile', ['Controllers\ProfileController', 'index']);

// Home redirect
$router->get('/', function() {
    Core\Auth::initSession();
    if (isset($_SESSION['user_id'])) {
        $redirect = Core\Auth::userRole() === 'customer' ? '/profile' : '/orders';
        header("Location: " . APP_URL . $redirect);
    } else {
        header("Location: " . APP_URL . "/login");
    }
    exit;
});

// Dispatch request
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Handle API calls conventionally outside of router or pass them here if they hit index.php
if (str_starts_with($uri, parse_url(APP_URL, PHP_URL_PATH) . '/api/')) {
    // Let api scripts handle themselves, or route them?
    // According to specs: "All AJAX endpoints are in the /api/ directory."
    // We already rewrite all requests to index.php. So we can route them to actual PHP files.
    $apiPath = parse_url($uri, PHP_URL_PATH);
    $apiFile = str_replace(parse_url(APP_URL, PHP_URL_PATH), __DIR__, $apiPath);
    if (file_exists($apiFile)) {
        require_once $apiFile;
        exit;
    }
}

$router->dispatch($uri, $method);
