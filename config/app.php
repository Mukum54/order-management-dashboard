<?php
// app.php
define('APP_NAME', 'Order Management Dashboard');
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
define('APP_URL', 'http://' . $host); 
// NOTE: Automatically detects if accessed via localhost or order-dashboard.local
define('APP_ENV', 'development'); // 'production' disables error display
define('APP_VERSION', '1.0.0');
define('SESSION_LIFETIME', 7200); // 2 hours
define('BCRYPT_COST', 12);
define('UPLOAD_PATH', __DIR__ . '/../public/img/uploads/');
define('MAX_UPLOAD_SIZE', 2097152); // 2MB

// Set error reporting based on environment
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}
