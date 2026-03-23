<?php
// Check if the user is logged in and has the correct role
if (!isset($_SESSION['staff_logged_in']) || $_SESSION['user_role'] !== 'staff') {
    // Store error message temporarily
    $error_message = 'Unauthorized access detected. Please log in as a staff member.';

    // Destroy the current session
    session_unset();
    session_destroy();

    // Start a new session to carry the error message
    session_start();
    $_SESSION['error'] = $error_message;

    // Redirect to unified login page
    header('Location: ../auth/unified-login.php');
    exit();
}

// NEW: Check if the staff account is archived
require_once __DIR__ . '/../../config/db_conn.php';

// Get the current staff's ID from session
if (isset($_SESSION['user_id'])) {
    $staff_id = $_SESSION['user_id'];

    // Check if staff exists and is not archived
    $check_query = "SELECT id, name FROM staffs WHERE id = ? AND archived = 0";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param('i', $staff_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Staff is archived or doesn't exist
        $error_message = 'Your account has been deactivated. Please contact the system administrator.';

        // Destroy the current session
        session_unset();
        session_destroy();

        // Start a new session to carry the error message
        session_start();
        $_SESSION['error'] = $error_message;

        // Redirect to unified login page
        header('Location: ../auth/unified-login.php');
        exit();
    }

    $stmt->close();
}
