<?php
namespace Controllers;
use Core\Controller;
use Core\Auth;
use Models\Order;
use Models\User;
use Models\OrderItem;

class OrderController extends Controller
{
    public function index()
    {
        Auth::requireRole(['admin', 'manager', 'staff']);
        
        $staffMembers = User::getAll(); 

        $this->view('orders/list', [
            'title' => 'Orders',
            'role' => Auth::userRole(),
            'staffMembers' => $staffMembers
        ]);
    }

    public function detail($id)
    {
        Auth::requireRole(['admin', 'manager', 'staff', 'customer']);
        $order = Order::findById((int)$id);
        
        if (!$order) {
            $this->redirect('/orders');
        }

        // RBAC constraints
        $role = Auth::userRole();
        $userId = Auth::userId();
        
        if ($role === 'customer' && $order->customer_id != $userId) {
            http_response_code(403);
            require __DIR__ . '/../views/errors/403.php';
            exit;
        }
        if ($role === 'staff' && $order->assigned_to != $userId) {
            http_response_code(403);
            require __DIR__ . '/../views/errors/403.php';
            exit;
        }

        $items = OrderItem::getByOrderId($order->id);
        
        $db = Order::getDB();
        $stmt = $db->prepare("SELECT h.*, u.name as user_name FROM order_status_history h LEFT JOIN users u ON h.changed_by = u.id WHERE h.order_id = :id ORDER BY h.created_at DESC");
        $stmt->execute(['id' => $order->id]);
        $history = $stmt->fetchAll(\PDO::FETCH_OBJ);

        $staffMembers = [];
        if (in_array($role, ['admin', 'manager'])) {
            $staffMembers = User::getAll();
        }

        $this->view('orders/detail', [
            'title' => 'Order ' . $order->order_number,
            'order' => $order,
            'items' => $items,
            'history' => $history,
            'role' => $role,
            'staffMembers' => $staffMembers
        ]);
    }
}
