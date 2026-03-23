<?php
require_once '../config/db_conn.php';

function getFilterValues()
{
    global $search;
    $search = isset($_GET['search']) ? $_GET['search'] : '';
}

function buildQuery($conn, $customer_id)
{
    $query = "SELECT 
            ll.*,
            ll.amount_change AS previous_change,
            u.name AS customer_name,
            u.id AS customer_id,
            u.type AS customer_type,
            u.contact_num AS customer_phone,
            u.balance AS customer_balance,
            CASE 
                WHEN ll.accommodated_by_type = 'admin' THEN CONCAT('Admin ', a.name)
                WHEN ll.accommodated_by_type = 'staff' THEN CONCAT('Staff ', s.name)
                ELSE 'System'
            END AS accommodated_by,
            CASE 
                WHEN ll.cancelled_by_type = 'admin' THEN CONCAT('Admin ', cancel_a.name)
                WHEN ll.cancelled_by_type = 'staff' THEN CONCAT('Staff ', cancel_s.name)
                ELSE 'System'
            END AS cancelled_by,
            COALESCE(ld.rounds_of_wash, 1) AS rounds_of_wash,
            COALESCE(ld.scoops_of_detergent, 1) AS scoops_of_detergent,
            COALESCE(ld.dryer_preference, 0) AS dryer_preference,
            COALESCE(ld.folding_service, 0) AS folding_service,
            COALESCE(ld.separate_whites, 0) AS separate_whites,
            COALESCE(ld.is_whites_order, 0) AS is_whites_order,
            COALESCE(ld.bleach_cups, 0) AS bleach_cups,
            COALESCE(ld.fabcon_cups, 0) AS fabcon_cups,
            COALESCE(ld.detergent_product_id, '') AS detergent_product_id,
            COALESCE(ld.fabcon_product_id, '') AS fabcon_product_id,
            COALESCE(ld.bleach_product_id, '') AS bleach_product_id,
            COALESCE(li.tops, 0) AS tops,
            COALESCE(li.bottoms, 0) AS bottoms,
            COALESCE(li.undergarments, 0) AS undergarments,
            COALESCE(li.delicates, 0) AS delicates,
            COALESCE(li.linens, 0) AS linens,
            COALESCE(li.curtains_drapes, 0) AS curtains_drapes,
            COALESCE(li.blankets_comforters, 0) AS blankets_comforters,
            COALESCE(li.others, 0) AS others,
            r.receipt_number,
            r.payment_status,
            r.amount_tendered,
            r.total_price,
            r.amount_change,
            r.order_details,
            r.created_at AS receipt_created_at
          FROM laundry_lists ll
          LEFT JOIN users u ON ll.customer_id = u.id
          LEFT JOIN admins a ON (ll.accommodated_by_type = 'admin' AND ll.accommodated_by_id = a.id)
          LEFT JOIN staffs s ON (ll.accommodated_by_type = 'staff' AND ll.accommodated_by_id = s.id)
          LEFT JOIN admins AS cancel_a ON (ll.cancelled_by_type = 'admin' AND ll.cancelled_by_id = cancel_a.id)
          LEFT JOIN staffs AS cancel_s ON (ll.cancelled_by_type = 'staff' AND ll.cancelled_by_id = cancel_s.id)
          LEFT JOIN laundry_details ld ON ll.id = ld.laundry_list_id
          LEFT JOIN laundry_items li ON ll.id = li.laundry_list_id
          LEFT JOIN receipts r ON ll.id = r.laundry_list_id
          WHERE ll.customer_id = ?
          ORDER BY ll.created_at ASC";

    return $query;
}

function fetchData($conn, $query, $customer_id)
{
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

getFilterValues();
$query = buildQuery($conn, $customer_id);
$allOrders = fetchData($conn, $query, $customer_id);