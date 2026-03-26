<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

spl_autoload_register(function ($class) {
    $prefixMap = ['Core\\' => '../core/', 'Models\\' => '../models/'];
    foreach ($prefixMap as $prefix => $base_dir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) === 0) {
            $file = __DIR__ . '/' . $base_dir . str_replace('\\', '/', substr($class, $len)) . '.php';
            if (file_exists($file)) require $file;
        }
    }
});

use Core\Auth;
use Models\Notification;

Auth::initSession();

if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => 'AJAX request required', 'code' => 403]));
}
header('Content-Type: application/json');
if (!Auth::userId()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'list') {
    $notifs = Notification::getUnreadForUser(Auth::userId());
    echo json_encode(['success' => true, 'notifications' => $notifs, 'unread_count' => count($notifs)]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::checkCsrf();
    if ($action === 'mark_read') {
        $id = (int)($_POST['notification_id'] ?? 0);
        if ($id > 0) {
            Notification::markRead($id, Auth::userId());
        }
        echo json_encode(['success' => true]);
        exit;
    }
    if ($action === 'mark_all_read') {
        $db = Notification::getDB();
        $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([Auth::userId()]);
        echo json_encode(['success' => true]);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
