<?php
session_start();

// Determine current user role and prepare logout message
$user_role = '';
$user_name = '';
$logout_message = '';

if (isset($_SESSION['super_admin_logged_in']) && $_SESSION['super_admin_logged_in']) {
    $user_role = 'super_admin';
    $user_name = $_SESSION['user_name'] ?? 'Super Admin';
    // $logout_message = 'Super Admin logged out successfully.';
    
    // Unset super admin specific session variables
    unset($_SESSION['super_admin_logged_in']);
    unset($_SESSION['super_admin_id']);
    
} elseif (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    $user_role = 'admin';
    $user_name = $_SESSION['user_name'] ?? 'Admin';
    // $logout_message = 'Admin logged out successfully.';
    
    // Unset admin specific session variables
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['admin_id']);
    
} elseif (isset($_SESSION['staff_logged_in']) && $_SESSION['staff_logged_in']) {
    $user_role = 'staff';
    $user_name = $_SESSION['user_name'] ?? 'Staff';
    // $logout_message = 'Staff logged out successfully.';
    
    // Unset staff specific session variables
    unset($_SESSION['staff_logged_in']);
    unset($_SESSION['staff_id']);
}

// AUDIT LOGGING - Log logout activity
if ($user_role && file_exists('../admin/req/audit-logger.php')) {
    include '../admin/req/audit-logger.php';
    $user_id = $_SESSION['user_id'] ?? 0;
    logActivity($user_id, $user_role, $user_name, 'logout', 'User logged out successfully');
}

// If the user is only an admin/staff/super_admin (not also a customer), clear these common variables
if (in_array($_SESSION['user_role'] ?? '', ['super_admin', 'admin', 'staff'])) {
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_role']);
}

// Optional: Log the logout activity if database connection is available
if ($user_role && file_exists('../config/db_conn.php')) {
    try {
        require_once '../config/db_conn.php';
        
        // Check if activity_logs table exists before logging
        $table_check = $conn->query("SHOW TABLES LIKE 'activity_logs'");
        if ($table_check && $table_check->num_rows > 0) {
            $stmt = $conn->prepare("INSERT INTO activity_logs (user_type, action, description, ip_address, user_agent, created_at) VALUES (?, 'logout', ?, ?, ?, NOW())");
            $description = $user_name . ' (' . ucfirst(str_replace('_', ' ', $user_role)) . ') logged out';
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            $user_agent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255); // Limit length
            
            $stmt->bind_param("ssss", $user_role, $description, $ip_address, $user_agent);
            $stmt->execute();
            $stmt->close();
        }
        
        $conn->close();
    } catch (Exception $e) {
        // Log error but don't stop logout process
        error_log("Logout logging failed: " . $e->getMessage());
    }
}

// Set success message for login page
if (!empty($logout_message)) {
    $_SESSION['success'] = $logout_message;
}

// Regenerate session ID for security
session_regenerate_id(true);

// Generate new CSRF token for the login form
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Redirect to admin login page
header("Location: admin-login.php");
exit();
?>
[file content end]