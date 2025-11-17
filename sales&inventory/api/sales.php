<?php
header('Content-Type: application/json');
require_once '../config/auth.php';
require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDBConnection();

switch ($method) {
    case 'GET':
        ensure_api_auth(['admin']);
        if (isset($_GET['id'])) {
            // Get single sale with items
            $id = intval($_GET['id']);
            $stmt = $conn->prepare("SELECT * FROM sales WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $sale = $stmt->get_result()->fetch_assoc();
            
            $stmt = $conn->prepare("SELECT si.*, p.name as product_name FROM sale_items si 
                                   JOIN products p ON si.product_id = p.id 
                                   WHERE si.sale_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $sale['items'] = $items;
            
            echo json_encode($sale);
        } else {
            // Get all sales
            $query = "SELECT * FROM sales ORDER BY created_at DESC LIMIT 100";
            $result = $conn->query($query);
            $sales = [];
            while ($row = $result->fetch_assoc()) {
                $sales[] = $row;
            }
            echo json_encode($sales);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $items = $data['items'];
        $total_amount = floatval($data['total_amount']);
        $discount = floatval($data['discount'] ?? 0);
        $final_amount = floatval($data['final_amount']);
        $payment_method = $data['payment_method'] ?? 'cash';
        
        // Generate order number
        $order_number = 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $conn->begin_transaction();
        
        try {
            // Insert sale
            $stmt = $conn->prepare("INSERT INTO sales (order_number, total_amount, discount, final_amount, payment_method) 
                                   VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sddds", $order_number, $total_amount, $discount, $final_amount, $payment_method);
            $stmt->execute();
            $sale_id = $conn->insert_id;
            
            // Insert sale items and update inventory
            foreach ($items as $item) {
                $product_id = intval($item['product_id']);
                $quantity = intval($item['quantity']);
                $unit_price = floatval($item['unit_price']);
                $subtotal = floatval($item['subtotal']);
                
                // Insert sale item
                $stmt = $conn->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, subtotal) 
                                       VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iiidd", $sale_id, $product_id, $quantity, $unit_price, $subtotal);
                $stmt->execute();
                
                // Update product stock
                $stmt = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
                $stmt->bind_param("ii", $quantity, $product_id);
                $stmt->execute();
                
                // Record inventory transaction
                $stmt = $conn->prepare("INSERT INTO inventory_transactions (product_id, transaction_type, quantity, reference_id) 
                                       VALUES (?, 'sale', ?, ?)");
                $stmt->bind_param("iii", $product_id, $quantity, $sale_id);
                $stmt->execute();
            }
            
            $conn->commit();
            echo json_encode(['success' => true, 'sale_id' => $sale_id, 'order_number' => $order_number]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;
}

$conn->close();
?>


