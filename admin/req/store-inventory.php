<?php
require_once '../../config/db_conn.php';
require_once __DIR__ . '/audit-logger.php'; // Add audit logger
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'] ?? null;
    $quantity = $_POST['quantity'] ?? null;
    $type = $_POST['type'] ?? null;
    $description = $_POST['description'] ?? ''; // Get description from form

    if (!validateInventoryInput($productId, $quantity, $type)) {
        header('Location: ../inventory.php');
        exit;
    }

    $conn->autocommit(false);

    try {
        if ($type === 'OUT' || $type === 'Used') {
            if (!validateStockAvailability($conn, $productId, $quantity)) {
                throw new Exception('Insufficient stock for this transaction.');
            }
        }

        updateInventory($conn, $productId, $quantity, $type);
        logTransaction($conn, $productId, $quantity, $type, $description);

        // AUDIT LOGGING - Log inventory transaction
        if (file_exists(__DIR__ . '/audit-logger.php')) {
            $productName = getProductName($conn, $productId);
            $auditDescription = $type . ' transaction: ' . $quantity . ' stock(s) of ' . $productName;

            // Add user description to audit log if provided
            if (!empty($description)) {
                $auditDescription .= ' - ' . $description;
            }

            logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'inventory_transaction', $auditDescription);
        }

        $conn->commit();
        $_SESSION['success'] = 'Product transaction saved successfully.';
    } catch (Exception $e) {
        $conn->rollback();

        // AUDIT LOGGING - Log error
        if (file_exists(__DIR__ . '/audit-logger.php')) {
            $errorDescription = 'Failed inventory transaction: ' . $e->getMessage();
            logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'inventory_error', $errorDescription);
        }

        $_SESSION['error'] = 'Error saving transaction: ' . $e->getMessage();
    }

    header('Location: ../inventory.php');
    exit;
}

function validateInventoryInput($productId, $quantity, $type)
{
    if (!$productId || !$quantity || $quantity < 1 || !$type) {
        $_SESSION['error'] = 'All fields are required.';
        return false;
    }
    return true;
}

function validateStockAvailability($conn, $productId, $quantity)
{
    $stmt = $conn->prepare("SELECT stock_quantity FROM inventory WHERE product_id = ?");
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result && $result['stock_quantity'] >= $quantity;
}

function updateInventory($conn, $productId, $quantity, $type)
{
    if ($type === 'IN') {
        $stmt = $conn->prepare("UPDATE inventory SET stock_quantity = stock_quantity + ? WHERE product_id = ?");
    } elseif ($type === 'OUT' || $type === 'Used') {
        $stmt = $conn->prepare("UPDATE inventory SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
    } else {
        throw new Exception('Invalid transaction type.');
    }
    $stmt->bind_param('ii', $quantity, $productId);
    if (!$stmt->execute()) {
        throw new Exception('Failed to update inventory.');
    }
}

function logTransaction($conn, $productId, $quantity, $type, $description = '')
{
    $stmt = $conn->prepare("INSERT INTO supply_transactions (product_id, quantity, type, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('iiss', $productId, $quantity, $type, $description);
    if (!$stmt->execute()) {
        throw new Exception('Failed to log transaction.');
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
