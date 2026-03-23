<?php
// req/cancel-laundry.php
session_start();
require_once '../../config/db_conn.php';
require_once 'audit-logger.php';

function cancelLaundryOrder($conn, $data)
{
    // Begin transaction
    $conn->autocommit(false);

    try {
        $laundryId = $data['laundry_id'];
        $customerId = $data['customer_id'];
        $deductedBalance = floatval($data['deducted_balance']);
        $cancellationReason = $data['cancellation_reason'];

        // Combine notes if "Other" reason is selected
        $cancellationNotes = '';
        if ($cancellationReason === 'Other' && !empty($data['cancellation_notes'])) {
            $cancellationNotes = $data['cancellation_notes'];
        } elseif (!empty($data['general_cancellation_notes'])) {
            $cancellationNotes = $data['general_cancellation_notes'];
        }

        // If both exist, combine them
        if ($cancellationReason === 'Other' && !empty($data['cancellation_notes']) && !empty($data['general_cancellation_notes'])) {
            $cancellationNotes = $data['cancellation_notes'] . ' | Additional: ' . $data['general_cancellation_notes'];
        }

        // Get current user info
        $cancelledById = $_SESSION['user_id'];
        $cancelledByType = $_SESSION['user_role']; // 'admin' or 'staff'
        $cancelledByName = $_SESSION['user_name'];

        // Get order details
        $orderQuery = $conn->prepare("
            SELECT ll.status, ll.customer_id, ll.queue_number, u.name as customer_name,
                   ld.rounds_of_wash, ld.scoops_of_detergent, ld.fabcon_cups, ld.bleach_cups,
                   ld.detergent_product_id, ld.fabcon_product_id, ld.bleach_product_id
            FROM laundry_lists ll
            LEFT JOIN users u ON ll.customer_id = u.id
            LEFT JOIN laundry_details ld ON ll.id = ld.laundry_list_id
            WHERE ll.id = ?
        ");
        $orderQuery->bind_param('i', $laundryId);
        $orderQuery->execute();
        $orderResult = $orderQuery->get_result();

        if ($orderResult->num_rows === 0) {
            throw new Exception('Order not found.');
        }

        $orderData = $orderResult->fetch_assoc();
        $status = $orderData['status'];
        $customerName = $orderData['customer_name'] ?? 'Unknown';
        $queueNumber = $orderData['queue_number'];

        // Check if order can be cancelled - ONLY Pending orders can be cancelled
        if ($status !== 'Pending') {
            throw new Exception('Only orders with "Pending" status can be cancelled.');
        }

        // Revert inventory for supplies used
        $scoopsOfDetergent = $orderData['scoops_of_detergent'] ?? 0;
        $fabconCups = $orderData['fabcon_cups'] ?? 0;
        $bleachCups = $orderData['bleach_cups'] ?? 0;
        $detergentProductId = $orderData['detergent_product_id'];
        $fabconProductId = $orderData['fabcon_product_id'];
        $bleachProductId = $orderData['bleach_product_id'];

        // Update inventory
        $updateInventoryQuery = $conn->prepare("
            UPDATE inventory
            SET available_units = available_units + ?
            WHERE product_id = ?
        ");

        if ($detergentProductId && $scoopsOfDetergent > 0) {
            $updateInventoryQuery->bind_param('ii', $scoopsOfDetergent, $detergentProductId);
            $updateInventoryQuery->execute();
        }

        if ($fabconProductId && $fabconCups > 0) {
            $updateInventoryQuery->bind_param('ii', $fabconCups, $fabconProductId);
            $updateInventoryQuery->execute();
        }

        if ($bleachProductId && $bleachCups > 0) {
            $updateInventoryQuery->bind_param('ii', $bleachCups, $bleachProductId);
            $updateInventoryQuery->execute();
        }

        // Return deducted balance to customer if any
        if ($deductedBalance > 0 && $customerId) {
            $updateBalanceQuery = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            $updateBalanceQuery->bind_param('di', $deductedBalance, $customerId);
            if (!$updateBalanceQuery->execute()) {
                throw new Exception('Failed to return balance to customer: ' . $conn->error);
            }
            $updateBalanceQuery->close();
        }

        // Update laundry_lists with cancellation details
        $cancelQuery = $conn->prepare("
            UPDATE laundry_lists 
            SET status = 'Cancelled',
                cancellation_reason = ?,
                cancellation_notes = ?,
                cancelled_at = NOW(),
                cancelled_by_id = ?,
                cancelled_by_type = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $cancelQuery->bind_param('ssisi', $cancellationReason, $cancellationNotes, $cancelledById, $cancelledByType, $laundryId);

        if (!$cancelQuery->execute()) {
            throw new Exception('Failed to cancel order: ' . $conn->error);
        }
        $cancelQuery->close();

        // Commit transaction
        $conn->commit();

        // Audit logging
        if (file_exists('audit-logger.php')) {
            $refundText = $deductedBalance > 0 ? ' (₱' . number_format($deductedBalance, 2) . ' refunded)' : '';
            $description = 'Cancelled laundry order #' . $queueNumber . ' for customer: ' . $customerName .
                ' - Reason: ' . $cancellationReason . $refundText;
            logActivity($cancelledById, $cancelledByType, $cancelledByName, 'cancel_order', $description);
        }

        $_SESSION['success'] = 'Order #' . $queueNumber . ' has been cancelled successfully.' .
            ($deductedBalance > 0 ? ' Deducted balance has been returned to customer.' : '');
        header('Location: ../laundry-list.php?status_filter=Cancelled');
        exit;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();

        // Audit logging for error
        if (file_exists('audit-logger.php')) {
            $errorDescription = 'Failed to cancel laundry order - Error: ' . $e->getMessage();
            logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'order_cancel_error', $errorDescription);
        }

        $_SESSION['error'] = 'Error cancelling order: ' . $e->getMessage();
        header('Location: ../laundry-list.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cancelLaundryOrder($conn, $_POST);
} else {
    $_SESSION['error'] = 'Invalid request method.';
    header('Location: ../laundry-list.php');
    exit;
}
