<?php
namespace Models;
use Core\Model;
use PDO;

class User extends Model
{
    public static function findByEmail(string $email): ?object
    {
        $stmt = self::getDB()->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public static function findById(int $id): ?object
    {
        $stmt = self::getDB()->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public static function updateTheme(int $id, string $theme): bool
    {
        $stmt = self::getDB()->prepare("UPDATE users SET theme_preference = :theme WHERE id = :id");
        return $stmt->execute(['theme' => $theme, 'id' => $id]);
    }

    public static function getAll(): array
    {
        $stmt = self::getDB()->query("SELECT id, name, email, role, is_active FROM users ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
