<?php
namespace Controllers;
use Core\Controller;
use Core\Auth;
use Models\User;

class AuthController extends Controller
{
    public function index()
    {
        Auth::initSession();
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/orders');
        }
        $this->view('auth/login');
    }

    public function postLogin()
    {
        Auth::checkCsrf();

        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember_me']);

        if (!$email || empty($password)) {
            $this->json(['success' => false, 'error' => 'Valid email and password are required']);
        }

        $user = User::findByEmail($email);
        $db = User::getDB();

        if ($user) {
            // Check lockout
            if ($user->lockout_until && strtotime($user->lockout_until) > time()) {
                $rem = ceil((strtotime($user->lockout_until) - time()) / 60);
                $this->json(['success' => false, 'error' => "Account locked. Try again in {$rem} minutes."]);
            }

            if (password_verify($password, $user->password_hash)) {
                if (!$user->is_active) {
                    $this->json(['success' => false, 'error' => 'Account is deactivated']);
                }

                // Reset failed attempts
                $stmt = $db->prepare("UPDATE users SET failed_login_attempts = 0, lockout_until = NULL, last_login = NOW() WHERE id = ?");
                $stmt->execute([$user->id]);

                Auth::login($user);

                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $hash = password_hash($token, PASSWORD_BCRYPT);
                    $stmt = $db->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                    $stmt->execute([$hash, $user->id]);
                    $cookieValue = $user->id . '|' . $token;
                    $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
                    setcookie('remember_token', $cookieValue, time() + (86400 * 30), '/', '', $isSecure, true);
                }

                $redirect = '/orders';
                if ($user->role === 'customer') {
                    $redirect = '/profile';
                }
                $this->json(['success' => true, 'redirect' => APP_URL . $redirect]);
            } else {
                // Failed login
                $attempts = $user->failed_login_attempts + 1;
                $lockout = null;
                if ($attempts >= 5) {
                    $lockout = date('Y-m-d H:i:s', time() + (15 * 60)); // 15 mins
                }
                $stmt = $db->prepare("UPDATE users SET failed_login_attempts = ?, lockout_until = ? WHERE id = ?");
                $stmt->execute([$attempts, $lockout, $user->id]);

                $this->json(['success' => false, 'error' => 'Invalid credentials']);
            }
        } else {
            // Fake timing to prevent enumeration
            password_verify($password, '$2y$12$dummyhashdummyhashdummyhashdummyhashdummyh');
            $this->json(['success' => false, 'error' => 'Invalid credentials']);
        }
    }

    public function logout()
    {
        Auth::logout();
        $this->redirect('/login');
    }

    public function forgotView()
    {
        Auth::initSession();
        $this->view('auth/forgot', ['title' => 'Forgot Password']);
    }

    public function forgotPost()
    {
        Auth::checkCsrf();
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        
        if ($email) {
            $user = User::findByEmail($email);
            if ($user && $user->is_active) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour
                $db = User::getDB();
                $stmt = $db->prepare("UPDATE users SET reset_token = ?, reset_expires_at = ? WHERE id = ?");
                $stmt->execute([$token, $expires, $user->id]);
                
                // Need to send email
                $resetLink = APP_URL . '/reset-password?token=' . urlencode($token);
                // We'll queue sending the 'password_reset.php' email
                $db->prepare("INSERT INTO notifications (user_id, type, title, message) VALUES (?, 'system', 'Password Reset', ?)")
                   ->execute([$user->id, "Reset link: $resetLink"]);
            }
        }
        $this->json(['success' => true, 'message' => 'If that email exists, a reset link will be sent.']);
    }

    public function resetView()
    {
        Auth::initSession();
        $token = $_GET['token'] ?? '';
        $this->view('auth/reset', ['title' => 'Reset Password', 'token' => $token]);
    }

    public function resetPost()
    {
        Auth::checkCsrf();
        $token = $_POST['token'] ?? '';
        $new = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (empty($token) || empty($new) || empty($confirm)) {
            $this->json(['success' => false, 'error' => 'All fields required']);
        }
        if (strlen($new) < 8) {
            $this->json(['success' => false, 'error' => 'Password must be at least 8 characters']);
        }
        if ($new !== $confirm) {
            $this->json(['success' => false, 'error' => 'Passwords do not match']);
        }

        $db = User::getDB();
        $stmt = $db->prepare("SELECT id, reset_expires_at FROM users WHERE reset_token = ?");
        $stmt->execute([$token]);
        $u = $stmt->fetch(\PDO::FETCH_OBJ);

        if (!$u || strtotime($u->reset_expires_at) < time()) {
            $this->json(['success' => false, 'error' => 'Invalid or expired token']);
        }

        $hash = password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $db->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires_at = NULL, lockout_until = NULL, failed_login_attempts = 0 WHERE id = ?");
        if ($stmt->execute([$hash, $u->id])) {
            $this->json(['success' => true, 'redirect' => APP_URL . '/login']);
        } else {
            $this->json(['success' => false, 'error' => 'Database update failed']);
        }
    }
}
