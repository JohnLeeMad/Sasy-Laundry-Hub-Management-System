<?php
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['user_role'] !== 'admin') {
    $error_message = 'Unauthorized access detected. Please log in as an admin.';

    session_unset();
    session_destroy();

    session_start();
    $_SESSION['error'] = $error_message;

    header('Location: ../auth/unified-login.php');
    exit();
}

require_once __DIR__ . '/../../config/db_conn.php';

if (isset($_SESSION['user_id'])) {
    $admin_id = $_SESSION['user_id'];

    $check_query = "SELECT id, name FROM admins WHERE id = ? AND archived = 0";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param('i', $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $error_message = 'Your account has been deactivated. Please contact the system administrator.';

        session_unset();
        session_destroy();

        session_start();
        $_SESSION['error'] = $error_message;

        header('Location: ../auth/unified-login.php');
        exit();
    }

    $stmt->close();
}
