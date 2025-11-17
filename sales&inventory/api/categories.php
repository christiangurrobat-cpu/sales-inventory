<?php
header('Content-Type: application/json');
// Suppress PHP notices/warnings being printed as HTML — return JSON errors instead
ini_set('display_errors', '0');
error_reporting(0);

require_once '../config/auth.php';
require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDBConnection();

switch ($method) {
    case 'GET':
        $query = "SELECT * FROM categories ORDER BY name";
        $result = $conn->query($query);
        if ($result === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Server error querying categories']);
            break;
        }
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        echo json_encode($categories);
        break;
        
    case 'POST':
        ensure_api_auth(['admin']);
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $data['name'];
        $description = $data['description'] ?? '';
        
        $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $description);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }
        break;
}

$conn->close();
?>


