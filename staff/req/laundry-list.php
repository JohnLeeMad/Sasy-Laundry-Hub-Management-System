<?php
require_once '../config/db_conn.php';

function getFilterValues()
{
    global $date, $search;
    $date = $_SESSION['laundry_filter_date'] ?? date('Y-m-d');

    if (isset($_GET['date'])) {
        $date = $_GET['date'];
        $_SESSION['laundry_filter_date'] = $date;
    }

    // Get search parameter if exists
    $search = isset($_GET['search']) ? $_GET['search'] : '';
}

function buildQuery($conn, $date)
{
    $query = "SELECT 
        laundry_lists.*, 
        laundry_lists.amount_change AS previous_change,
        users.name AS customer_name, 
        users.id AS customer_id, 
        users.type AS customer_type, 
        users.contact_num AS customer_phone,
        users.balance AS customer_balance,
        CASE 
            WHEN laundry_lists.accommodated_by_type = 'admin' THEN CONCAT('Admin ', admins.name)
            WHEN laundry_lists.accommodated_by_type = 'staff' THEN CONCAT('Staff ', staffs.name)
            ELSE 'System'
        END AS accommodated_by,
        CASE 
            WHEN laundry_lists.cancelled_by_type = 'admin' THEN CONCAT('Admin ', cancel_admins.name)
            WHEN laundry_lists.cancelled_by_type = 'staff' THEN CONCAT('Staff ', cancel_staffs.name)
            ELSE 'System'
        END AS cancelled_by,
        COALESCE(laundry_details.rounds_of_wash, 1) AS rounds_of_wash,
        COALESCE(laundry_details.scoops_of_detergent, 1) AS scoops_of_detergent,
        COALESCE(laundry_details.dryer_preference, 0) AS dryer_preference,
        COALESCE(laundry_details.folding_service, 0) AS folding_service,
        COALESCE(laundry_details.separate_whites, 0) AS separate_whites,
        COALESCE(laundry_details.is_whites_order, 0) AS is_whites_order,
        COALESCE(laundry_details.bleach_cups, 0) AS bleach_cups,
        COALESCE(laundry_details.fabcon_cups, 0) AS fabcon_cups,
        COALESCE(laundry_details.detergent_product_id, '') AS detergent_product_id,
        COALESCE(laundry_details.fabcon_product_id, '') AS fabcon_product_id,
        COALESCE(laundry_details.bleach_product_id, '') AS bleach_product_id,
        receipts.receipt_number,
        receipts.payment_status,
        receipts.amount_tendered,
        receipts.total_price,
        receipts.amount_change,
        receipts.order_details,
        receipts.created_at AS receipt_created_at
    FROM laundry_lists
    LEFT JOIN users ON laundry_lists.customer_id = users.id
    LEFT JOIN admins ON (laundry_lists.accommodated_by_type = 'admin' AND laundry_lists.accommodated_by_id = admins.id)
    LEFT JOIN staffs ON (laundry_lists.accommodated_by_type = 'staff' AND laundry_lists.accommodated_by_id = staffs.id)
    LEFT JOIN admins AS cancel_admins ON (laundry_lists.cancelled_by_type = 'admin' AND laundry_lists.cancelled_by_id = cancel_admins.id)
    LEFT JOIN staffs AS cancel_staffs ON (laundry_lists.cancelled_by_type = 'staff' AND laundry_lists.cancelled_by_id = cancel_staffs.id)
    LEFT JOIN laundry_details ON laundry_lists.id = laundry_details.laundry_list_id
    LEFT JOIN receipts ON laundry_lists.id = receipts.laundry_list_id
    WHERE 1=1";

    if (!empty($date)) {
        $query .= " AND DATE(laundry_lists.created_at) = '" . mysqli_real_escape_string($conn, $date) . "'";
    }

    $query .= " ORDER BY laundry_lists.created_at DESC";
    return $query;
}

function fetchData($conn, $query)
{
    $result = mysqli_query($conn, $query);
    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

getFilterValues();
$query = buildQuery($conn, $date);
$laundryLists = fetchData($conn, $query);

// Fetch all customers
$customersQuery = "SELECT * FROM users";
$customers = fetchData($conn, $customersQuery);