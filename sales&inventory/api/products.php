<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Suppress direct PHP warnings from being output as HTML — always return valid JSON
ini_set('display_errors', '0');
error_reporting(0);

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/auth.php';
require_once '../config/database.php';

// Get request method - handle PUT method override for servers that don't support it
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST' && isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
    $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
}

$conn = getDBConnection();

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Get single product
            $id = intval($_GET['id']);
            $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p 
                                   LEFT JOIN categories c ON p.category_id = c.id 
                                   WHERE p.id = ?");
            if (!$stmt) {
                http_response_code(500);
                echo json_encode(['error' => 'Server error preparing statement']);
                break;
            }
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                http_response_code(500);
                echo json_encode(['error' => 'Server error executing query']);
                break;
            }

            // Bind result and fetch (compatible without mysqlnd)
            $meta = $stmt->result_metadata();
            if ($meta) {
                $fields = [];
                $row = [];
                while ($f = $meta->fetch_field()) {
                    $fields[] = &$row[$f->name];
                }
                call_user_func_array([$stmt, 'bind_result'], $fields);
                if ($stmt->fetch()) {
                    // Convert bound row to associative array
                    $product = [];
                    foreach ($row as $k => $v) {
                        $product[$k] = $v;
                    }
                    echo json_encode($product);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Product not found']);
                }
            } else {
                // No metadata, return not found
                http_response_code(404);
                echo json_encode(['error' => 'Product not found']);
            }
        } else {
            // Get all products
            $query = "SELECT p.*, c.name as category_name FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     ORDER BY p.created_at DESC";
            $result = $conn->query($query);
            if ($result === false) {
                http_response_code(500);
                echo json_encode(['error' => 'Server error querying products']);
                break;
            }

            $products = [];
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
            echo json_encode($products);
        }
        break;
        
    case 'POST':
        ensure_api_auth(['admin']);
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Check if this is an update request (has id in data)
        if (isset($data['id']) && !empty($data['id'])) {
            // Handle as UPDATE
            $id = intval($data['id']);
            $name = $data['name'] ?? '';
            $category_id = $data['category_id'] ?? null;
            $description = $data['description'] ?? '';
            $price = floatval($data['price'] ?? 0);
            $cost = floatval($data['cost'] ?? 0);
            $stock_quantity = intval($data['stock_quantity'] ?? 0);
            $unit = $data['unit'] ?? 'piece';
            
            if (empty($name) || $id <= 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid product data']);
                break;
            }
            
            $stmt = $conn->prepare("UPDATE products SET name=?, category_id=?, description=?, price=?, cost=?, stock_quantity=?, unit=? WHERE id=?");
            $stmt->bind_param("sisdddsi", $name, $category_id, $description, $price, $cost, $stock_quantity, $unit, $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => $stmt->error]);
            }
        } else {
            // Handle as INSERT
            $name = $data['name'] ?? '';
            $category_id = $data['category_id'] ?? null;
            $description = $data['description'] ?? '';
            $price = floatval($data['price'] ?? 0);
            $cost = floatval($data['cost'] ?? 0);
            $stock_quantity = intval($data['stock_quantity'] ?? 0);
            $unit = $data['unit'] ?? 'piece';
            
            if (empty($name)) {
                echo json_encode(['success' => false, 'error' => 'Product name is required']);
                break;
            }
            
            $stmt = $conn->prepare("INSERT INTO products (name, category_id, description, price, cost, stock_quantity, unit) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sisddds", $name, $category_id, $description, $price, $cost, $stock_quantity, $unit);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'id' => $conn->insert_id, 'message' => 'Product created successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => $stmt->error]);
            }
        }
        break;
        
    case 'PUT':
        ensure_api_auth(['admin']);
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id']) || empty($data['id'])) {
            echo json_encode(['success' => false, 'error' => 'Product ID is required for update']);
            break;
        }
        
        $id = intval($data['id']);
        $name = $data['name'] ?? '';
        $category_id = $data['category_id'] ?? null;
        $description = $data['description'] ?? '';
        $price = floatval($data['price'] ?? 0);
        $cost = floatval($data['cost'] ?? 0);
        $stock_quantity = intval($data['stock_quantity'] ?? 0);
        $unit = $data['unit'] ?? 'piece';
        
        if (empty($name) || $id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid product data']);
            break;
        }
        
        $stmt = $conn->prepare("UPDATE products SET name=?, category_id=?, description=?, price=?, cost=?, stock_quantity=?, unit=? WHERE id=?");
        $stmt->bind_param("sisdddsi", $name, $category_id, $description, $price, $cost, $stock_quantity, $unit, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }
        break;
        
    case 'DELETE':
        ensure_api_auth(['admin']);
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
            break;
        }
        
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        break;
}

$conn->close();
?>


