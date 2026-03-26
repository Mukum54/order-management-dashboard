<?php
namespace Models;
use Core\Model;
use PDO;

class OrderItem extends Model
{
    public static function getByOrderId(int $orderId): array
    {
        $stmt = self::getDB()->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
