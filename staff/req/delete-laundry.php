<?php
session_start();
require_once '../../config/db_conn.php';
require_once 'audit-logger.php'; // Add audit logger

function deleteLaundryRecord($conn, $id)
{
    // Begin transaction
    $conn->autocommit(false);

    try {
        // Check the order status and get customer_id and deducted_balance
        $statusQuery = $conn->prepare("SELECT status, customer_id, deducted_balance FROM laundry_lists WHERE id = ?");
        $statusQuery->bind_param('i', $id);
        $statusQuery->execute();
        $statusResult = $statusQuery->get_result();

        if ($statusResult->num_rows === 0) {
            throw new Exception('Order not found.');
        }

        $orderData = $statusResult->fetch_assoc();
        $status = $orderData['status'];
        $customerId = $orderData['customer_id'];
        $deductedBalance = $orderData['deducted_balance'] ?? 0;

        // Fetch customer name for audit logging
        $customerName = 'Unknown';
        if ($customerId) {
            $customerQuery = $conn->prepare("SELECT name FROM users WHERE id = ?");
            $customerQuery->bind_param('i', $customerId);
            $customerQuery->execute();
            $customerResult = $customerQuery->get_result();
            if ($customerResult && $customerResult->num_rows > 0) {
                $customerRow = $customerResult->fetch_assoc();
                $customerName = $customerRow['name'];
            }
            $customerQuery->close();
        }

        if (in_array($status, ['Ongoing', 'Ready for Pickup', 'Claimed', 'Unclaimed'])) {
            throw new Exception('Cannot delete an order that is Ongoing, Ready for Pickup, or Claimed.');
        }

        // Delete the associated receipt record first
        $deleteReceiptQuery = $conn->prepare("DELETE FROM receipts WHERE laundry_list_id = ?");
        $deleteReceiptQuery->bind_param('i', $id);
        if (!$deleteReceiptQuery->execute()) {
            throw new Exception('Failed to delete receipt: ' . $conn->error);
        }
        $deleteReceiptQuery->close();

        // Fetch laundry details for the order including product IDs
        $detailsQuery = $conn->prepare("
            SELECT rounds_of_wash, scoops_of_detergent, fabcon_cups, bleach_cups, 
                   detergent_product_id, fabcon_product_id, bleach_product_id 
            FROM laundry_details 
            WHERE laundry_list_id = ?
        ");
        $detailsQuery->bind_param('i', $id);
        $detailsQuery->execute();
        $detailsResult = $detailsQuery->get_result();

        if ($detailsResult->num_rows === 0) {
            throw new Exception('Order details not found.');
        }

        $details = $detailsResult->fetch_assoc();

        // Ensure values are not null
        $scoopsOfDetergent = $details['scoops_of_detergent'] ?? 0;
        $fabconCups = $details['fabcon_cups'] ?? 0;
        $zonroxCups = $details['bleach_cups'] ?? 0;

        // Get product IDs
        $detergentProductId = $details['detergent_product_id'];
        $fabconProductId = $details['fabcon_product_id'];
        $bleachProductId = $details['bleach_product_id'];

        // Prepare inventory update query
        $updateInventoryQuery = $conn->prepare("
            UPDATE inventory
            SET available_units = available_units + ?
            WHERE product_id = ?
        ");

        // Revert detergent stock if product was used
        if ($detergentProductId && $scoopsOfDetergent > 0) {
            $updateInventoryQuery->bind_param('ii', $scoopsOfDetergent, $detergentProductId);
            $updateInventoryQuery->execute();
        }

        // Revert fabric conditioner stock if product was used
        if ($fabconProductId && $fabconCups > 0) {
            $updateInventoryQuery->bind_param('ii', $fabconCups, $fabconProductId);
            $updateInventoryQuery->execute();
        }

        // Revert bleach stock if product was used
        if ($bleachProductId && $zonroxCups > 0) {
            $updateInventoryQuery->bind_param('ii', $zonroxCups, $bleachProductId);
            $updateInventoryQuery->execute();
        }

        // Return deducted balance to customer if there was any
        if ($deductedBalance > 0 && $customerId) {
            $updateBalanceQuery = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            $updateBalanceQuery->bind_param('di', $deductedBalance, $customerId);
            if (!$updateBalanceQuery->execute()) {
                throw new Exception('Failed to return balance to customer: ' . $conn->error);
            }
            $updateBalanceQuery->close();
        }

        // Delete from laundry_details
        $stmt = $conn->prepare("DELETE FROM laundry_details WHERE laundry_list_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Delete from laundry_lists
        $stmt = $conn->prepare("DELETE FROM laundry_lists WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Commit transaction
        $conn->commit();

        // AUDIT LOGGING - Log order deletion activity
        if (file_exists('audit-logger.php')) {
            $refundText = $deductedBalance > 0 ? ' (₱' . number_format($deductedBalance, 2) . ' refunded)' : '';
            $description = 'Deleted laundry order for customer: ' . $customerName .
                ' and reverted inventory' . $refundText;
            logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'delete_order', $description);
        }

        $_SESSION['success'] = 'Laundry record deleted and inventory reverted successfully.' .
            ($deductedBalance > 0 ? ' Deducted balance has been returned to customer.' : '');
        header('Location: ../laundry-list.php');
        exit;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();

        // AUDIT LOGGING - Log deletion error
        if (file_exists('audit-logger.php')) {
            $errorDescription = 'Failed to delete laundry order for customer: ' . ($customerName ?? 'Unknown') .
                ' - Error: ' . $e->getMessage();
            logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'order_delete_error', $errorDescription);
        }

        $_SESSION['error'] = 'Error deleting laundry record: ' . $e->getMessage();
        header('Location: ../laundry-list.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    deleteLaundryRecord($conn, $id);
}
