<?php
session_start();
require_once '../../config/db_conn.php';

// Set Philippine timezone
date_default_timezone_set('Asia/Manila');

// Get order ID and receipt type from URL
$orderId = $_GET['order_id'] ?? null;
$receiptType = $_GET['type'] ?? 'initial'; // 'initial' or 'final'

if (!$orderId) {
    echo "Invalid order ID";
    exit;
}

// Fetch complete order data with receipt information
$query = "
    SELECT 
        ll.*,
        r.receipt_number,
        u.name AS customer_name,
        u.contact_num AS customer_phone,
        u.balance as customer_balance,
        ld.rounds_of_wash,
        ld.scoops_of_detergent,
        ld.dryer_preference,
        ld.folding_service,
        ld.bleach_cups,
        ld.fabcon_cups,
        ld.detergent_product_id,
        ld.fabcon_product_id,
        ld.bleach_product_id,
        sp_detergent.name AS detergent_name,
        sp_detergent.unit_price AS detergent_price,
        sp_fabcon.name AS fabcon_name,
        sp_fabcon.unit_price AS fabcon_price,
        sp_bleach.name AS bleach_name,
        sp_bleach.unit_price AS bleach_price,
        li.tops, li.bottoms, li.undergarments, li.delicates,
        li.linens, li.curtains_drapes, li.blankets_comforters, li.others,
        CASE 
            WHEN ll.accommodated_by_type = 'admin' THEN CONCAT('Admin ', a.name)
            WHEN ll.accommodated_by_type = 'staff' THEN CONCAT('Staff ', s.name)
            ELSE 'System'
        END AS accommodated_by
    FROM laundry_lists ll
    LEFT JOIN receipts r ON ll.id = r.laundry_list_id
    LEFT JOIN users u ON ll.customer_id = u.id
    LEFT JOIN laundry_details ld ON ll.id = ld.laundry_list_id
    LEFT JOIN laundry_items li ON ll.id = li.laundry_list_id
    LEFT JOIN supply_products sp_detergent ON ld.detergent_product_id = sp_detergent.id
    LEFT JOIN supply_products sp_fabcon ON ld.fabcon_product_id = sp_fabcon.id
    LEFT JOIN supply_products sp_bleach ON ld.bleach_product_id = sp_bleach.id
    LEFT JOIN admins a ON (ll.accommodated_by_type = 'admin' AND ll.accommodated_by_id = a.id)
    LEFT JOIN staffs s ON (ll.accommodated_by_type = 'staff' AND ll.accommodated_by_id = s.id)
    WHERE ll.id = ?
    ORDER BY r.created_at DESC
    LIMIT 1
";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $orderId);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    echo "Order not found";
    exit;
}

// Fetch laundry prices
$priceQuery = $conn->query("SELECT item_name, price FROM laundry_prices");
$prices = [];
while ($row = $priceQuery->fetch_assoc()) {
    $prices[$row['item_name']] = $row['price'];
}

$is_final = ($receiptType === 'final');
$receiptTitle = $is_final ? 'FINAL RECEIPT' : 'LAUNDRY QUOTATION';
$created_at = date('M d, Y - h:i A', strtotime($order['created_at']));
$current_time = date('M d, Y H:i:s');
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Receipt #<?php echo $order['receipt_number']; ?></title>
    <style>
        @media print {
            @page {
                size: 58mm auto;
                margin: 2mm;
            }

            body {
                width: 58mm;
                margin: 0;
                padding: 2mm;
                font-family: 'Courier New', monospace;
                font-size: 11px;
                line-height: 1.2;
            }

            .no-print {
                display: none !important;
            }
        }

        body {
            width: 58mm;
            margin: 0;
            padding: 2mm;
            font-family: 'Courier New', monospace;
            font-size: 11px;
            line-height: 1.2;
        }

        .header {
            text-align: center;
            margin-bottom: 3mm;
            border-bottom: 1px dashed #000;
            padding-bottom: 2mm;
        }

        .company-name {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 1mm;
        }

        .receipt-title {
            font-size: 13px;
            margin-bottom: 1mm;
            font-weight: bold;
        }

        .section {
            margin-bottom: 2mm;
        }

        .section-title {
            font-weight: bold;
            border-bottom: 1px solid #000;
            margin-bottom: 1mm;
            padding-bottom: 0.5mm;
            text-align: center;
            font-size: 12px;
        }

        .line-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5mm;
            font-size: 11px;
        }

        .line-item .description {
            flex: 2;
        }

        .line-item .amount {
            flex: 1;
            text-align: right;
        }

        .total-section {
            border-top: 1px solid #000;
            margin-top: 1mm;
            padding-top: 0.5mm;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            margin-top: 3mm;
            border-top: 1px dashed #000;
            padding-top: 2mm;
            font-size: 9px;
        }

        .barcode {
            text-align: center;
            margin: 2mm 0;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            font-weight: bold;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 2mm 0;
        }

        .receipt-info {
            font-size: 11px;
            margin: 1mm 0;
        }

        .balance-notice {
            font-size: 9px;
            text-align: center;
            margin: 1mm 0;
            font-style: italic;
        }

        .customer-balance {
            background: #f8f9fa;
            padding: 1mm;
            border: 1px dashed #ccc;
            margin: 1mm 0;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="company-name">SASY LAUNDRY HUB</div>
        <div class="receipt-title"><?php echo $receiptTitle; ?></div>
        <div class="receipt-info">Receipt: #<?php echo $order['receipt_number']; ?></div>
        <div class="receipt-info">Queue: #<?php echo $order['queue_number']; ?></div>
        <div class="receipt-info">Date: <?php echo $created_at; ?></div>
    </div>

    <div class="section">
        <div class="section-title">CUSTOMER INFO</div>
        <div class="line-item">
            <div class="description">Name:</div>
            <div class="amount"><?php echo htmlspecialchars($order['customer_name']); ?></div>
        </div>
        <div class="line-item">
            <div class="description">Accommodated by:</div>
            <div class="amount"><?php echo htmlspecialchars($order['accommodated_by']); ?></div>
        </div>
    </div>

    <div class="divider"></div>

    <div class="section">
        <div class="section-title">ORDER DETAILS</div>

        <!-- Service Charges -->
        <?php if ($order['rounds_of_wash'] > 0): ?>
            <div class="line-item">
                <div class="description">Wash (<?php echo $order['rounds_of_wash']; ?> rounds)</div>
                <div class="amount">₱<?php echo number_format($order['rounds_of_wash'] * ($prices['wash_per_round'] ?? 0), 2); ?></div>
            </div>
        <?php endif; ?>

        <?php if ($order['dryer_preference'] > 0): ?>
            <div class="line-item">
                <div class="description">Dryer (<?php echo $order['dryer_preference']; ?> rounds)</div>
                <div class="amount">₱<?php echo number_format($order['dryer_preference'] * ($prices['dryer_per_round'] ?? 0), 2); ?></div>
            </div>
        <?php endif; ?>

        <?php if ($order['folding_service']): ?>
            <div class="line-item">
                <div class="description">Folding Service</div>
                <div class="amount">₱<?php echo number_format(($prices['folding_service'] ?? 0), 2); ?></div>
            </div>
        <?php endif; ?>

        <!-- Products -->
        <?php if ($order['scoops_of_detergent'] > 0 && $order['detergent_name']): ?>
            <div class="line-item">
                <div class="description"><?php echo $order['detergent_name']; ?> (<?php echo $order['scoops_of_detergent']; ?> scoops)</div>
                <div class="amount">₱<?php echo number_format($order['scoops_of_detergent'] * ($order['detergent_price'] ?? 0), 2); ?></div>
            </div>
        <?php endif; ?>

        <?php if ($order['fabcon_cups'] > 0 && $order['fabcon_name']): ?>
            <div class="line-item">
                <div class="description"><?php echo $order['fabcon_name']; ?> (<?php echo $order['fabcon_cups']; ?> cups)</div>
                <div class="amount">₱<?php echo number_format($order['fabcon_cups'] * ($order['fabcon_price'] ?? 0), 2); ?></div>
            </div>
        <?php endif; ?>

        <?php if ($order['bleach_cups'] > 0 && $order['bleach_name']): ?>
            <div class="line-item">
                <div class="description"><?php echo $order['bleach_name']; ?> (<?php echo $order['bleach_cups']; ?> cups)</div>
                <div class="amount">₱<?php echo number_format($order['bleach_cups'] * ($order['bleach_price'] ?? 0), 2); ?></div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Clothing Items Summary -->
    <?php
    $clothingItems = [
        'Tops' => $order['tops'],
        'Bottoms' => $order['bottoms'],
        'Undergarments' => $order['undergarments'],
        'Delicates' => $order['delicates'],
        'Linens' => $order['linens'],
        'Curtains' => $order['curtains_drapes'],
        'Blankets' => $order['blankets_comforters'],
        'Others' => $order['others']
    ];

    $hasClothingItems = false;
    foreach ($clothingItems as $quantity) {
        if ($quantity > 0) {
            $hasClothingItems = true;
            break;
        }
    }

    if ($hasClothingItems):
    ?>
        <div class="section">
            <div class="section-title">CLOTHING ITEMS</div>
            <?php foreach ($clothingItems as $name => $quantity): ?>
                <?php if ($quantity > 0): ?>
                    <div class="line-item">
                        <div class="description"><?php echo $name; ?></div>
                        <div class="amount"><?php echo $quantity; ?> pcs</div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="divider"></div>

    <!-- Payment Summary -->
    <div class="section">
        <div class="section-title">PAYMENT SUMMARY</div>

        <?php if ($order['deducted_balance'] > 0): ?>
            <div class="line-item">
                <div class="description">Subtotal</div>
                <div class="amount">₱<?php echo number_format($order['total_price'], 2); ?></div>
            </div>
            <div class="line-item">
                <div class="description">Balance Used</div>
                <div class="amount">-₱<?php echo number_format($order['deducted_balance'], 2); ?></div>
            </div>
        <?php endif; ?>

        <div class="line-item total-section">
            <div class="description">TOTAL</div>
            <div class="amount">₱<?php echo number_format($order['adjusted_total_price'], 2); ?></div>
        </div>

        <div class="line-item">
            <div class="description">Amount Tendered</div>
            <div class="amount">₱<?php echo number_format($order['amount_tendered'], 2); ?></div>
        </div>

        <?php if ($order['amount_change'] > 0 && !$order['change_stored_as_balance']): ?>
            <div class="line-item">
                <div class="description">Change</div>
                <div class="amount">₱<?php echo number_format($order['amount_change'], 2); ?></div>
            </div>
        <?php endif; ?>

        <?php if ($order['change_stored_as_balance'] && $order['amount_change'] > 0): ?>
            <div class="line-item">
                <div class="description">Change</div>
                <div class="amount">₱<?php echo number_format($order['amount_change'], 2); ?>*</div>
            </div>
            <div class="balance-notice">
                * Change stored as customer balance
            </div>
        <?php endif; ?>

        <!-- Customer Balance Display - Only show when balance is used or change is stored -->
        <?php if ($order['deducted_balance'] > 0 || $order['change_stored_as_balance']): ?>
            <div class="customer-balance">
                Customer Balance: ₱<?php echo number_format($order['customer_balance'], 2); ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Barcode for quick scanning -->
    <div class="barcode">
        *<?php echo $order['receipt_number']; ?>*
    </div>

    <div class="footer">
        <div>Thank you for your business!</div>
        <?php if (!$is_final): ?>
            <div>Please keep this receipt for claiming</div>
        <?php endif; ?>
        <?php if ($is_final): ?>
            <div>*** FINAL RECEIPT - ORDER CLAIMED ***</div>
            <div>Keep this receipt for your records</div>
        <?php else: ?>
            <div>*** QUOTATION RECEIPT ***</div>
        <?php endif; ?>
        <div>Generated: <?php echo $current_time; ?></div>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);

            // Close window after printing
            setTimeout(function() {
                window.close();
            }, 2000);
        };

        // Handle print dialog cancel
        window.onafterprint = function() {
            setTimeout(function() {
                window.close();
            }, 500);
        };
    </script>
</body>

</html>