<?php
require_once '../config/db_conn.php';

// Get current user
$username = $_SESSION['name'] ?? 'Admin';

date_default_timezone_set('Asia/Manila');

// Get today's date
$today = date('Y-m-d');

// Fetch data using functions
$totalProfitToday = getTotalProfitToday($conn, $today);
$totalCustomersToday = getTotalCustomersToday($conn, $today);
$totalClaimedToday = getTotalClaimedToday($conn, $today);

// Function to get total profit today
function getTotalProfitToday($conn, $today)
{
    $query = "SELECT COALESCE(SUM(total_price), 0) AS total_profit 
              FROM laundry_lists 
              WHERE payment_status = 'Paid' 
              AND DATE(created_at) = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total_profit'];
}

// Function to get total customers today
function getTotalCustomersToday($conn, $today)
{
    $query = "SELECT COUNT(DISTINCT id) AS total_customers 
              FROM laundry_lists 
              WHERE DATE(created_at) = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total_customers'];
}

// Function to get total claimed laundry today
function getTotalClaimedToday($conn, $today)
{
    $query = "SELECT COUNT(*) AS total_claimed 
              FROM laundry_lists 
              WHERE status = 'Claimed' 
              AND DATE(created_at) = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total_claimed'];
}
// Add this function to your existing dashboard.php in the req folder
function getLowStockAlerts($conn)
{
    $lowStockThreshold = 3;

    $query = "SELECT 
                sp.name as product_name,
                sc.name as category_name,
                i.stock_quantity,
                i.available_units
              FROM inventory i
              JOIN supply_products sp ON i.product_id = sp.id
              JOIN supply_categories sc ON sp.category_id = sc.id
              WHERE sp.is_active = 1
              AND i.stock_quantity <= ?
              ORDER BY i.stock_quantity ASC, sc.name, sp.name";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $lowStockThreshold);
    $stmt->execute();
    $result = $stmt->get_result();

    $alerts = [];
    while ($row = $result->fetch_assoc()) {
        $alerts[] = $row;
    }

    return $alerts;
}
