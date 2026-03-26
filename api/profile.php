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
use Models\User;

Auth::initSession();

// AJAX only
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => 'AJAX request required', 'code' => 403]));
}

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::checkCsrf();
    
    if ($action === 'theme') {
        $theme = $_POST['theme'] ?? 'dark';
        if (in_array($theme, ['light', 'dark'])) {
            if (Auth::userId()) {
                User::updateTheme(Auth::userId(), $theme);
                $_SESSION['theme'] = $theme;
            }
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid theme']);
        }
        exit;
    }

    if ($action === 'update') {
        Auth::requireRole(['admin', 'manager', 'staff', 'customer']);
        $userId = Auth::userId();
        
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        
        if (empty($name)) {
            echo json_encode(['success' => false, 'error' => 'Name is required']);
            exit;
        }

        $db = User::getDB();
        
        $sql = "UPDATE users SET name = :name, phone = :phone WHERE id = :id";
        $params = ['name' => $name, 'phone' => $phone, 'id' => $userId];
        
        $stmt = $db->prepare($sql);
        if ($stmt->execute($params)) {
            $_SESSION['name'] = $name;
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Update failed']);
        }
        exit;
    }

    if ($action === 'update_password') {
        Auth::requireRole(['admin', 'manager', 'staff', 'customer']);
        $userId = Auth::userId();
        
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (empty($current) || empty($new) || empty($confirm)) {
            echo json_encode(['success' => false, 'error' => 'All fields required']);
            exit;
        }

        if (strlen($new) < 8) {
            echo json_encode(['success' => false, 'error' => 'New password must be at least 8 characters']);
            exit;
        }

        if ($new !== $confirm) {
            echo json_encode(['success' => false, 'error' => 'Passwords do not match']);
            exit;
        }

        $user = User::findById($userId);
        if (!$user || !password_verify($current, $user->password_hash)) {
            echo json_encode(['success' => false, 'error' => 'Current password is incorrect']);
            exit;
        }

        $hash = password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]);
        $db = User::getDB();
        $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        if ($stmt->execute([$hash, $userId])) {
            echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update database']);
        }
        exit;
    }

    if ($action === 'update_preferences') {
        Auth::requireRole(['admin', 'manager', 'staff', 'customer']);
        $userId = Auth::userId();
        
        $email_notifications = isset($_POST['email_notifications']) ? (int)$_POST['email_notifications'] : 0;
        
        $db = User::getDB();
        $stmt = $db->prepare("UPDATE users SET email_notifications = ? WHERE id = ?");
        if ($stmt->execute([$email_notifications, $userId])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update preferences']);
        }
        exit;
    }

    if ($action === 'toggle_user_status') {
        Auth::requireRole(['admin']);
        $targetId = (int)($_POST['user_id'] ?? 0);
        if ($targetId === Auth::userId()) {
            echo json_encode(['success' => false, 'error' => 'Cannot toggle yourself']);
            exit;
        }

        $db = User::getDB();
        // Flip the is_active bit
        $stmt = $db->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
        if ($stmt->execute([$targetId])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to toggle status']);
        }
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'Invalid action or method']);
