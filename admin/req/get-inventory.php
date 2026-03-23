<?php
require_once '../config/db_conn.php';

$inventoryItems = fetchInventoryItems($conn);
$supplyInOutItems = fetchSupplyInOutItems($conn, $_GET);

/**
 * Fetch current inventory items.
 */
function fetchInventoryItems($conn)
{
    $stmt = $conn->query("
        SELECT 
            i.id, 
            i.product_id,
            sp.name AS product_name,
            sc.name AS category_name, 
            i.stock_quantity,
            i.available_units,
            sp.max_unit_per_container
        FROM inventory i
        INNER JOIN supply_products sp ON i.product_id = sp.id
        INNER JOIN supply_categories sc ON sp.category_id = sc.id
        WHERE sp.is_active = 1
        ORDER BY sc.name, sp.name
    ");
    $items = $stmt ? $stmt->fetch_all(MYSQLI_ASSOC) : [];

    // Check and replenish inventory
    foreach ($items as &$item) {
        if ($item['available_units'] == 0 && $item['stock_quantity'] > 0) {
            replenishInventory($conn, $item);
        }
    }
    unset($item);

    return $items;
}

function replenishInventory($conn, &$item)
{
    $item['available_units'] = $item['max_unit_per_container'];
    $item['stock_quantity'] -= 1;

    // Update database
    $conn->query("
        UPDATE inventory 
        SET available_units = {$item['available_units']}, 
            stock_quantity = {$item['stock_quantity']}
        WHERE product_id = {$item['product_id']}
    ");

    // Log the transaction
    $conn->query("
        INSERT INTO supply_transactions (product_id, quantity, type, created_at)
        VALUES ({$item['product_id']}, 1, 'Used', NOW())
    ");
}

function fetchSupplyInOutItems($conn, $filterParams = [])
{
    // Build the base query
    $query = "
        SELECT 
            st.id,
            st.created_at AS date, 
            sp.name AS supply_name, 
            sc.name AS category_name,
            st.quantity, 
            st.type
        FROM supply_transactions st
        INNER JOIN supply_products sp ON st.product_id = sp.id
        INNER JOIN supply_categories sc ON sp.category_id = sc.id
        WHERE sp.is_active = 1
    ";

    // Add date filtering if parameters exist
    $whereConditions = [];
    $params = [];

    if (!empty($filterParams['startDate'])) {
        $whereConditions[] = "st.created_at >= ?";
        $params[] = $filterParams['startDate'] . ' 00:00:00';
    }

    if (!empty($filterParams['endDate'])) {
        $whereConditions[] = "st.created_at <= ?";
        $params[] = $filterParams['endDate'] . ' 23:59:59';
    }

    if (!empty($whereConditions)) {
        $query .= " AND " . implode(" AND ", $whereConditions);
    }

    $query .= " ORDER BY st.created_at DESC";

    // Prepare and execute the query with parameters
    $stmt = $conn->prepare($query);

    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}
