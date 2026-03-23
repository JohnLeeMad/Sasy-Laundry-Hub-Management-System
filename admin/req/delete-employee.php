<?php
session_start();
require_once '../../config/db_conn.php';
require_once __DIR__ . '/audit-logger.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method.';
    header('Location: ../employees.php');
    exit;
}

if (!isset($_POST['id']) || !isset($_POST['type'])) {
    $_SESSION['error'] = 'Missing required fields.';
    header('Location: ../employees.php');
    exit;
}

$id = intval($_POST['id']);
$type = strtolower(trim($_POST['type']));

if (!in_array($type, ['admin', 'staff'])) {
    $_SESSION['error'] = 'Invalid user type.';
    header('Location: ../employees.php');
    exit;
}

$table = ($type === 'admin') ? 'admins' : 'staffs';

// PROTECTION: Check if trying to delete self
$currentUserId = $_SESSION['user_id'] ?? null;
$currentRole = $_SESSION['user_role'] ?? null; // 'admin' or 'staff'

if ($currentUserId && $id == $currentUserId && $type == $currentRole) {
    $_SESSION['error'] = 'You cannot delete your own account.';
    header('Location: ../employees.php');
    exit;
}

// Get user name
$userQuery = "SELECT name FROM $table WHERE id = ?";
$stmt = mysqli_prepare($conn, $userQuery);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$userName = 'Unknown';
if ($result && mysqli_num_rows($result) > 0) {
    $userData = mysqli_fetch_assoc($result);
    $userName = $userData['name'];
}
mysqli_stmt_close($stmt);

// Delete
$query = "DELETE FROM $table WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    $_SESSION['error'] = 'Database error: ' . mysqli_error($conn);
    header('Location: ../employees.php');
    exit;
}

mysqli_stmt_bind_param($stmt, 'i', $id);
$result = mysqli_stmt_execute($stmt);

if ($result) {
    if (file_exists(__DIR__ . '/audit-logger.php')) {
        logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'user_deleted', 'Deleted user: ' . $userName);
    }
    $_SESSION['success'] = ucfirst($type) . ' deleted successfully!';
} else {
    if (file_exists(__DIR__ . '/audit-logger.php')) {
        logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'user_error', 'Failed to delete user');
    }
    $_SESSION['error'] = 'Failed to delete ' . $type;
}

mysqli_stmt_close($stmt);
header('Location: ../employees.php');
exit;
?>