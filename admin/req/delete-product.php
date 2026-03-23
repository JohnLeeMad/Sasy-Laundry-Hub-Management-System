<?php
session_start();
require_once '../../config/db_conn.php';
require_once __DIR__ . '/audit-logger.php'; // Add audit logger

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);

    try {
        // Get product name for audit logging
        $productName = 'Unknown Product';
        $getProductStmt = $conn->prepare("SELECT name FROM supply_products WHERE id = ?");
        $getProductStmt->bind_param("i", $id);
        $getProductStmt->execute();
        $productResult = $getProductStmt->get_result();
        if ($productResult->num_rows > 0) {
            $productRow = $productResult->fetch_assoc();
            $productName = $productRow['name'];
        }
        $getProductStmt->close();

        // Check if product is being used in inventory (stock exists)
        $checkInventoryStmt = $conn->prepare("SELECT COUNT(*) as count FROM inventory WHERE product_id = ? AND (stock_quantity > 0 OR available_units > 0)");
        $checkInventoryStmt->bind_param("i", $id);
        $checkInventoryStmt->execute();
        $inventoryResult = $checkInventoryStmt->get_result();
        $inventoryRow = $inventoryResult->fetch_assoc();
        $checkInventoryStmt->close();

        if ($inventoryRow['count'] > 0) {
            $_SESSION['error'] = 'Cannot delete this product because it still has stock in inventory.';

            // AUDIT LOGGING - Log deletion attempt with stock
            if (file_exists(__DIR__ . '/audit-logger.php')) {
                $errorDescription = 'Attempted to delete product "' . $productName . '" but it still has stock in inventory';
                logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'delete_product_error', $errorDescription);
            }
        } else {
            // Check if product is being used in laundry_details
            $checkLaundryStmt = $conn->prepare("SELECT COUNT(*) as count FROM laundry_details WHERE detergent_product_id = ? OR fabcon_product_id = ? OR bleach_product_id = ?");
            $checkLaundryStmt->bind_param("iii", $id, $id, $id);
            $checkLaundryStmt->execute();
            $laundryResult = $checkLaundryStmt->get_result();
            $laundryRow = $laundryResult->fetch_assoc();
            $checkLaundryStmt->close();

            // Check if product is being used in prelist_details
            $checkPrelistStmt = $conn->prepare("SELECT COUNT(*) as count FROM prelist_details WHERE detergent_product_id = ? OR fabcon_product_id = ? OR bleach_product_id = ?");
            $checkPrelistStmt->bind_param("iii", $id, $id, $id);
            $checkPrelistStmt->execute();
            $prelistResult = $checkPrelistStmt->get_result();
            $prelistRow = $prelistResult->fetch_assoc();
            $checkPrelistStmt->close();

            if ($laundryRow['count'] > 0 || $prelistRow['count'] > 0) {
                $_SESSION['error'] = 'Cannot delete this product because it is being used in laundry orders.';

                // AUDIT LOGGING - Log deletion attempt with active orders
                if (file_exists(__DIR__ . '/audit-logger.php')) {
                    $errorDescription = 'Attempted to delete product "' . $productName . '" but it is being used in laundry orders';
                    logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'delete_product_error', $errorDescription);
                }
            } else {
                // Safe to delete - begin transaction
                $conn->begin_transaction();

                try {
                    // Delete from supply_transactions first (foreign key constraint)
                    $deleteTransactionsStmt = $conn->prepare("DELETE FROM supply_transactions WHERE product_id = ?");
                    $deleteTransactionsStmt->bind_param("i", $id);
                    $deleteTransactionsStmt->execute();
                    $deleteTransactionsStmt->close();

                    // Delete from inventory
                    $deleteInventoryStmt = $conn->prepare("DELETE FROM inventory WHERE product_id = ?");
                    $deleteInventoryStmt->bind_param("i", $id);
                    $deleteInventoryStmt->execute();
                    $deleteInventoryStmt->close();

                    // Delete from supply_products
                    $deleteProductStmt = $conn->prepare("DELETE FROM supply_products WHERE id = ?");
                    $deleteProductStmt->bind_param("i", $id);
                    $deleteProductStmt->execute();
                    $deleteProductStmt->close();

                    // Commit transaction
                    $conn->commit();
                    $_SESSION['success'] = 'Supply product deleted successfully!';

                    // AUDIT LOGGING - Log successful deletion
                    if (file_exists(__DIR__ . '/audit-logger.php')) {
                        $description = 'Deleted supply product: "' . $productName . '"';
                        logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'delete_product', $description);
                    }
                } catch (Exception $e) {
                    // Rollback transaction on error
                    $conn->rollback();
                    $_SESSION['error'] = 'Failed to delete supply product: ' . $e->getMessage();

                    // AUDIT LOGGING - Log deletion error
                    if (file_exists(__DIR__ . '/audit-logger.php')) {
                        $errorDescription = 'Failed to delete product "' . $productName . '": ' . $e->getMessage();
                        logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'delete_product_error', $errorDescription);
                    }
                }
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();

        // AUDIT LOGGING - Log general database error
        if (file_exists(__DIR__ . '/audit-logger.php')) {
            $errorDescription = 'Database error while attempting to delete product: ' . $e->getMessage();
            logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'delete_product_error', $errorDescription);
        }
    }
} else {
    $_SESSION['error'] = 'Invalid product ID.';

    // AUDIT LOGGING - Log invalid product ID attempt
    if (file_exists(__DIR__ . '/audit-logger.php')) {
        $errorDescription = 'Attempted to delete product with invalid ID';
        logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'delete_product_error', $errorDescription);
    }
}

header('Location: ../supply-list.php');
exit;
