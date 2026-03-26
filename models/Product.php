<?php
namespace Models;
use Core\Model;
use PDO;

class Product extends Model
{
    public static function getAll(): array
    {
        $stmt = self::getDB()->query("SELECT * FROM products WHERE is_active = 1");
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
