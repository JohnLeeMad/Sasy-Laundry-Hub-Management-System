<?php
session_start();
require_once '../../config/db_conn.php';
require_once __DIR__ . '/audit-logger.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method.';
    header('Location: ../employees.php');
    exit;
}

error_log("Archive POST data: " . print_r($_POST, true));

$id = intval($_POST['id'] ?? 0);
$role = trim($_POST['role'] ?? '');
$action = trim($_POST['action'] ?? '');

if (!$id || !$role || !$action) {
    $_SESSION['error'] = 'Missing required information. Got: id=' . $id . ', role=' . $role . ', action=' . $action;
    header('Location: ../employees.php');
    exit;
}

if (!in_array($action, ['archive', 'restore'])) {
    $_SESSION['error'] = 'Invalid action.';
    header('Location: ../employees.php');
    exit;
}

$table = ($role === 'Admin') ? 'admins' : 'staffs';

$currentUserId = $_SESSION['user_id'] ?? null;
$currentRole = $_SESSION['user_role'] ?? null;
$currentRoleCapitalized = ucfirst($currentRole);

if ($action === 'archive' && $currentUserId && $id == $currentUserId && $role == $currentRoleCapitalized) {
    $_SESSION['error'] = 'You cannot archive your own account.';
    header('Location: ../employees.php');
    exit;
}

$nameQuery = "SELECT name FROM $table WHERE id = ?";
$stmt = mysqli_prepare($conn, $nameQuery);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$employee = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$employee) {
    $_SESSION['error'] = 'Employee not found.';
    header('Location: ../employees.php');
    exit;
}
$archiveStatus = ($action === 'archive') ? 1 : 0;
$updateQuery = "UPDATE $table SET archived = ?, updated_at = NOW() WHERE id = ?";
$stmt = mysqli_prepare($conn, $updateQuery);
mysqli_stmt_bind_param($stmt, 'ii', $archiveStatus, $id);

if (mysqli_stmt_execute($stmt)) {
    $actionText = ($action === 'archive') ? 'archived' : 'restored';
    
    if (file_exists(__DIR__ . '/audit-logger.php')) {
        logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 
                    'user_' . $actionText, 
                    ucfirst($actionText) . " user: {$employee['name']}");
    }
    
    $_SESSION['success'] = "User '{$employee['name']}' has been {$actionText} successfully.";
} else {
    $_SESSION['error'] = 'Failed to ' . $action . ' user.';
}

mysqli_stmt_close($stmt);
header('Location: ../employees.php');
exit;
?>