<?php
require_once '../../config/db_conn.php';
require_once __DIR__ . '/audit-logger.php'; // Add audit logger
session_start();

function fetchTransaction($conn, $id)
{
    $stmt = $conn->prepare("SELECT * FROM supply_transactions WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getCurrentStock($conn, $productId)
{
    $stmt = $conn->prepare("SELECT stock_quantity FROM inventory WHERE product_id = ?");
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result ? $result['stock_quantity'] : 0;
}

function updateInventory($conn, $productId, $adjustment)
{
    $currentStock = getCurrentStock($conn, $productId);
    $newStock = $currentStock + $adjustment;
    $finalStock = max(0, $newStock);

    $stmt = $conn->prepare("UPDATE inventory SET stock_quantity = ? WHERE product_id = ?");
    $stmt->bind_param('ii', $finalStock, $productId);
    return $stmt->execute();
}

function deleteTransaction($conn, $id)
{
    $stmt = $conn->prepare("DELETE FROM supply_transactions WHERE id = ?");
    $stmt->bind_param('i', $id);
    return $stmt->execute();
}

// NEW FUNCTION: Get product name for audit logging
function getProductName($conn, $productId)
{
    $stmt = $conn->prepare("SELECT name FROM supply_products WHERE id = ?");
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result ? $result['name'] : 'Unknown Product';
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $conn->autocommit(false);

    try {
        $transaction = fetchTransaction($conn, $id);

        if (!$transaction) {
            throw new Exception('Transaction not found.');
        }

        $adjustment = $transaction['type'] === 'IN'
            ? -$transaction['quantity']
            : $transaction['quantity'];

        if (!updateInventory($conn, $transaction['product_id'], $adjustment)) {
            throw new Exception('Failed to update inventory.');
        }

        if (!deleteTransaction($conn, $id)) {
            throw new Exception('Failed to delete transaction.');
        }

        // AUDIT LOGGING - Log transaction deletion
        if (file_exists(__DIR__ . '/audit-logger.php')) {
            $productName = getProductName($conn, $transaction['product_id']);
            $description = 'Deleted transaction' . ': ' . $transaction['quantity'] . ' ' .
                $transaction['type'] . ' for ' . $productName;
            logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'delete_transaction', $description);
        }

        $conn->commit();
        $newStock = getCurrentStock($conn, $transaction['product_id']);
        if ($newStock === 0 && $transaction['type'] === 'IN') {
            $_SESSION['success'] = 'Transaction deleted. Inventory was adjusted to zero to prevent negative stock.';
        } else {
            $_SESSION['success'] = 'Transaction deleted successfully.';
        }
    } catch (Exception $e) {
        $conn->rollback();

        // AUDIT LOGGING - Log error
        if (file_exists(__DIR__ . '/audit-logger.php')) {
            $errorDescription = 'Failed to delete transaction #' . $id . ': ' . $e->getMessage();
            logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'transaction_error', $errorDescription);
        }

        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }

    header('Location: ../inventory.php');
    exit;
}
