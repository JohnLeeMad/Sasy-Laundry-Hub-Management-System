<?php
require_once '../../config/db_conn.php';

$customerId = $_GET['customer_id'] ?? null;
$laundryId = $_GET['laundry_id'] ?? null;

if ($customerId) {
    $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->bind_param('i', $customerId);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();
    $balance = $customer['balance'] ?? 0;

    // Adjust balance based on the laundry order if needed
    if ($laundryId) {
        $stmt = $conn->prepare("SELECT total_price FROM laundry_lists WHERE id = ?");
        $stmt->bind_param('i', $laundryId);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $totalPrice = $order['total_price'] ?? 0;
        // Logic to determine how much balance was used (if tracked separately)
    }

    header('Content-Type: application/json');
    echo json_encode(['balance' => $balance]);
} else {
    echo json_encode(['balance' => 0]);
}
