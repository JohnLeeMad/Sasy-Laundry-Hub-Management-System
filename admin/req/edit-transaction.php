<?php
require_once '../../config/db_conn.php';
require_once __DIR__ . '/audit-logger.php'; // Add audit logger
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $quantity = $_POST['quantity'] ?? null;
    $type = $_POST['type'] ?? null;

    if (!validateEditTransactionInput($id, $quantity, $type)) {
        header('Location: ../inventory.php');
        exit;
    }

    $conn->autocommit(false);

    try {
        $originalTransaction = fetchOriginalTransaction($conn, $id);
        if (!$originalTransaction) {
            throw new Exception('Transaction not found.');
        }

        revertOriginalTransaction($conn, $originalTransaction);

        if ($type === 'OUT' || $type === 'Used') {
            if (!validateStockAvailability($conn, $originalTransaction['product_id'], $quantity)) {
                throw new Exception('Insufficient stock for this transaction.');
            }
        }

        applyNewTransaction($conn, $originalTransaction['product_id'], $quantity, $type);
        updateTransaction($conn, $id, $quantity, $type);

        // AUDIT LOGGING - Log transaction edit
        if (file_exists(__DIR__ . '/audit-logger.php')) {
            $productName = getProductName($conn, $originalTransaction['product_id']);
            $description = 'Edited transaction' . ': Changed from ' .
                $originalTransaction['quantity'] . ' ' . $originalTransaction['type'] .
                ' to ' . $quantity . ' ' . $type . ' for ' . $productName;
            logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'edit_transaction', $description);
        }

        $conn->commit();
        $_SESSION['success'] = 'Transaction updated successfully.';
    } catch (Exception $e) {
        $conn->rollback();

        // AUDIT LOGGING - Log error
        if (file_exists(__DIR__ . '/audit-logger.php')) {
            $errorDescription = 'Failed to edit transaction #' . $id . ': ' . $e->getMessage();
            logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'transaction_error', $errorDescription);
        }

        $_SESSION['error'] = 'Error updating transaction: ' . $e->getMessage();
    }

    header('Location: ../inventory.php');
    exit;
}

function validateEditTransactionInput($id, $quantity, $type)
{
    if (!$id || !$quantity || $quantity < 1 || !$type) {
        $_SESSION['error'] = 'All fields are required.';
        return false;
    }
    return true;
}

function fetchOriginalTransaction($conn, $id)
{
    $stmt = $conn->prepare("SELECT * FROM supply_transactions WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function revertOriginalTransaction($conn, $originalTransaction)
{
    $adjustment = $originalTransaction['type'] === 'IN' ? -$originalTransaction['quantity'] : $originalTransaction['quantity'];
    $stmt = $conn->prepare("UPDATE inventory SET stock_quantity = stock_quantity + ? WHERE product_id = ?");
    $stmt->bind_param('ii', $adjustment, $originalTransaction['product_id']);
    if (!$stmt->execute()) {
        throw new Exception('Failed to revert original transaction.');
    }
}

function validateStockAvailability($conn, $productId, $quantity)
{
    $stmt = $conn->prepare("SELECT stock_quantity FROM inventory WHERE product_id = ?");
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result && $result['stock_quantity'] >= $quantity;
}

function applyNewTransaction($conn, $productId, $quantity, $type)
{
    // Get current stock first
    $stmt = $conn->prepare("SELECT stock_quantity FROM inventory WHERE product_id = ?");
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $currentStock = $result ? $result['stock_quantity'] : 0;

    // Calculate new stock
    $adjustment = $type === 'IN' ? $quantity : -$quantity;
    $newStock = $currentStock + $adjustment;
    $finalStock = max(0, $newStock); // Prevent negative stock

    // Update with final stock
    $stmt = $conn->prepare("UPDATE inventory SET stock_quantity = ? WHERE product_id = ?");
    $stmt->bind_param('ii', $finalStock, $productId);
    if (!$stmt->execute()) {
        throw new Exception('Failed to apply new transaction.');
    }

    // Add warning message if stock was adjusted to zero
    if ($finalStock === 0 && $newStock < 0) {
        $_SESSION['warning'] = 'Stock quantity was adjusted to zero to prevent negative stock.';
    }
}

function updateTransaction($conn, $id, $quantity, $type)
{
    $stmt = $conn->prepare("UPDATE supply_transactions SET quantity = ?, type = ? WHERE id = ?");
    $stmt->bind_param('isi', $quantity, $type, $id);
    if (!$stmt->execute()) {
        throw new Exception('Failed to update transaction.');
    }
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
