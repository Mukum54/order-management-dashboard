<?php
namespace Models;
use Core\Model;
use PDO;

class Notification extends Model
{
    public static function getUnreadForUser(int $userId): array
    {
        $stmt = self::getDB()->prepare("SELECT * FROM notifications WHERE user_id = :user_id AND is_read = 0 ORDER BY created_at DESC");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public static function create(int $userId, string $type, string $title, string $message, ?int $orderId = null, bool $emailSent = false): bool
    {
        $stmt = self::getDB()->prepare("INSERT INTO notifications (user_id, order_id, type, title, message, email_sent) VALUES (:user_id, :order_id, :type, :title, :message, :email_sent)");
        return $stmt->execute([
            'user_id' => $userId,
            'order_id' => $orderId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'email_sent' => $emailSent ? 1 : 0
        ]);
    }

    public static function markRead(int $id, int $userId): bool
    {
        $stmt = self::getDB()->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id");
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }
}
