<?php
require_once '../../config/db_conn.php';
session_start();

function validateOrderInput($order)
{
    $errors = [];
    $roundsOfWash = $order['rounds_of_wash'] ?? null;
    $dryerPreference = $order['dryer_preference'] ?? null;

    if (!$roundsOfWash || $roundsOfWash < 1 || $roundsOfWash > 4) {
        $errors[] = 'Rounds of wash must be between 1 and 4.';
    }

    if ($dryerPreference === null || $dryerPreference < 0 || $dryerPreference > 2) {
        $errors[] = 'Dryer preference must be between 0 and 2.';
    }

    // Check detergent (required)
    $hasDetergent = false;
    foreach ($order['products'] ?? [] as $product) {
        if ($product['type'] === 'detergent') {
            $hasDetergent = true;
            if ($product['quantity'] < 1 || $product['quantity'] > 10) {
                $errors[] = 'Scoops of detergent must be between 1 and 10.';
            }
            break;
        }
    }

    if (!$hasDetergent) {
        $errors[] = 'Detergent is required.';
    }

    // Check optional products
    foreach ($order['products'] ?? [] as $product) {
        switch ($product['type']) {
            case 'fabric_conditioner':
                if ($product['quantity'] < 0 || $product['quantity'] > 10) {
                    $errors[] = 'Fabcon cups must be between 0 and 10.';
                }
                break;
            case 'bleach':
                if ($product['quantity'] < 0 || $product['quantity'] > 10) {
                    $errors[] = 'Bleach cups must be between 0 and 10.';
                }
                break;
        }
    }

    return $errors;
}

function checkInventoryForOrders($conn, $orders)
{
    $productUsage = [];

    foreach ($orders as $order) {
        if (isset($order['detergent_product_id']) && $order['detergent_product_id']) {
            $productUsage[$order['detergent_product_id']] = ($productUsage[$order['detergent_product_id']] ?? 0) + $order['scoops_of_detergent'];
        }

        if (isset($order['fabcon_product_id']) && $order['fabcon_product_id'] && $order['fabcon_cups'] > 0) {
            $productUsage[$order['fabcon_product_id']] = ($productUsage[$order['fabcon_product_id']] ?? 0) + $order['fabcon_cups'];
        }

        if (isset($order['bleach_product_id']) && $order['bleach_product_id'] && $order['bleach_cups'] > 0) {
            $productUsage[$order['bleach_product_id']] = ($productUsage[$order['bleach_product_id']] ?? 0) + $order['bleach_cups'];
        }
    }

    if (empty($productUsage)) {
        return [];
    }

    $productIds = array_keys($productUsage);
    $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
    $inventoryQuery = $conn->prepare("SELECT product_id, available_units FROM inventory WHERE product_id IN ($placeholders)");
    $inventoryQuery->bind_param(str_repeat('i', count($productIds)), ...$productIds);
    $inventoryQuery->execute();
    $result = $inventoryQuery->get_result();

    $inventory = [];
    while ($row = $result->fetch_assoc()) {
        $inventory[$row['product_id']] = $row['available_units'];
    }

    $insufficientStock = [];
    foreach ($productUsage as $productId => $requiredQuantity) {
        $availableStock = $inventory[$productId] ?? 0;
        if ($requiredQuantity > $availableStock) {
            $productQuery = $conn->prepare("SELECT name FROM supply_products WHERE id = ?");
            $productQuery->bind_param('i', $productId);
            $productQuery->execute();
            $productResult = $productQuery->get_result();
            $productName = $productResult->fetch_assoc()['name'] ?? "Product ID $productId";

            $insufficientStock[] = "Insufficient stock for $productName. Required: $requiredQuantity, Available: $availableStock";
        }
    }

    // Instead of redirecting immediately, return the error
    if (!empty($insufficientStock)) {
        return ['error' => implode(', ', $insufficientStock)];
    }

    return $inventory;
}

function calculateOrderTotalPrice($conn, $order)
{
    $priceQuery = $conn->query("SELECT item_name, price FROM laundry_prices");
    $prices = [];
    while ($row = $priceQuery->fetch_assoc()) {
        $prices[$row['item_name']] = $row['price'];
    }

    $productIds = array_filter([
        $order['detergent_product_id'] ?? null,
        $order['fabcon_product_id'] ?? null,
        $order['bleach_product_id'] ?? null
    ]);

    $productPrices = [];
    if (!empty($productIds)) {
        $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
        $productPriceQuery = $conn->prepare("SELECT id, unit_price FROM supply_products WHERE id IN ($placeholders)");
        $productPriceQuery->bind_param(str_repeat('i', count($productIds)), ...$productIds);
        $productPriceQuery->execute();
        $result = $productPriceQuery->get_result();

        while ($row = $result->fetch_assoc()) {
            $productPrices[$row['id']] = $row['unit_price'];
        }
    }

    $totalPrice = ($order['rounds_of_wash'] * $prices['wash_per_round']) +
        ($order['dryer_preference'] * $prices['dryer_per_round']) +
        (isset($order['folding_service']) && $order['folding_service'] ? $prices['folding_service'] : 0);

    if (isset($order['detergent_product_id']) && $order['detergent_product_id']) {
        $totalPrice += ($order['scoops_of_detergent'] * $productPrices[$order['detergent_product_id']]);
    }

    if (isset($order['fabcon_product_id']) && $order['fabcon_product_id']) {
        $totalPrice += ($order['fabcon_cups'] * $productPrices[$order['fabcon_product_id']]);
    }

    if (isset($order['bleach_product_id']) && $order['bleach_product_id']) {
        $totalPrice += ($order['bleach_cups'] * $productPrices[$order['bleach_product_id']]);
    }

    return round($totalPrice, 2);
}

function storePrelistOrder($conn, $customerId, $totalPrice, $deductedBalance, $adjustedTotalPrice, $remarks)
{
    $stmt = $conn->prepare("INSERT INTO prelist_orders (customer_id, total_price, deducted_balance, adjusted_total_price, remarks) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('iddds', $customerId, $totalPrice, $deductedBalance, $adjustedTotalPrice, $remarks);
    $stmt->execute();
    return $conn->insert_id;
}

function storePrelistDetails($conn, $prelistOrderId, $order)
{
    $detergentProductId = $order['detergent_product_id'] ?? null;
    $fabconProductId = (!empty($order['fabcon_product_id']) && $order['fabcon_cups'] > 0) ? $order['fabcon_product_id'] : null;
    $bleachProductId = (!empty($order['bleach_product_id']) && $order['bleach_cups'] > 0) ? $order['bleach_product_id'] : null;
    $foldingService = $order['folding_service'] ? 1 : 0;
    $separateWhites = $order['separate_whites'] ? 1 : 0;
    $isWhitesOrder = ($order['is_whites_order'] ?? false) ? 1 : 0;

    $stmt = $conn->prepare("INSERT INTO prelist_details 
        (prelist_order_id, rounds_of_wash, scoops_of_detergent, dryer_preference, 
        folding_service, bleach_cups, fabcon_cups, detergent_product_id, 
        fabcon_product_id, bleach_product_id, separate_whites, is_whites_order) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param(
        'iiiiiiiiiiii', 
        $prelistOrderId, 
        $order['rounds_of_wash'], 
        $order['scoops_of_detergent'], 
        $order['dryer_preference'], 
        $foldingService, 
        $order['bleach_cups'], 
        $order['fabcon_cups'], 
        $detergentProductId, 
        $fabconProductId, 
        $bleachProductId, 
        $separateWhites,
        $isWhitesOrder
    );
    $stmt->execute();
}

function storePrelistItems($conn, $prelistOrderId, $items)
{
    $tops = $items['tops'] ?? 0;
    $bottoms = $items['bottoms'] ?? 0;
    $undergarments = $items['undergarments'] ?? 0;
    $delicates = $items['delicates'] ?? 0;
    $linens = $items['linens'] ?? 0;
    $curtains_drapes = $items['curtains_drapes'] ?? 0;
    $blankets_comforters = $items['blankets_comforters'] ?? 0;
    $others = $items['others'] ?? 0;

    $stmt = $conn->prepare("INSERT INTO prelist_items (prelist_order_id, tops, bottoms, undergarments, delicates, linens, curtains_drapes, blankets_comforters, others) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('iiiiiiiii', $prelistOrderId, $tops, $bottoms, $undergarments, $delicates, $linens, $curtains_drapes, $blankets_comforters, $others);
    $stmt->execute();
}

function updateInventoryForOrders($conn, $orders)
{
    foreach ($orders as $order) {
        // Detergent
        if (isset($order['detergent_product_id']) && $order['detergent_product_id']) {
            $stmt = $conn->prepare("UPDATE inventory SET available_units = available_units - ? WHERE product_id = ?");
            $stmt->bind_param('ii', $order['scoops_of_detergent'], $order['detergent_product_id']);
            $stmt->execute();
        }

        // Fabric Conditioner
        if (isset($order['fabcon_product_id']) && $order['fabcon_product_id'] && $order['fabcon_cups'] > 0) {
            $stmt = $conn->prepare("UPDATE inventory SET available_units = available_units - ? WHERE product_id = ?");
            $stmt->bind_param('ii', $order['fabcon_cups'], $order['fabcon_product_id']);
            $stmt->execute();
        }

        // Bleach
        if (isset($order['bleach_product_id']) && $order['bleach_product_id'] && $order['bleach_cups'] > 0) {
            $stmt = $conn->prepare("UPDATE inventory SET available_units = available_units - ? WHERE product_id = ?");
            $stmt->bind_param('ii', $order['bleach_cups'], $order['bleach_product_id']);
            $stmt->execute();
        }
    }
}

function updateCustomerBalance($conn, $customerId, $balanceUsed)
{
    $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->bind_param('i', $customerId);
    $stmt->execute();
    $result = $stmt->get_result();
    $currentBalance = $result->fetch_assoc()['balance'] ?? 0;
    $newBalance = round($currentBalance - $balanceUsed, 2);

    $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
    $stmt->bind_param('di', $newBalance, $customerId);
    $stmt->execute();
}

function generateReceipt($conn, $prelist_order_id, $customer_id, $customer_name, $payment_status, $total_price, $order, $balance_used = 0)
{
    // Get product names for the receipt
    $productIds = array_filter([
        $order['detergent_product_id'] ?? null,
        $order['fabcon_product_id'] ?? null,
        $order['bleach_product_id'] ?? null
    ]);

    $productNames = [];
    $productPrices = [];
    if (!empty($productIds)) {
        $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
        $productQuery = $conn->prepare("SELECT id, name, unit_price FROM supply_products WHERE id IN ($placeholders)");
        $productQuery->bind_param(str_repeat('i', count($productIds)), ...$productIds);
        $productQuery->execute();
        $result = $productQuery->get_result();

        while ($row = $result->fetch_assoc()) {
            $productNames[$row['id']] = $row['name'];
            $productPrices[$row['id']] = $row['unit_price'];
        }
    }

    // Get prices from laundry_prices table
    $priceQuery = $conn->query("SELECT item_name, price FROM laundry_prices");
    $prices = [];
    while ($row = $priceQuery->fetch_assoc()) {
        $prices[$row['item_name']] = $row['price'];
    }

    // Build order details
    $order_details = [];
    $order_details[] = 'Rounds of Wash: ' . ($order['rounds_of_wash'] ?? 1) . ' x ₱' . number_format($prices['wash_per_round'] ?? 0, 2) . ' = ₱' . number_format(($order['rounds_of_wash'] ?? 1) * ($prices['wash_per_round'] ?? 0), 2);
    $order_details[] = 'Dryer Preference: ' . ($order['dryer_preference'] ?? 0) . ' round(s) x ₱' . number_format($prices['dryer_per_round'] ?? 0, 2) . ' = ₱' . number_format(($order['dryer_preference'] ?? 0) * ($prices['dryer_per_round'] ?? 0), 2);

    // Add folding service if applicable
    if ($order['folding_service'] ?? false) {
        $order_details[] = 'Folding Service: ₱' . number_format($prices['folding_service'] ?? 0, 2);
    }

    // Add detergent with price
    if (($order['scoops_of_detergent'] ?? 0) > 0) {
        $detergentName = isset($order['detergent_product_id']) ? ($productNames[$order['detergent_product_id']] ?? 'Unknown Detergent') : 'Unknown Detergent';
        $detergentPrice = isset($order['detergent_product_id']) ? ($order['scoops_of_detergent'] * $productPrices[$order['detergent_product_id']]) : 0;
        $order_details[] = sprintf('Detergent: %d scoop(s) (%s) x ₱%.2f = ₱%.2f', $order['scoops_of_detergent'], $detergentName, $productPrices[$order['detergent_product_id']] ?? 0, $detergentPrice);
    }

    // Add fabric conditioner with price if applicable
    if (($order['fabcon_cups'] ?? 0) > 0) {
        $fabconName = isset($order['fabcon_product_id']) ? ($productNames[$order['fabcon_product_id']] ?? 'Unknown Fabric Conditioner') : 'Unknown Fabric Conditioner';
        $fabconPrice = isset($order['fabcon_product_id']) ? ($order['fabcon_cups'] * $productPrices[$order['fabcon_product_id']]) : 0;
        $order_details[] = sprintf('Fabric Conditioner: %d cup(s) (%s) x ₱%.2f = ₱%.2f', $order['fabcon_cups'], $fabconName, $productPrices[$order['fabcon_product_id']] ?? 0, $fabconPrice);
    }

    // Add bleach with price if applicable
    if (($order['bleach_cups'] ?? 0) > 0) {
        $bleachName = isset($order['bleach_product_id']) ? ($productNames[$order['bleach_product_id']] ?? 'Unknown Bleach') : 'Unknown Bleach';
        $bleachPrice = isset($order['bleach_product_id']) ? ($order['bleach_cups'] * $productPrices[$order['bleach_product_id']]) : 0;
        $order_details[] = sprintf('Bleach: %d cup(s) (%s) x ₱%.2f = ₱%.2f', $order['bleach_cups'], $bleachName, $productPrices[$order['bleach_product_id']] ?? 0, $bleachPrice);
    }

    // Add remarks if exists
    if (!empty($order['remarks'])) {
        $order_details[] = 'Remarks: ' . $order['remarks'];
    }

    // Show balance information if balance was used
    if ($balance_used > 0) {
        $original_price = $order['total_price'] ?? ($total_price + $balance_used);
        $order_details[] = 'Original Price: ₱' . number_format($original_price, 2);
        $order_details[] = 'Balance Used: -₱' . number_format($balance_used, 2);
    }

    $order_details_text = implode("\n", $order_details);
    $accommodated_by = 'System';

    $stmt = $conn->prepare("INSERT INTO prelist_receipts (prelist_order_id, customer_id, customer_name, payment_status, total_price, order_details, accommodated_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('iissdss', $prelist_order_id, $customer_id, $customer_name, $payment_status, $total_price, $order_details_text, $accommodated_by);
    $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['orders_data'])) {
        $ordersData = json_decode($_POST['orders_data'], true);

        if (empty($ordersData['orders']) || count($ordersData['orders']) === 0) {
            $_SESSION['error'] = 'No valid orders were submitted. Please check your order details.';
            header('Location: ../prelist-orders.php');
            exit;
        }

        $allOrderErrors = [];
        foreach ($ordersData['orders'] as $index => $order) {
            $orderErrors = validateOrderInput($order);
            if (!empty($orderErrors)) {
                foreach ($orderErrors as $error) {
                    $allOrderErrors[] = "Order " . ($index + 1) . ": " . $error;
                }
            }
        }

        if (!empty($allOrderErrors)) {
            $_SESSION['error'] = implode('<br>', $allOrderErrors);
            header('Location: ../prelist-orders.php');
            exit;
        }

        $orders = [];
        foreach ($ordersData['orders'] as $order) {
            $phpOrder = [
                'rounds_of_wash' => $order['rounds_of_wash'],
                'dryer_preference' => $order['dryer_preference'],
                'folding_service' => $order['folding_service'],
                'scoops_of_detergent' => 0,
                'fabcon_cups' => 0,
                'bleach_cups' => 0,
                'separate_whites' => $order['separate_whites'] ?? 0,
                'is_whites_order' => $order['is_whites_order'] ?? false,
                'remarks' => $order['remarks'] ?? ''
            ];

            foreach ($order['products'] as $product) {
                switch ($product['type']) {
                    case 'detergent':
                        $phpOrder['detergent_product_id'] = $product['product_id'];
                        $phpOrder['scoops_of_detergent'] = $product['quantity'];
                        break;
                    case 'fabric_conditioner':
                        $phpOrder['fabcon_product_id'] = $product['product_id'];
                        $phpOrder['fabcon_cups'] = $product['quantity'];
                        break;
                    case 'bleach':
                        $phpOrder['bleach_product_id'] = $product['product_id'];
                        $phpOrder['bleach_cups'] = $product['quantity'];
                        break;
                }
            }

            $items = [
                'tops' => $order['items']['tops'] ?? 0,
                'bottoms' => $order['items']['bottoms'] ?? 0,
                'undergarments' => $order['items']['undergarments'] ?? 0,
                'delicates' => $order['items']['delicates'] ?? 0,
                'linens' => $order['items']['linens'] ?? 0,
                'curtains_drapes' => $order['items']['curtains_drapes'] ?? 0,
                'blankets_comforters' => $order['items']['blankets_comforters'] ?? 0,
                'others' => $order['items']['others'] ?? 0
            ];

            $orders[] = ['order' => $phpOrder, 'items' => $items];
        }

        // Check inventory and handle errors properly
        $inventoryResult = checkInventoryForOrders($conn, array_column($orders, 'order'));
        if (isset($inventoryResult['error'])) {
            $_SESSION['error'] = $inventoryResult['error'];
            header('Location: ../prelist-orders.php');
            exit;
        }

        usort($orders, function ($a, $b) use ($conn) {
            $priceA = calculateOrderTotalPrice($conn, $a['order']);
            $priceB = calculateOrderTotalPrice($conn, $b['order']);
            return $priceB <=> $priceA;
        });

        $originalGrandTotal = 0;
        foreach ($orders as $order) {
            $originalGrandTotal += calculateOrderTotalPrice($conn, $order['order']);
        }
        $originalGrandTotal = round($originalGrandTotal, 2);

        $balanceUsed = $ordersData['use_balance'] ? min($ordersData['balance_used'] ?? 0, $originalGrandTotal) : 0;
        $adjustedGrandTotal = round($originalGrandTotal - $balanceUsed, 2);

        $conn->autocommit(false);
        try {
            $customerId = $_POST['customer_id'] ?? null;
            $customerName = null;
            if (!$customerName) {
                $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
                $stmt->bind_param('i', $customerId);
                $stmt->execute();
                $result = $stmt->get_result();
                $customerName = $result->fetch_assoc()['name'];
            }

            $globalRemarks = $ordersData['global_remarks'] ?? '';

            foreach ($orders as $index => $order) {
                $phpOrder = $order['order'];
                $items = $order['items'];

                $orderTotal = calculateOrderTotalPrice($conn, $phpOrder);
                $orderDeductedBalance = round(($balanceUsed / $originalGrandTotal) * $orderTotal, 2);
                $adjustedOrderTotal = round($orderTotal - $orderDeductedBalance, 2);

                $remarks = !empty($phpOrder['remarks']) ? $phpOrder['remarks'] : $globalRemarks;

                $prelistOrderId = storePrelistOrder($conn, $customerId, $orderTotal, $orderDeductedBalance, $adjustedOrderTotal, $remarks);
                storePrelistDetails($conn, $prelistOrderId, $phpOrder);
                storePrelistItems($conn, $prelistOrderId, $items);

                // Combine order data with items data for the receipt
                $receiptData = array_merge($phpOrder, $items);
                generateReceipt($conn, $prelistOrderId, $customerId, $customerName, 'Unpaid', $adjustedOrderTotal, $receiptData, $orderDeductedBalance);
            }

            // Update customer balance if balance was used
            if ($ordersData['use_balance'] && $balanceUsed > 0 && $customerId) {
                updateCustomerBalance($conn, $customerId, $balanceUsed);
            }

            updateInventoryForOrders($conn, array_column($orders, 'order'));

            $conn->commit();
            $orderCount = count($orders);
            $_SESSION['success'] = $orderCount . ' laundry order' . ($orderCount !== 1 ? 's have' : ' has') . ' been pre-listed successfully. ' . 'Please bring your clothes to the shop and present your receipt number to our staff.';
            header('Location: ../prelist-orders.php');
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = 'Error pre-listing laundry orders: ' . $e->getMessage();
            header('Location: ../prelist-orders.php');
            exit;
        }
    }
}