<?php
header('Content-Type: application/json');
require_once '../config/auth.php';
require_once '../config/database.php';

ensure_api_auth(['admin']);

$conn = getDBConnection();
$type = $_GET['type'] ?? 'dashboard';

switch ($type) {
    case 'dashboard':
        // Today's sales
        $today = date('Y-m-d');
        $stmt = $conn->prepare("SELECT COUNT(*) as total_orders, 
                               SUM(final_amount) as total_revenue 
                               FROM sales 
                               WHERE DATE(created_at) = ? AND status = 'completed'");
        $stmt->bind_param("s", $today);
        $stmt->execute();
        $todayStats = $stmt->get_result()->fetch_assoc();
        
        // Low stock products
        $stmt = $conn->prepare("SELECT COUNT(*) as low_stock_count 
                               FROM products 
                               WHERE stock_quantity < 10 AND status = 'active'");
        $stmt->execute();
        $lowStock = $stmt->get_result()->fetch_assoc();
        
        // Top selling products (last 7 days)
        $stmt = $conn->prepare("SELECT p.name, SUM(si.quantity) as total_sold, SUM(si.subtotal) as revenue
                               FROM sale_items si
                               JOIN sales s ON si.sale_id = s.id
                               JOIN products p ON si.product_id = p.id
                               WHERE s.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND s.status = 'completed'
                               GROUP BY p.id, p.name
                               ORDER BY total_sold DESC
                               LIMIT 5");
        $stmt->execute();
        $topProducts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode([
            'today_orders' => intval($todayStats['total_orders']),
            'today_revenue' => floatval($todayStats['total_revenue'] ?? 0),
            'low_stock_count' => intval($lowStock['low_stock_count']),
            'top_products' => $topProducts
        ]);
        break;
        
    case 'sales':
        $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $end_date = $_GET['end_date'] ?? date('Y-m-d');
        
        $stmt = $conn->prepare("SELECT DATE(created_at) as date, 
                               COUNT(*) as orders, 
                               SUM(final_amount) as revenue 
                               FROM sales 
                               WHERE DATE(created_at) BETWEEN ? AND ? AND status = 'completed'
                               GROUP BY DATE(created_at)
                               ORDER BY date");
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        $sales = [];
        while ($row = $result->fetch_assoc()) {
            $sales[] = $row;
        }
        echo json_encode($sales);
        break;
}

$conn->close();
?>


