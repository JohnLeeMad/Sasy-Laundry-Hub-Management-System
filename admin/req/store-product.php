<?php
session_start();
require_once '../../config/db_conn.php';
require_once 'audit-logger.php'; // Add audit logger

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $categoryId = $_POST['category_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $measurement = $_POST['measurement'] ?? null; // Default measurement if not provided
    $price = $_POST['price'] ?? '';
    $maxUnit = $_POST['max_unit_per_container'] ?? '';
    $description = $_POST['description'] ?? null;

    // Convert empty strings to null
    $measurement = ($measurement === '' ? null : $measurement);
    $description = ($description === '' ? null : $description);

    // Validation
    if (empty($categoryId) || empty($name) || empty($price) || empty($maxUnit)) {
        $_SESSION['error'] = 'Please fill in all required fields.';
        header('Location: ../supply-list.php');
        exit;
    }

    if (!is_numeric($price) || $price < 0) {
        $_SESSION['error'] = 'Please enter a valid price.';
        header('Location: ../supply-list.php');
        exit;
    }

    if (!is_numeric($maxUnit) || $maxUnit < 1) {
        $_SESSION['error'] = 'Please enter a valid max unit per container.';
        header('Location: ../supply-list.php');
        exit;
    }

    try {
        if (empty($id)) {
            // Check category limit before inserting new product
            $checkStmt = $conn->prepare("SELECT COUNT(*) as product_count FROM supply_products WHERE category_id = ?");
            $checkStmt->bind_param("i", $categoryId);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            $row = $result->fetch_assoc();
            $currentCount = $row['product_count'];
            $checkStmt->close();

            // Check if category already has 2 products
            if ($currentCount >= 2) {
                $_SESSION['error'] = 'This category already has the maximum number of products (2). Please choose a different category or remove an existing product first.';
                header('Location: ../supply-list.php');
                exit;
            }

            // Insert new product
            $stmt = $conn->prepare("INSERT INTO supply_products (category_id, name, measurement, price, max_unit_per_container, description) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssis", $categoryId, $name, $measurement, $price, $maxUnit, $description);

            if ($stmt->execute()) {
                // AUDIT LOGGING - Log product creation
                if (file_exists('audit-logger.php')) {
                    $description = 'Created new supply product: ' . $name . ' (measurement: ' . ($measurement ?? 'N/A') . ', Price: ₱' . $price . ')';
                    logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'add_supply', $description);
                }

                $_SESSION['success'] = 'Supply product added successfully!';
            } else {
                $_SESSION['error'] = 'Failed to add supply product.';
            }
        } else {
            // For updates, check if category is being changed
            $currentCategoryStmt = $conn->prepare("SELECT category_id FROM supply_products WHERE id = ?");
            $currentCategoryStmt->bind_param("i", $id);
            $currentCategoryStmt->execute();
            $currentCategoryResult = $currentCategoryStmt->get_result();
            $currentCategoryRow = $currentCategoryResult->fetch_assoc();
            $currentCategoryId = $currentCategoryRow['category_id'];
            $currentCategoryStmt->close();

            // If category is being changed, check the new category's limit
            if ($currentCategoryId != $categoryId) {
                $checkStmt = $conn->prepare("SELECT COUNT(*) as product_count FROM supply_products WHERE category_id = ?");
                $checkStmt->bind_param("i", $categoryId);
                $checkStmt->execute();
                $result = $checkStmt->get_result();
                $row = $result->fetch_assoc();
                $newCategoryCount = $row['product_count'];
                $checkStmt->close();

                // Check if new category already has 2 products
                if ($newCategoryCount >= 2) {
                    $_SESSION['error'] = 'The selected category already has the maximum number of products (2).';
                    header('Location: ../supply-list.php');
                    exit;
                }
            }

            // Update existing product
            $stmt = $conn->prepare("UPDATE supply_products SET category_id = ?, name = ?, measurement = ?, price = ?, max_unit_per_container = ?, description = ? WHERE id = ?");
            $stmt->bind_param("isssisi", $categoryId, $name, $measurement, $price, $maxUnit, $description, $id);

            if ($stmt->execute()) {
                // AUDIT LOGGING - Log product update
                if (file_exists('audit-logger.php')) {
                    $description = 'Updated supply product: ' . $name . ' (measurement: ' . ($measurement ?? 'N/A') . ', Price: ₱' . $price . ')';
                    logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'update_supply', $description);
                }

                $_SESSION['success'] = 'Supply product updated successfully!';
            } else {
                $_SESSION['error'] = 'Failed to update supply product.';
            }
        }

        $stmt->close();
    } catch (Exception $e) {
        // AUDIT LOGGING - Log error
        if (file_exists('audit-logger.php')) {
            $errorDescription = 'Failed to ' . (empty($id) ? 'create' : 'update') . ' supply product: ' . $e->getMessage();
            logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'supply_error', $errorDescription);
        }

        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $_SESSION['error'] = 'Invalid request method.';
}

header('Location: ../supply-list.php');
exit;
