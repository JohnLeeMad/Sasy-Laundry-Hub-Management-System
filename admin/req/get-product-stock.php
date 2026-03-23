<?php
require_once '../../config/db_conn.php';

if (isset($_GET['id'])) {
    $productId = $_GET['id'];
    $stmt = $conn->prepare("SELECT stock_quantity FROM inventory WHERE product_id = ?");
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    header('Content-Type: application/json');
    echo json_encode([
        'stock_quantity' => $result ? $result['stock_quantity'] : 0
    ]);
    exit;
}

header('HTTP/1.1 400 Bad Request');
echo json_encode(['error' => 'Product ID is required']);
