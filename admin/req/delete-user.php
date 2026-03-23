<?php
require_once '../../config/db_conn.php';
require_once __DIR__ . '/audit-logger.php'; // Add audit logger
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    try {
        // Get user details for audit log before deletion
        $userQuery = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
        $userQuery->bind_param('i', $id);
        $userQuery->execute();
        $userResult = $userQuery->get_result();

        if ($userResult->num_rows === 0) {
            $_SESSION['error'] = 'User not found.';
            header('Location: ../customers.php');
            exit;
        }

        $userData = $userResult->fetch_assoc();
        $userName = $userData ? $userData['name'] : 'Unknown';
        $userEmail = $userData ? $userData['email'] : 'Unknown';

        // Check if user has existing orders
        $orderCheck = $conn->prepare("
            SELECT COUNT(*) as order_count 
            FROM laundry_lists 
            WHERE customer_id = ?
        ");
        $orderCheck->bind_param('i', $id);
        $orderCheck->execute();
        $orderResult = $orderCheck->get_result();
        $orderData = $orderResult->fetch_assoc();
        $orderCount = $orderData['order_count'];

        if ($orderCount > 0) {
            // AUDIT LOGGING - Log deletion attempt with existing orders
            if (file_exists(__DIR__ . '/audit-logger.php')) {
                $auditDescription = 'Attempted to delete customer with existing orders: ' . $userName . ' (' . $userEmail . ') - ' . $orderCount . ' orders found';
                logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'customer_deletion_blocked', $auditDescription);
            }

            $_SESSION['error'] = 'Cannot delete customer "' . $userName . '" because they have ' . $orderCount . ' existing order(s) in the system. Please archive the customer instead or delete their orders first.';
            header('Location: ../customers.php');
            exit;
        }

        // Check if user has reviews
        $reviewCheck = $conn->prepare("
            SELECT COUNT(*) as review_count 
            FROM reviews 
            WHERE customer_id = ?
        ");
        $reviewCheck->bind_param('i', $id);
        $reviewCheck->execute();
        $reviewResult = $reviewCheck->get_result();
        $reviewData = $reviewResult->fetch_assoc();
        $reviewCount = $reviewData['review_count'];

        if ($reviewCount > 0) {
            // AUDIT LOGGING - Log deletion attempt with existing reviews
            if (file_exists(__DIR__ . '/audit-logger.php')) {
                $auditDescription = 'Attempted to delete customer with existing reviews: ' . $userName . ' (' . $userEmail . ') - ' . $reviewCount . ' reviews found';
                logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'customer_deletion_blocked', $auditDescription);
            }

            $_SESSION['error'] = 'Cannot delete customer "' . $userName . '" because they have ' . $reviewCount . ' review(s) in the system. Please delete their reviews first.';
            header('Location: ../customers.php');
            exit;
        }

        // If no orders or reviews, proceed with deletion
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // AUDIT LOGGING - Log successful user deletion
        if (file_exists(__DIR__ . '/audit-logger.php')) {
            $auditDescription = 'Deleted customer: ' . $userName . ' (' . $userEmail . ')';
            logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'customer_deleted', $auditDescription);
        }

        $_SESSION['success'] = 'Customer "' . $userName . '" deleted successfully.';
        header('Location: ../customers.php');
        exit;
    } catch (Exception $e) {
        // Check if it's a foreign key constraint violation
        if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
            // AUDIT LOGGING - Log foreign key constraint error
            if (file_exists(__DIR__ . '/audit-logger.php')) {
                $errorDescription = 'Foreign key constraint violation when deleting customer: ' . $userName . ' (' . $userEmail . ') - Customer has related records in other tables';
                logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'customer_deletion_constraint', $errorDescription);
            }

            $_SESSION['error'] = 'Cannot delete customer "' . $userName . '" because they have existing orders, reviews, or other related records in the system. Please delete all associated records first or contact system administrator.';
        } else {
            // AUDIT LOGGING - Log other errors
            if (file_exists(__DIR__ . '/audit-logger.php')) {
                $errorDescription = 'Failed to delete customer: ' . $e->getMessage();
                logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'customer_error', $errorDescription);
            }

            $_SESSION['error'] = 'Error deleting customer: ' . $e->getMessage();
        }

        header('Location: ../customers.php');
        exit;
    }
}
