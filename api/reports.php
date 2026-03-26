<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

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
use PDO;

Auth::initSession();
$isExport = isset($_GET['action']) && in_array($_GET['action'], ['export_pdf', 'export_csv']);
if (!$isExport && (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest')) {
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => 'AJAX request required', 'code' => 403]));
}

header('Content-Type: application/json');
Auth::requireRole(['admin', 'manager']);

$action = $_GET['action'] ?? '';
$range = $_GET['range'] ?? '30d';

$db = Order::getDB();

// Mock Date Range Logic for demo
$dateFilter = "1=1";
if ($range === 'today') $dateFilter = "DATE(created_at) = CURDATE()";
if ($range === '7d') $dateFilter = "created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
if ($range === '30d') $dateFilter = "created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
if ($range === 'this_month') $dateFilter = "MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
if ($range === 'last_month') $dateFilter = "MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))";

if ($action === 'kpis') {
    $stmt = $db->query("SELECT 
        COUNT(*) as total_orders,
        SUM(IF(status='delivered', total, 0)) as revenue,
        SUM(IF(status='pending', 1, 0)) as pending_orders,
        SUM(IF(status='cancelled', 1, 0)) as cancelled_orders,
        SUM(IF(status='refunded', total, 0)) as refund_total
        FROM orders WHERE $dateFilter");
    
    $kpis = $stmt->fetch(PDO::FETCH_ASSOC);
    $total = (int)$kpis['total_orders'];
    $cancelled = (int)$kpis['cancelled_orders'];
    $delivered = (int)$db->query("SELECT COUNT(*) FROM orders WHERE status='delivered' AND $dateFilter")->fetchColumn();
    $rev = (float)$kpis['revenue'];
    
    echo json_encode([
        'success' => true,
        'data' => [
            'total_orders' => $total,
            'revenue' => number_format($rev, 2),
            'pending_orders' => (int)$kpis['pending_orders'],
            'aov' => $delivered > 0 ? number_format($rev / $delivered, 2) : '0.00',
            'cancelled_rate' => $total > 0 ? round(($cancelled / $total) * 100) : 0,
            'refund_total' => number_format((float)$kpis['refund_total'], 2)
        ]
    ]);
    exit;
}

if ($action === 'export_csv') {
    $stmt = $db->query("SELECT o.id, o.order_number, u.name as customer, o.status, o.total, o.created_at FROM orders o LEFT JOIN users u ON o.customer_id = u.id WHERE $dateFilter ORDER BY o.created_at DESC");
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="report_export_' . date('Ymd_His') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Order ID', 'Order Number', 'Customer Name', 'Status', 'Total', 'Created At'], ',', '"', '\\');
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['id'],
            $row['order_number'],
            $row['customer'],
            strtoupper($row['status']),
            '$' . number_format((float)$row['total'], 2),
            $row['created_at']
        ], ',', '"', '\\');
    }
    fclose($output);
    exit;
}

if ($action === 'chart_revenue' || $action === 'chart_orders') {
    $stmt = $db->query("SELECT DATE(created_at) as date, COUNT(*) as orders, SUM(total) as revenue FROM orders WHERE $dateFilter GROUP BY DATE(created_at) ORDER BY date ASC");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $labels = []; $values = [];
    foreach($data as $row) {
        $labels[] = date('M j', strtotime($row['date']));
        $values[] = $action === 'chart_revenue' ? (float)$row['revenue'] : (int)$row['orders'];
    }
    
    echo json_encode(['success' => true, 'labels' => $labels, 'values' => $values]);
    exit;
}

if ($action === 'chart_status') {
    $stmt = $db->query("SELECT status, COUNT(*) as count FROM orders WHERE $dateFilter GROUP BY status");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $labels = []; $values = [];
    foreach($data as $row) {
        $labels[] = ucfirst($row['status']);
        $values[] = (int)$row['count'];
    }
    
    echo json_encode(['success' => true, 'labels' => $labels, 'values' => $values]);
    exit;
}

if ($action === 'chart_products') {
    // Top 5 products by revenue
    $stmt = $db->query("SELECT product_name, SUM(subtotal) as rev FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE $dateFilter GROUP BY product_name ORDER BY rev DESC LIMIT 5");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $labels = []; $values = [];
    foreach($data as $row) {
        $labels[] = $row['product_name'];
        $values[] = (float)$row['rev'];
    }
    
    echo json_encode(['success' => true, 'labels' => $labels, 'values' => $values]);
    exit;
}

if ($action === 'chart_staff') {
    $stmt = $db->query("SELECT IFNULL(u.name, 'Unassigned') as staff, COUNT(o.id) as count FROM orders o LEFT JOIN users u ON o.assigned_to = u.id WHERE $dateFilter GROUP BY o.assigned_to ORDER BY count DESC LIMIT 5");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $labels = []; $values = [];
    foreach($data as $row) {
        $labels[] = $row['staff'];
        $values[] = (int)$row['count'];
    }
    
    echo json_encode(['success' => true, 'labels' => $labels, 'values' => $values]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
