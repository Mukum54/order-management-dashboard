<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

spl_autoload_register(function ($class) {
    // Basic API autoloader
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
use Models\Order;
use Core\Mailer;
use Models\Notification;

Auth::initSession();
$isExport = isset($_GET['action']) && in_array($_GET['action'], ['export_pdf', 'export_csv']);
if (!$isExport && (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest')) {
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => 'AJAX request required', 'code' => 403]));
}

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if ($action === 'export_pdf') {
        Auth::requireRole(['admin', 'manager', 'staff', 'customer']);
        $id = (int)($_GET['id'] ?? 0);
        $order = Order::findById($id);
        if (!$order) { die('Order not found'); }
        
        $role = Auth::userRole();
        $userId = Auth::userId();
        if ($role === 'customer' && $order->customer_id != $userId) { die('Forbidden'); }
        if ($role === 'staff' && $order->assigned_to != $userId) { die('Forbidden'); }

        $items = \Models\OrderItem::getByOrderId($id);
        
        $html = '<h1 style="font-family: sans-serif; color: #1E3A8A;">Invoice #' . htmlspecialchars($order->order_number, ENT_QUOTES, 'UTF-8') . '</h1>';
        $html .= '<p style="font-family: sans-serif;"><strong>Date:</strong> ' . date('M j, Y', strtotime($order->created_at)) . '</p>';
        $html .= '<p style="font-family: sans-serif;"><strong>Customer:</strong> ' . htmlspecialchars($order->customer_name, ENT_QUOTES, 'UTF-8') . '</p>';
        $html .= '<p style="font-family: sans-serif;"><strong>Status:</strong> ' . strtoupper(htmlspecialchars($order->status, ENT_QUOTES, 'UTF-8')) . '</p>';
        
        $html .= '<table border="1" cellpadding="8" cellspacing="0" width="100%" style="font-family: sans-serif; margin-top: 20px;">';
        $html .= '<thead><tr style="background:#F3F4F6;"><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead><tbody>';
        
        foreach($items as $item) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($item->product_name, ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . $item->quantity . '</td>';
            $html .= '<td>$' . number_format($item->unit_price, 2) . '</td>';
            $html .= '<td align="right">$' . number_format($item->subtotal, 2) . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody><tfoot>';
        $html .= '<tr><th colspan="3" align="right">Subtotal</th><td align="right">$' . number_format($order->subtotal, 2) . '</td></tr>';
        $html .= '<tr><th colspan="3" align="right">Tax</th><td align="right">$' . number_format($order->tax, 2) . '</td></tr>';
        $html .= '<tr><th colspan="3" align="right">Total</th><td align="right"><strong>$' . number_format($order->total, 2) . '</strong></td></tr>';
        $html .= '</tfoot></table>';

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("invoice_{$order->order_number}.pdf", ["Attachment" => true]);
        exit;
    }
    if ($action === 'list') {
        Auth::requireRole(['admin', 'manager', 'staff']);
        
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;
        
        $filters = [];
        if (Auth::userRole() === 'staff') {
            $filters['assigned_to'] = Auth::userId();
        }

        if (!empty($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }
        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        if (!empty($_GET['date_range'])) {
            if ($_GET['date_range'] === 'today') {
                $filters['date_from'] = date('Y-m-d');
                $filters['date_to'] = date('Y-m-d');
            } elseif ($_GET['date_range'] === '7days') {
                $filters['date_from'] = date('Y-m-d', strtotime('-7 days'));
                $filters['date_to'] = date('Y-m-d');
            } elseif ($_GET['date_range'] === '30days') {
                $filters['date_from'] = date('Y-m-d', strtotime('-30 days'));
                $filters['date_to'] = date('Y-m-d');
            }
        }
        if (!empty($_GET['sort'])) {
            $filters['sort'] = $_GET['sort'];
        }
        if (!empty($_GET['dir'])) {
            $filters['dir'] = $_GET['dir'];
        }

        $result = Order::paginate($limit, $offset, $filters);
        
        echo json_encode([
            'success' => true,
            'orders' => $result['data'],
            'total' => $result['total'],
            'pages' => ceil($result['total'] / $limit)
        ]);
        exit;
    }

    if ($action === 'export_csv') {
        Auth::requireRole(['admin', 'manager', 'staff']);
        
        $filters = [];
        if (Auth::userRole() === 'staff') {
            $filters['assigned_to'] = Auth::userId();
        }

        // Fetch all matching orders (no pagination limit)
        $result = Order::paginate(10000, 0, $filters);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="orders_export_' . date('Ymd_His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Order ID', 'Order Number', 'Customer Name', 'Status', 'Total', 'Created At'], ',', '"', '\\');
        
        foreach ($result['data'] as $row) {
            fputcsv($output, [
                $row->id,
                $row->order_number,
                $row->customer_name ?? 'Unknown',
                strtoupper($row->status),
                '$' . number_format($row->total, 2),
                $row->created_at
            ], ',', '"', '\\');
        }
        fclose($output);
        exit;
    }
}

if ($method === 'POST') {
    Auth::checkCsrf();

    if ($action === 'update_status') {
        Auth::requireRole(['admin', 'manager', 'staff', 'customer']);
        
        $id = (int)($_POST['order_id'] ?? 0);
        $newStatus = strtolower(trim($_POST['status'] ?? ''));
        $validStatuses = ['pending','processing','shipped','delivered','cancelled','refunded'];
        if (!in_array($newStatus, $validStatuses)) {
            echo json_encode(['success' => false, 'error' => 'Invalid status']);
            exit;
        }
        $comment = trim($_POST['comment'] ?? '');
        $tracking = trim($_POST['tracking_number'] ?? '');
        $notify = isset($_POST['notify_customer']) ? true : false;
        $assignTo = isset($_POST['assign_to']) && $_POST['assign_to'] !== '' ? (int)$_POST['assign_to'] : null;

        $order = Order::findById($id);
        if (!$order) {
            echo json_encode(['success' => false, 'error' => 'Order not found']);
            exit;
        }

        $role = Auth::userRole();
        $userId = Auth::userId();

        // RBAC validation
        if ($role === 'customer') {
            if ($order->customer_id != $userId || $newStatus !== 'cancelled' || $order->status !== 'pending') {
                echo json_encode(['success' => false, 'error' => 'Customers can only cancel pending orders']);
                exit;
            }
        } elseif ($role === 'staff') {
            if ($order->assigned_to != $userId) {
                echo json_encode(['success' => false, 'error' => 'Not assigned to this order']);
                exit;
            }
            if (!in_array($newStatus, ['processing', 'shipped'])) {
                echo json_encode(['success' => false, 'error' => 'Staff can only move to processing or shipped']);
                exit;
            }
        } elseif ($role === 'manager') {
            if ($newStatus === 'refunded') {
                echo json_encode(['success' => false, 'error' => 'Managers cannot refund orders']);
                exit;
            }
        }

        // Process Assignment
        if (($role === 'admin' || $role === 'manager') && $assignTo !== null) {
            $db = Order::getDB();
            $stmt = $db->prepare("UPDATE orders SET assigned_to = :a WHERE id = :id");
            $stmt->execute(['a' => $assignTo, 'id' => $id]);
        }

        // Process Tracking
        if ($newStatus === 'shipped' && !empty($tracking)) {
            $db = Order::getDB();
            $stmt = $db->prepare("UPDATE orders SET tracking_number = :t, shipped_at = NOW() WHERE id = :id");
            $stmt->execute(['t' => $tracking, 'id' => $id]);
        }

        if ($newStatus === 'delivered') {
            $db = Order::getDB();
            $stmt = $db->prepare("UPDATE orders SET delivered_at = NOW() WHERE id = :id");
            $stmt->execute(['id' => $id]);
        }
        if ($newStatus === 'cancelled') {
            $db = Order::getDB();
            $stmt = $db->prepare("UPDATE orders SET cancelled_at = NOW() WHERE id = :id");
            $stmt->execute(['id' => $id]);
        }

        $success = Order::updateStatus($id, $newStatus, $userId, $comment);

        // I-12 Inventory Logic
        if ($success && $newStatus === 'refunded' && $order->status !== 'refunded') {
            $db = Order::getDB();
            $items = \Models\OrderItem::getByOrderId($id);
            $stmt = $db->prepare("UPDATE products SET stock_qty = stock_qty + ? WHERE id = ?");
            foreach($items as $item) {
                $stmt->execute([$item->quantity, $item->product_id]);
            }
        }

        if ($success) {
            if ($notify) {
                // Determine template based on status
                $template = 'status_update';
                if ($newStatus === 'shipped') $template = 'order_shipped';
                if ($newStatus === 'delivered') $template = 'order_delivered';
                if ($newStatus === 'cancelled') $template = 'order_cancelled';
                if ($newStatus === 'refunded') $template = 'order_refunded';
                
                // Add notification to DB (email_sent could be true if mail succeeds)
                $mailData = [
                    'order' => $order,
                    'old_status' => $order->status,
                    'new_status' => $newStatus,
                    'comment' => $comment,
                    'tracking_number' => $tracking,
                    'cancel_reason' => $comment
                ];
                $mailSent = false;
                // Only send mail if user hasn't disabled it (we'd need a deeper look at user preference)
                if ($order->customer_email) {
                    // Try to send email
                    // Uncomment when PHPMailer is fully configured
                    // $mailSent = Mailer::send($order->customer_email, $order->customer_name, "Order #{$order->order_number} Update", $template, $mailData);
                }
                
                Notification::create($order->customer_id, 'status_change', "Order Status Updated", "Your order #{$order->order_number} is now {$newStatus}.", $id, $mailSent);
            }
            $historyHtml = '<div style="position: relative; margin-bottom: 20px;">
                <div class="new-dot" style="position: absolute; left: -20px; top: 4px; width: 10px; height: 10px; border-radius: 50%; background: var(--accent); z-index: 2; border: 2px solid var(--bg-card);"></div>
                <div style="font-weight: 600; font-size: 14px; margin-bottom: 4px;">
                    Status changed to <span style="text-transform: lowercase;">'.htmlspecialchars($newStatus, ENT_QUOTES, 'UTF-8').'</span>
                </div>
                <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 4px;">
                    By You on '.date('M j, Y h:i A').'
                </div>';
            if ($comment) {
                $historyHtml .= '<div style="font-size: 13px; font-style: italic; background: var(--bg-secondary); padding: 8px; border-radius: 6px; margin-top: 8px;">
                    "'.htmlspecialchars($comment, ENT_QUOTES, 'UTF-8').'"
                </div>';
            }
            $historyHtml .= '</div>';
            
            echo json_encode([
                'success' => true, 
                'new_status' => $newStatus, 
                'message' => 'Status updated successfully',
                'html' => $historyHtml
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update status']);
        }
        exit;
    }

    if ($action === 'bulk_update') {
        Auth::requireRole(['admin', 'manager', 'staff']);
        $ids = json_decode($_POST['order_ids'] ?? '[]');
        $newStatus = strtolower(trim($_POST['status'] ?? ''));
        $validStatuses = ['pending','processing','shipped','delivered','cancelled','refunded'];
        
        if (!is_array($ids) || empty($ids) || !in_array($newStatus, $validStatuses)) {
            echo json_encode(['success' => false, 'error' => 'Invalid data or status']);
            exit;
        }

        $userId = Auth::userId();
        $successCount = 0;
        
        foreach ($ids as $id) {
            if (Order::updateStatus((int)$id, $newStatus, $userId, 'Bulk updated by ' . Auth::userRole())) {
                $successCount++;
            }
        }
        
        echo json_encode(['success' => true, 'updated' => $successCount]);
        exit;
    }

    if ($action === 'add_note') {
        Auth::requireRole(['admin', 'manager', 'staff']);
        $id = (int)($_POST['order_id'] ?? 0);
        $note = trim($_POST['note'] ?? '');
        
        $db = Order::getDB();
        $stmt = $db->prepare("UPDATE orders SET notes = CONCAT(IFNULL(notes, ''), :note) WHERE id = :id");
        // format: \n[Date] User (Role): Note
        $line = "\n[" . date('Y-m-d H:i') . "] " . Auth::userId() . " (" . Auth::userRole() . "): " . $note;
        $stmt->execute(['note' => $line, 'id' => $id]);
        
        echo json_encode(['success' => true, 'note' => $line]);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
