<?php
require_once '../../config/db_conn.php';
session_start();

function cancelPrelistOrder($conn, $id)
{
    // Begin transaction
    $conn->autocommit(false);

    try {
        // Check the order status and get customer_id and deducted_balance
        $statusQuery = $conn->prepare("SELECT status, customer_id, deducted_balance FROM prelist_orders WHERE id = ?");
        $statusQuery->bind_param('i', $id);
        $statusQuery->execute();
        $statusResult = $statusQuery->get_result();

        if ($statusResult->num_rows === 0) {
            throw new Exception('Order not found or you do not have permission to delete it.');
        }

        $orderData = $statusResult->fetch_assoc();
        $status = $orderData['status'];
        $customerId = $orderData['customer_id'];
        $deductedBalance = $orderData['deducted_balance'] ?? 0;

        if ($status !== 'Pre-listed') {
            throw new Exception('Only Pre-listed orders can be deleted.');
        }

        // Delete the associated receipt record first
        $deleteReceiptQuery = $conn->prepare("DELETE FROM prelist_receipts WHERE prelist_order_id = ?");
        $deleteReceiptQuery->bind_param('i', $id);
        if (!$deleteReceiptQuery->execute()) {
            throw new Exception('Failed to delete receipt: ' . $conn->error);
        }
        $deleteReceiptQuery->close();

        // Fetch prelist details for the order including product IDs
        $detailsQuery = $conn->prepare("
            SELECT rounds_of_wash, scoops_of_detergent, fabcon_cups, bleach_cups, 
                   detergent_product_id, fabcon_product_id, bleach_product_id 
            FROM prelist_details 
            WHERE prelist_order_id = ?
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
        $bleachCups = $details['bleach_cups'] ?? 0;

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
        if ($bleachProductId && $bleachCups > 0) {
            $updateInventoryQuery->bind_param('ii', $bleachCups, $bleachProductId);
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

        // Delete from prelist_details
        $stmt = $conn->prepare("DELETE FROM prelist_details WHERE prelist_order_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Delete from prelist_items
        $stmt = $conn->prepare("DELETE FROM prelist_items WHERE prelist_order_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Delete from prelist_orders
        $stmt = $conn->prepare("DELETE FROM prelist_orders WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Commit transaction
        $conn->commit();

        $_SESSION['success'] = 'Order deleted successfully.' .
            ($deductedBalance > 0 ? ' Deducted balance has been returned to the customer\'s account.' : '');
        header('Location: ../prelisted-orders.php');
        exit;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error'] = 'Error deleting order: ' . $e->getMessage();
        header('Location: ../prelisted-orders.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    if ($id) {
        cancelPrelistOrder($conn, $id);
    } else {
        $_SESSION['error'] = 'Invalid order ID.';
        header('Location: ../prelisted-orders.php');
        exit;
    }
}
