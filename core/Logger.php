<?php
namespace Core;
use Core\Model;
use Core\Auth;

class Logger {
    public static function log(string $action, ?int $user_id = null): void {
        try {
            $db = Model::getDB();
            $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, ip_address) VALUES (?, ?, ?)");
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $stmt->execute([$user_id ?? Auth::userId(), $action, $ip]);
        } catch (\Exception $e) {
            // Ignore for now
        }
    }
}
