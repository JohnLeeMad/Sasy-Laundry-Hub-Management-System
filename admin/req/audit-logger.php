<?php
// Final fixed version of audit-logger.php

// Solution 1: Use dynamic path detection
$current_dir = __DIR__;
$project_root = dirname(dirname($current_dir)); // Go up two levels from admin/req/
$db_config_path = $project_root . '/config/db_conn.php';

// Check if the file exists, if not try alternative paths
if (!file_exists($db_config_path)) {
    // Try going up one level (if called from admin/ directory)
    $db_config_path = dirname($current_dir) . '/config/db_conn.php';

    if (!file_exists($db_config_path)) {
        // Try the original path (if called from admin/req/ directory)
        $db_config_path = '../config/db_conn.php';

        if (!file_exists($db_config_path)) {
            // Last resort: try from project root
            $db_config_path = '../../config/db_conn.php';
        }
    }
}

require_once $db_config_path;

function logActivity($userId, $userType, $userName, $action, $description = null)
{
    global $conn;

    if (!isset($conn)) {
        error_log("ERROR: Database connection not available in logActivity");
        return false;
    }

    // Get IP address
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

    // Get user agent
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

    $query = "INSERT INTO audit_logs (user_id, user_type, user_name, action, description, ip_address, user_agent) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);

    if (!$stmt) {
        error_log("ERROR: Failed to prepare statement: " . $conn->error);
        return false;
    }

    $stmt->bind_param("issssss", $userId, $userType, $userName, $action, $description, $ipAddress, $userAgent);

    $result = $stmt->execute();

    if (!$result) {
        error_log("ERROR: Failed to execute statement: " . $stmt->error);
        return false;
    }

    // CRITICAL FIX: Explicitly commit the audit log transaction
    // Check if autocommit is disabled, and if so, commit this transaction
    $autocommitResult = $conn->query("SELECT @@autocommit as autocommit_status");
    if ($autocommitResult) {
        $autocommitRow = $autocommitResult->fetch_assoc();
        if ($autocommitRow['autocommit_status'] == 0) {
            // Autocommit is disabled, so we need to explicitly commit
            $conn->commit();
            error_log("AUDIT: Explicitly committed audit log transaction");
        }
    }

    error_log("SUCCESS: Audit log inserted and committed with ID: " . $conn->insert_id);

    return $result;
}

// Common actions for reference:
// - login
// - logout
// - create_order
// - update_order
// - delete_order
// - add_supply
// - update_supply
// - process_payment
// - update_settings
// - add_user
// - update_user
// - delete_user
