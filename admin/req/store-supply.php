<?php
require_once '../../config/db_conn.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null; // Check if an ID is provided
    $name = $_POST['name'] ?? null;
    $price = $_POST['price'] ?? null;
    $maxUnitPerContainer = $_POST['max_unit_per_container'];

    if (!validateSupplyInput($name, $price)) {
        header('Location: ../supply-list.php');
        exit;
    }

    $conn->autocommit(false); // Begin transaction

    try {
        if ($id) {
            updateSupply($conn, $id, $name, $price, $maxUnitPerContainer);
        } else {
            addNewSupply($conn, $name, $price, $maxUnitPerContainer);
        }

        $conn->commit(); // Commit transaction
        $_SESSION['success'] = "Supply successfully " . ($id ? 'updated' : 'added') . ".";
    } catch (Exception $e) {
        $conn->rollback(); // Rollback transaction on error
        $_SESSION['error'] = $e->getMessage();
    }

    header('Location: ../supply-list.php');
    exit;
}

/**
 * Validate supply input.
 */
function validateSupplyInput($name, $price)
{
    if (!$name || $price === null || $price < 0) {
        $_SESSION['error'] = 'Supply name and valid price are required.';
        return false;
    }
    return true;
}

/**
 * Update an existing supply.
 */
function updateSupply($conn, $id, $name, $price, $maxUnitPerContainer)
{
    // Update supply_list table
    $stmt = $conn->prepare("UPDATE supply_list SET name = ?, price = ?, max_unit_per_container = ? WHERE id = ?");
    $stmt->bind_param("sdii", $name, $price, $maxUnitPerContainer, $id);

    if (!$stmt->execute()) {
        throw new Exception("Failed to update supply.");
    }

    // Update inventory table (only if necessary, e.g., for other fields)
    $stmt = $conn->prepare("UPDATE inventory SET stock_quantity = stock_quantity WHERE supply_id = ?");
    $stmt->bind_param('i', $id);

    if (!$stmt->execute()) {
        throw new Exception('Failed to update inventory for the supply.');
    }
}

/**
 * Add a new supply.
 */
function addNewSupply($conn, $name, $price, $maxUnitPerContainer)
{
    // Insert into supply_list table
    $stmt = $conn->prepare("INSERT INTO supply_list (name, price, max_unit_per_container) VALUES (?, ?, ?)");
    $stmt->bind_param("sdi", $name, $price, $maxUnitPerContainer);

    if (!$stmt->execute()) {
        throw new Exception("Failed to add supply.");
    }

    // Insert into inventory table
    $supplyId = $conn->insert_id; // Get the ID of the newly added supply
    $stmt = $conn->prepare("INSERT INTO inventory (supply_id, stock_quantity, available_units) VALUES (?, 0, ?)");
    $stmt->bind_param('ii', $supplyId, $maxUnitPerContainer);

    if (!$stmt->execute()) {
        throw new Exception('Failed to add supply to inventory.');
    }
}
