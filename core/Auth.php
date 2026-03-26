<?php
namespace Core;

class Auth
{
    public static function initSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', $isSecure ? 1 : 0);
            ini_set('session.cookie_samesite', 'Lax');
            ini_set('session.gc_maxlifetime', defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 7200);
            session_start();
        }

        // Check for idle timeout (7200 seconds)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
            session_unset();
            session_destroy();
            session_start();
        }
        $_SESSION['last_activity'] = time();

        // Remember Me Logic (if not logged in but cookie exists)
        if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
            $parts = explode('|', $_COOKIE['remember_token']);
            if (count($parts) === 2) {
                $id = (int)$parts[0];
                $token = $parts[1];
                $db = \Models\User::getDB();
                $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND remember_token IS NOT NULL");
                $stmt->execute([$id]);
                $u = $stmt->fetchObject(\Models\User::class);
                if ($u && password_verify($token, $u->remember_token)) {
                    $_SESSION['user_id'] = $u->id;
                    $_SESSION['role'] = $u->role;
                    $_SESSION['name'] = $u->name;
                }
            }
        }
    }

    public static function requireRole(array $roles): void
    {
        self::initSession();
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
            self::redirectLogin();
        }

        if (!in_array($_SESSION['role'], $roles, true)) {
            http_response_code(403);
            if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
                require __DIR__ . '/../views/errors/403.php';
                exit;
            } else {
                header('Content-Type: application/json');
                die(json_encode(['success' => false, 'error' => 'Forbidden: Insufficient privileges', 'code' => 403]));
            }
        }
    }

    public static function checkCsrf(): void
    {
        self::initSession();
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            die(json_encode(['success' => false, 'error' => 'CSRF token validation failed', 'code' => 403]));
        }
    }

    private static function redirectLogin(): void
    {
        if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            header("Location: " . APP_URL . "/login");
            exit;
        } else {
            http_response_code(401);
            header('Content-Type: application/json');
            die(json_encode(['success' => false, 'error' => 'Unauthorized: Please login', 'code' => 401]));
        }
    }

    public static function generateCsrf(): string
    {
        self::initSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function userId(): ?int
    {
        self::initSession();
        return $_SESSION['user_id'] ?? null;
    }

    public static function userRole(): ?string
    {
        self::initSession();
        return $_SESSION['role'] ?? null;
    }

    public static function login($user): void
    {
        self::initSession();
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user->id;
        $_SESSION['role'] = $user->role;
        $_SESSION['name'] = $user->name;
    }

    public static function logout(): void
    {
        self::initSession();
        session_unset();
        session_destroy();
    }
}
