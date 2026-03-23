<?php
require_once '../config/db_conn.php';
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'You must be logged in to view pre-listed orders';
    header('Location: ../../auth/login.php');
    exit();
}

$allPrelistOrders = [];
try {
    $stmt = $conn->prepare("
        SELECT 
            po.id,
            po.customer_id,
            po.status,
            po.total_price,
            po.deducted_balance,
            po.adjusted_total_price,
            po.remarks,
            po.created_at,
            po.updated_at,
            u.name AS customer_name,
            u.contact_num AS customer_phone,
            pd.rounds_of_wash,
            pd.scoops_of_detergent,
            pd.dryer_preference,
            pd.folding_service,
            pd.bleach_cups,
            pd.fabcon_cups,
            pd.detergent_product_id,
            pd.fabcon_product_id,
            pd.bleach_product_id,
            pd.separate_whites,
            pd.is_whites_order,
            pi.tops,
            pi.bottoms,
            pi.undergarments,
            pi.delicates,
            pi.linens,
            pi.curtains_drapes,
            pi.blankets_comforters,
            pi.others,
            pr.receipt_number,
            pr.payment_status,
            pr.total_price AS receipt_total_price,
            pr.order_details,
            pr.created_at AS receipt_created_at,
            pr.accommodated_by
        FROM prelist_orders po
        LEFT JOIN users u ON po.customer_id = u.id
        LEFT JOIN prelist_details pd ON po.id = pd.prelist_order_id
        LEFT JOIN prelist_items pi ON po.id = pi.prelist_order_id
        LEFT JOIN prelist_receipts pr ON po.id = pr.prelist_order_id
    ");
    $stmt->execute();
    $result = $stmt->get_result();

    $ordersMap = [];
    while ($row = $result->fetch_assoc()) {
        $orderId = $row['id'];
        if (!isset($ordersMap[$orderId])) {
            $ordersMap[$orderId] = [
                'id' => $orderId,
                'customer_id' => $row['customer_id'],
                'status' => $row['status'],
                'total_price' => $row['total_price'],
                'deducted_balance' => $row['deducted_balance'],
                'adjusted_total_price' => $row['adjusted_total_price'],
                'remarks' => $row['remarks'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'customer_name' => $row['customer_name'],
                'customer_phone' => $row['customer_phone'],
                'rounds_of_wash' => $row['rounds_of_wash'],
                'scoops_of_detergent' => $row['scoops_of_detergent'],
                'dryer_preference' => $row['dryer_preference'],
                'folding_service' => $row['folding_service'],
                'bleach_cups' => $row['bleach_cups'],
                'fabcon_cups' => $row['fabcon_cups'],
                'detergent_product_id' => $row['detergent_product_id'],
                'fabcon_product_id' => $row['fabcon_product_id'],
                'bleach_product_id' => $row['bleach_product_id'],
                'separate_whites' => $row['separate_whites'],
                'is_whites_order' => $row['is_whites_order'],
                'tops' => $row['tops'],
                'bottoms' => $row['bottoms'],
                'undergarments' => $row['undergarments'],
                'delicates' => $row['delicates'],
                'linens' => $row['linens'],
                'curtains_drapes' => $row['curtains_drapes'],
                'blankets_comforters' => $row['blankets_comforters'],
                'others' => $row['others'],
                'receipt_number' => $row['receipt_number'] ?? 'N/A',
                'payment_status' => $row['payment_status'] ?? 'Unpaid',
                'amount_tendered' => 0.00, 
                'amount_change' => 0.00, 
                'total_price' => $row['receipt_total_price'] ?? $row['adjusted_total_price'],
                'order_details' => $row['order_details'] ?? 'No details',
                'created_at' => $row['receipt_created_at'] ?? $row['created_at'],
                'accommodated_by' => $row['accommodated_by'] ?? 'System'
            ];
        }
    }
    $allPrelistOrders = array_values($ordersMap);
} catch (Exception $e) {
    $_SESSION['error'] = 'Error fetching pre-listed orders: ' . $e->getMessage();
    $allPrelistOrders = [];
}
