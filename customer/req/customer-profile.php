<?php
session_start();

$username = $_SESSION['customer_name'] ?? 'Customer';

if (empty($_SESSION['customer_id'])) {
    header('Location: ../../auth/unified-login.php');
    exit();
}

require_once '../config/db_conn.php';

$customer_id = $_SESSION['customer_id'];

$sql = "SELECT id, name, email, contact_num, balance, created_at FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($customer = $result->fetch_assoc()) {
    $_SESSION['customer_name'] = $customer['name'];
    $_SESSION['customer_email'] = $customer['email'];
    $_SESSION['customer_contact_num'] = $customer['contact_num'];
    $_SESSION['customer_balance'] = $customer['balance'];
    $_SESSION['customer_details'] = $customer; 

    $customer_details = $customer;
} else {
    echo "Customer details not found.";
    exit();
}

$sql_customer_stats = "SELECT 
    MIN(created_at) as first_order_date,
    MAX(created_at) as last_order_date,
    COUNT(*) as total_orders,
    SUM(total_price) as lifetime_spending
    FROM laundry_lists 
    WHERE customer_id = ?";
$stmt_customer_stats = $conn->prepare($sql_customer_stats);
$stmt_customer_stats->bind_param("i", $customer_id);
$stmt_customer_stats->execute();
$customer_stats = $stmt_customer_stats->get_result()->fetch_assoc();

$_SESSION['customer_stats'] = $customer_stats;
