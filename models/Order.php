<?php
namespace Models;
use Core\Model;
use PDO;

class Order extends Model
{
    public static function paginate(int $limit, int $offset, array $filters = []): array
    {
        $where = [];
        $params = [];
        
        if (isset($filters['customer_id'])) {
            $where[] = "orders.customer_id = :customer_id";
            $params['customer_id'] = $filters['customer_id'];
        }
        if (isset($filters['assigned_to'])) {
            $where[] = "orders.assigned_to = :assigned_to";
            $params['assigned_to'] = $filters['assigned_to'];
        }
        if (!empty($filters['status'])) {
            $where[] = "orders.status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $where[] = "(orders.order_number LIKE :search OR users.name LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['date_from'])) {
            $where[] = "DATE(orders.created_at) >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "DATE(orders.created_at) <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        
        $whereSql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sortCol = 'orders.created_at';
        $sortDir = 'DESC';
        
        if (!empty($filters['sort'])) {
            $allowedSorts = [
                'id' => 'orders.id',
                'order_number' => 'orders.order_number',
                'customer_name' => 'users.name',
                'status' => 'orders.status',
                'total' => 'orders.total',
                'created_at' => 'orders.created_at'
            ];
            if (isset($allowedSorts[$filters['sort']])) {
                $sortCol = $allowedSorts[$filters['sort']];
            }
        }
        if (!empty($filters['dir']) && strtoupper($filters['dir']) === 'ASC') {
            $sortDir = 'ASC';
        }

        $sql = "SELECT orders.*, users.name as customer_name 
                FROM orders 
                LEFT JOIN users ON orders.customer_id = users.id 
                $whereSql 
                ORDER BY $sortCol $sortDir 
                LIMIT :limit OFFSET :offset";
                
        $stmt = self::getDB()->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(":$key", $val);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        $countSql = "SELECT COUNT(*) FROM orders LEFT JOIN users ON orders.customer_id = users.id $whereSql";
        $cStmt = self::getDB()->prepare($countSql);
        $cStmt->execute($params);
        $total = $cStmt->fetchColumn();
        
        return ['data' => $data, 'total' => $total];
    }
    
    public static function findById(int $id): ?object
    {
        $stmt = self::getDB()->prepare("
            SELECT orders.*, users.name as customer_name, users.email as customer_email, users.phone as customer_phone
            FROM orders 
            LEFT JOIN users ON orders.customer_id = users.id 
            WHERE orders.id = :id LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public static function updateStatus(int $id, string $status, int $changed_by, string $comment = ''): bool
    {
        $db = self::getDB();
        $db->beginTransaction();
        
        try {
            $order = self::findById($id);
            if (!$order) {
                $db->rollBack();
                return false;
            }
            
            $oldStatus = $order->status;
            
            $stmt = $db->prepare("UPDATE orders SET status = :status WHERE id = :id");
            $stmt->execute(['status' => $status, 'id' => $id]);
            
            $histStmt = $db->prepare("INSERT INTO order_status_history (order_id, changed_by, old_status, new_status, comment) VALUES (:order_id, :changed_by, :old_status, :new_status, :comment)");
            $histStmt->execute([
                'order_id' => $id,
                'changed_by' => $changed_by,
                'old_status' => $oldStatus,
                'new_status' => $status,
                'comment' => $comment
            ]);
            
            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            return false;
        }
    }
}
