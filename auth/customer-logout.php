<?php
session_start();

// Database connection for cleaning up remember tokens
require_once '../config/db_conn.php';

// Get the current customer's ID to ensure we only log out this specific customer
$current_customer_id = $_SESSION['customer_id'] ?? null;

// Only proceed if there's actually a customer logged in
if ($current_customer_id) {
    // Clean up ALL remember me tokens for this customer from database
    if (isset($_COOKIE['customer_remember'])) {
        $token = $_COOKIE['customer_remember'];
        $stmt = $conn->prepare("DELETE FROM customer_tokens WHERE customer_id = ? AND token = ?");
        $stmt->bind_param("is", $current_customer_id, $token);
        $stmt->execute();
    }
    
    // Alternatively, you can delete ALL tokens for this customer:
    // $stmt = $conn->prepare("DELETE FROM customer_tokens WHERE customer_id = ?");
    // $stmt->bind_param("i", $current_customer_id);
    // $stmt->execute();
    
    // Remove the cookie
    setcookie('customer_remember', '', time() - 3600, '/', '', true, true);
    
    // Always unset customer-specific session variables
    unset($_SESSION['customer_logged_in']);
    unset($_SESSION['customer_id']);
    unset($_SESSION['customer_name']);
    unset($_SESSION['customer_email']);

    // Only unset common variables if this session belongs to THIS customer
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $current_customer_id) {
        // Check if user has other active sessions (admin or staff)
        $hasOtherSessions = isset($_SESSION['admin_logged_in']) || isset($_SESSION['staff_logged_in']);
        
        // Only clear common variables if no other sessions exist
        if (!$hasOtherSessions) {
            unset($_SESSION['user_id']);
            unset($_SESSION['user_name']);
            unset($_SESSION['user_email']);
            unset($_SESSION['user_role']);
        }
    }
}

// Clear any success/error messages related to customer actions
unset($_SESSION['success']);
unset($_SESSION['error']);
unset($_SESSION['warning']);

header("Location: customer-login.php");
exit;