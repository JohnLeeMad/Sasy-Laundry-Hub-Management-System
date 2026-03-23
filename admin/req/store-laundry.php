<?php
session_start();

require_once '../../config/db_conn.php';
require_once __DIR__ . '/audit-logger.php';

function validateCustomerInput($input, $conn = null)
{
    $errors = [];
    $customerType = $input['customer_type'] ?? null;
    $customerId = $input['customer_id'] ?? null;
    $name = $input['customer_name'] ?? null;
    $contactNum = $input['customer_phone'] ?? null;
    $amountTendered = $input['amount_tendered'] ?? null;

    if (!$customerType || !in_array($customerType, ['registered', 'walk_in'])) {
        $errors[] = 'Invalid customer type.';
    }

    if ($customerType === 'registered' && !$customerId) {
        $errors[] = 'Registered customer must be selected.';
    }

    if ($customerType === 'walk_in') {
        if (!$name || !$contactNum) {
            $errors[] = 'Walk-in customer name and phone number are required.';
        }

        // Check for duplicate phone number
        if ($contactNum && $conn) {
            $stmt = $conn->prepare("SELECT id, name FROM users WHERE contact_num = ?");
            $stmt->bind_param('s', $contactNum);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $existingCustomer = $result->fetch_assoc();
                $errors[] = 'This phone number is already registered to a customer.';
            }
            $stmt->close();
        }
    }

    if (!empty($amountTendered) && $amountTendered < 0) {
        $errors[] = 'Amount tendered must be a positive number.';
    }

    return $errors;
}

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
            $productUsage[$order['detergent_product_id']] =
                ($productUsage[$order['detergent_product_id']] ?? 0) + $order['scoops_of_detergent'];
        }

        if (isset($order['fabcon_product_id']) && $order['fabcon_product_id'] && $order['fabcon_cups'] > 0) {
            $productUsage[$order['fabcon_product_id']] =
                ($productUsage[$order['fabcon_product_id']] ?? 0) + $order['fabcon_cups'];
        }

        if (isset($order['bleach_product_id']) && $order['bleach_product_id'] && $order['bleach_cups'] > 0) {
            $productUsage[$order['bleach_product_id']] =
                ($productUsage[$order['bleach_product_id']] ?? 0) + $order['bleach_cups'];
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

    foreach ($productUsage as $productId => $requiredQuantity) {
        $availableStock = $inventory[$productId] ?? 0;
        if ($requiredQuantity > $availableStock) {
            $productQuery = $conn->prepare("SELECT name FROM supply_products WHERE id = ?");
            $productQuery->bind_param('i', $productId);
            $productQuery->execute();
            $productResult = $productQuery->get_result();
            $productName = $productResult->fetch_assoc()['name'] ?? "Product ID $productId";

            $_SESSION['error'] = "Insufficient stock for $productName. Required: $requiredQuantity, Available: $availableStock";
            header('Location: ../laundry-list.php');
            exit;
        }
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

function processPayment($totalPrice, $amountTendered, $balanceUsed = 0)
{
    $minimumPayment = round(($totalPrice - $balanceUsed) * 0.5, 2);
    $tolerance = 0.001;

    if ($amountTendered < $minimumPayment - $tolerance) {
        $_SESSION['error'] = 'Partial payment is required. Please pay at least 50% of the remaining price after balance deduction.';
        header('Location: ../laundry-list.php');
        exit;
    }

    $change = $amountTendered >= ($totalPrice - $balanceUsed) - $tolerance
        ? round($amountTendered - ($totalPrice - $balanceUsed), 2)
        : 0;

    return [
        'status' => $amountTendered >= ($totalPrice - $balanceUsed) - $tolerance ? 'Paid' : 'Unpaid',
        'change' => $change
    ];
}

function updateInventoryForOrders($conn, $orders)
{
    $productUsage = [];

    foreach ($orders as $order) {
        if (isset($order['detergent_product_id']) && $order['detergent_product_id']) {
            $productUsage[$order['detergent_product_id']] =
                ($productUsage[$order['detergent_product_id']] ?? 0) + $order['scoops_of_detergent'];
        }

        if (isset($order['fabcon_product_id']) && $order['fabcon_product_id'] && $order['fabcon_cups'] > 0) {
            $productUsage[$order['fabcon_product_id']] =
                ($productUsage[$order['fabcon_product_id']] ?? 0) + $order['fabcon_cups'];
        }

        if (isset($order['bleach_product_id']) && $order['bleach_product_id'] && $order['bleach_cups'] > 0) {
            $productUsage[$order['bleach_product_id']] =
                ($productUsage[$order['bleach_product_id']] ?? 0) + $order['bleach_cups'];
        }
    }

    foreach ($productUsage as $productId => $quantity) {
        $stmt = $conn->prepare("UPDATE inventory SET available_units = available_units - ? WHERE product_id = ?");
        $stmt->bind_param('ii', $quantity, $productId);
        $stmt->execute();
    }
}

function generateReceipt($conn, $laundry_list_id, $customer_id, $customer_name, $queue_number, $payment_status, $amount_tendered, $total_price, $amount_change, $order, $balance_used = 0)
{
    // Fetch laundry prices
    $priceQuery = $conn->query("SELECT item_name, price FROM laundry_prices");
    $prices = [];
    while ($row = $priceQuery->fetch_assoc()) {
        $prices[$row['item_name']] = $row['price'];
    }

    // Fetch product names and prices
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
        $productQuery->close();
    }

    // Build order details with clothing and household items
    $order_details = [];

    // Clothing and household items
    $clothingItems = [
        'Tops' => $order['tops'] ?? 0,
        'Bottoms' => $order['bottoms'] ?? 0,
        'Undergarments' => $order['undergarments'] ?? 0,
        'Delicates' => $order['delicates'] ?? 0,
        'Linens' => $order['linens'] ?? 0,
        'Curtains & Drapes' => $order['curtains_drapes'] ?? 0,
        'Blankets & Comforters' => $order['blankets_comforters'] ?? 0,
        'Others' => $order['others'] ?? 0
    ];

    // Check if any clothing items have values > 0
    $hasClothingItems = false;
    foreach ($clothingItems as $quantity) {
        if ($quantity > 0) {
            $hasClothingItems = true;
            break;
        }
    }

    // Only add clothing items section if there are items with values > 0
    if ($hasClothingItems) {
        $order_details[] = 'Clothing & Household Items:';
        foreach ($clothingItems as $name => $quantity) {
            if ($quantity > 0) {
                $order_details[] = "$name: $quantity";
            }
        }
    }

    // Add rounds of wash with price
    $washPrice = ($order['rounds_of_wash'] * $prices['wash_per_round']);
    $order_details[] = sprintf('Washing Round(s): %d x ₱%.2f = ₱%.2f', $order['rounds_of_wash'], $prices['wash_per_round'], $washPrice);

    // Add dryer preference with price
    $dryerPrice = ($order['dryer_preference'] * $prices['dryer_per_round']);
    $order_details[] = sprintf('Drying Round(s): %d x ₱%.2f = ₱%.2f', $order['dryer_preference'], $prices['dryer_per_round'], $dryerPrice);

    // Add folding service with price
    $foldingService = isset($order['folding_service']) && $order['folding_service'] ? 'Yes' : 'No';
    $foldingPrice = $foldingService === 'Yes' ? $prices['folding_service'] : 0;
    $order_details[] = sprintf('Folding Service: %s%s', $foldingService, $foldingPrice > 0 ? sprintf(' - ₱%.2f', $foldingPrice) : '');

    // Add separate whites
    $separateWhites = isset($order['separate_whites']) && $order['separate_whites'] ? 'Yes' : 'No';
    $order_details[] = sprintf('Separate Whites: %s', $separateWhites);

    // Add detergent with price
    $detergentName = isset($order['detergent_product_id']) ? ($productNames[$order['detergent_product_id']] ?? 'Unknown Detergent') : 'Unknown Detergent';
    $detergentPrice = isset($order['detergent_product_id']) ? ($order['scoops_of_detergent'] * $productPrices[$order['detergent_product_id']]) : 0;
    $order_details[] = sprintf('Detergent: %d scoop(s) (%s) x ₱%.2f = ₱%.2f', $order['scoops_of_detergent'], $detergentName, $productPrices[$order['detergent_product_id']] ?? 0, $detergentPrice);

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

    // Only show balance information if balance was used
    if ($balance_used > 0) {
        $original_price = $order['total_price'] ?? ($total_price + $balance_used);
        $order_details[] = 'Original Price: ₱' . number_format($original_price, 2);
        $order_details[] = 'Balance Used: -₱' . number_format($balance_used, 2);
    }

    // Check if change was stored as balance
    $change_stored_as_balance = $order['change_stored_as_balance'] ?? 0;
    if ($amount_change > 0 && $change_stored_as_balance == 1) {
        $order_details[] = 'Change ₱' . number_format($amount_change, 2) . ' stored as customer balance';
    }

    $order_details_text = implode("\n", $order_details);

    $stmt = $conn->prepare("INSERT INTO receipts (laundry_list_id, customer_id, customer_name, queue_number, payment_status, amount_tendered, total_price, amount_change, order_details) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('iisissdds', $laundry_list_id, $customer_id, $customer_name, $queue_number, $payment_status, $amount_tendered, $total_price, $amount_change, $order_details_text);
    $stmt->execute();
}

function storeLaundryItems($conn, $laundry_list_id, $items)
{
    $stmt = $conn->prepare("INSERT INTO laundry_items (laundry_list_id, tops, bottoms, undergarments, delicates, linens, curtains_drapes, blankets_comforters, others) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('iiiiiiiii', $laundry_list_id, $items['tops'], $items['bottoms'], $items['undergarments'], $items['delicates'], $items['linens'], $items['curtains_drapes'], $items['blankets_comforters'], $items['others']);
    $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['orders_data'])) {
        $ordersData = json_decode($_POST['orders_data'], true);

        if (empty($ordersData['orders']) || count($ordersData['orders']) === 0) {
            $_SESSION['error'] = 'No valid orders were submitted. Please check your order details.';
            header('Location: ../laundry-list.php');
            exit;
        }

        // Pass $conn to validateCustomerInput for phone number checking
        $customerErrors = validateCustomerInput($ordersData, $conn);
        if (!empty($customerErrors)) {
            $_SESSION['error'] = implode('<br>', $customerErrors);
            header('Location: ../laundry-list.php');
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
            header('Location: ../laundry-list.php');
            exit;
        }

        $orders = [];
        foreach ($ordersData['orders'] as $order) {
            $phpOrder = [
                'rounds_of_wash' => $order['rounds_of_wash'],
                'dryer_preference' => $order['dryer_preference'],
                'folding_service' => $order['folding_service'],
                'separate_whites' => $order['separate_whites'] ?? false,
                'is_whites_order' => $order['is_whites_order'] ?? false,
                'scoops_of_detergent' => 0,
                'fabcon_cups' => 0,
                'bleach_cups' => 0,
                'remarks' => $order['remarks'] ?? ''
            ];

            $items = [
                'tops' => $_POST['tops'] ?? 0,
                'bottoms' => $_POST['bottoms'] ?? 0,
                'undergarments' => $_POST['undergarments'] ?? 0,
                'delicates' => $_POST['delicates'] ?? 0,
                'linens' => $_POST['linens'] ?? 0,
                'curtains_drapes' => $_POST['curtains_drapes'] ?? 0,
                'blankets_comforters' => $_POST['blankets_comforters'] ?? 0,
                'others' => $_POST['others'] ?? 0
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

            $orders[] = ['order' => $phpOrder, 'items' => $items];
        }

        checkInventoryForOrders($conn, array_column($orders, 'order'));

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

        $balanceUsed = $ordersData['use_balance'] ? min($ordersData['balance_used'], $originalGrandTotal) : 0;
        $adjustedGrandTotal = round($originalGrandTotal - $balanceUsed, 2);
        $totalAmountTendered = round($ordersData['amount_tendered'] ?? 0, 2);
        $totalChange = round($ordersData['change'] ?? 0, 2);

        $orderPaymentDetails = [];
        $totalMinimumRequired = 0;

        foreach ($orders as $index => $order) {
            $orderTotal = calculateOrderTotalPrice($conn, $order['order']);
            $balanceDeduction = round(($balanceUsed / $originalGrandTotal) * $orderTotal, 2);
            $adjustedPrice = round($orderTotal - $balanceDeduction, 2);
            $minimumRequired = round($adjustedPrice * 0.5, 2);

            $orderPaymentDetails[$index] = [
                'original_price' => $orderTotal,
                'deducted_balance' => $balanceDeduction,
                'adjusted_price' => $adjustedPrice,
                'minimum_required' => $minimumRequired,
                'amount_tendered' => 0,
                'amount_change' => 0,
                'payment_status' => 'Unpaid'
            ];
            $totalMinimumRequired += $minimumRequired;
        }
        $totalMinimumRequired = round($totalMinimumRequired, 2);

        $tolerance = 0.001;
        if ($totalAmountTendered < $totalMinimumRequired - $tolerance) {
            $_SESSION['error'] = 'Insufficient payment. Total minimum required: ₱' . number_format($totalMinimumRequired, 2) . ', but only ₱' . number_format($totalAmountTendered, 2) . ' was tendered.';
            header('Location: ../laundry-list.php');
            exit;
        }

        $remainingTendered = $totalAmountTendered;
        foreach ($orders as $index => $order) {
            $adjustedPrice = $orderPaymentDetails[$index]['adjusted_price'];
            $proportion = $adjustedPrice / $adjustedGrandTotal;
            $allocatedTendered = round($totalAmountTendered * $proportion, 2);

            $minRequired = $orderPaymentDetails[$index]['minimum_required'];
            $allocatedTendered = max($allocatedTendered, $minRequired);

            if ($allocatedTendered > $remainingTendered) {
                $allocatedTendered = $remainingTendered;
            }

            $orderPaymentDetails[$index]['amount_tendered'] = $allocatedTendered;
            $remainingTendered = round($remainingTendered - $allocatedTendered, 2);

            if ($allocatedTendered >= $orderPaymentDetails[$index]['adjusted_price'] - $tolerance) {
                $orderPaymentDetails[$index]['payment_status'] = 'Paid';
                $orderPaymentDetails[$index]['amount_change'] = round($totalChange * $proportion, 2);
            }
        }

        $conn->autocommit(false);
        try {
            $customerId = $ordersData['customer_id'] ?? null;
            if ($ordersData['customer_type'] === 'walk_in') {
                $stmt = $conn->prepare("INSERT INTO users (name, contact_num, type, email, password) VALUES (?, ?, 'walk-in', ?, ?)");
                $email = 'walkin_' . time() . '@example.com';
                $password = password_hash(bin2hex(random_bytes(5)), PASSWORD_BCRYPT);
                $stmt->bind_param('ssss', $ordersData['customer_name'], $ordersData['customer_phone'], $email, $password);
                $stmt->execute();
                $customerId = $conn->insert_id;
            }

            $customerName = $ordersData['customer_type'] === 'walk_in' ? $ordersData['customer_name'] : null;
            if (!$customerName) {
                $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
                $stmt->bind_param('i', $customerId);
                $stmt->execute();
                $result = $stmt->get_result();
                $customerName = $result->fetch_assoc()['name'];
            }

            $accommodated_by_id = $_SESSION['user_id'];
            $accommodated_by_type = $_SESSION['user_role'];
            $globalRemarks = $ordersData['global_remarks'] ?? ''; // Get global remarks

            // Initialize the array to store laundry list IDs
            $orderQueueNumbers = [];

            foreach ($orders as $index => $orderData) {
                $order = $orderData['order'];
                $items = $orderData['items'];

                $orderDetails = $orderPaymentDetails[$index];

                $orderDeductedBalance = $orderDetails['deducted_balance'];
                $adjustedOrderTotal = $orderDetails['adjusted_price'];
                $amountTendered = $orderDetails['amount_tendered'];
                $paymentStatus = $orderDetails['payment_status'];
                $amountChange = $orderDetails['amount_change'];
                $originalOrderTotal = $orderDetails['original_price'];

                // Use per-order remarks if available; otherwise, use global remarks
                $remarks = !empty($order['remarks']) ? $order['remarks'] : $globalRemarks;

                $stmt = $conn->prepare("INSERT INTO laundry_lists 
            (customer_id, accommodated_by_id, accommodated_by_type, 
            status, payment_status, amount_tendered, amount_change, total_price, deducted_balance, adjusted_total_price, remarks) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $status = 'Pending';
                $stmt->bind_param(
                    'iisssddddds',
                    $customerId,
                    $accommodated_by_id,
                    $accommodated_by_type,
                    $status,
                    $paymentStatus,
                    $amountTendered,
                    $amountChange,
                    $originalOrderTotal,
                    $orderDeductedBalance,
                    $adjustedOrderTotal,
                    $remarks
                );
                $stmt->execute();
                $laundry_list_id = $conn->insert_id;

                // Store the laundry list ID for later queue number retrieval
                $orderQueueNumbers[$index] = $laundry_list_id;

                $detergentProductId = $order['detergent_product_id'] ?? null;
                $fabconProductId = (!empty($order['fabcon_product_id']) && $order['fabcon_cups'] > 0)
                    ? $order['fabcon_product_id']
                    : null;
                $bleachProductId = (!empty($order['bleach_product_id']) && $order['bleach_cups'] > 0)
                    ? $order['bleach_product_id']
                    : null;

                $stmt = $conn->prepare("INSERT INTO laundry_details 
                (laundry_list_id, rounds_of_wash, scoops_of_detergent, dryer_preference, 
                folding_service, separate_whites, is_whites_order, bleach_cups, fabcon_cups, detergent_product_id, 
                fabcon_product_id, bleach_product_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $foldingService = $order['folding_service'] ? 1 : 0;
                $separateWhites = ($order['separate_whites'] ?? false) ? 1 : 0;
                $isWhitesOrder = ($order['is_whites_order'] ?? false) ? 1 : 0;

                $stmt->bind_param(
                    'iiiiiiiiiiii',
                    $laundry_list_id,
                    $order['rounds_of_wash'],
                    $order['scoops_of_detergent'],
                    $order['dryer_preference'],
                    $foldingService,
                    $separateWhites,
                    $isWhitesOrder,
                    $order['bleach_cups'],
                    $order['fabcon_cups'],
                    $detergentProductId,
                    $fabconProductId,
                    $bleachProductId
                );
                $stmt->execute();

                storeLaundryItems($conn, $laundry_list_id, $items);
            }

            // Commit the transaction first
            $conn->commit();

            // Store all order IDs for receipt printing
            $allOrderIds = [];

            // NOW get the queue numbers after commit and generate receipts
            foreach ($orderQueueNumbers as $index => $laundry_list_id) {
                $queueStmt = $conn->prepare("SELECT queue_number FROM laundry_lists WHERE id = ?");
                $queueStmt->bind_param('i', $laundry_list_id);
                $queueStmt->execute();
                $queueResult = $queueStmt->get_result();
                $queueNumber = $queueResult->fetch_assoc()['queue_number'];

                // Get the order data for receipt generation
                $orderData = $orders[$index];
                $order = $orderData['order'];
                $items = $orderData['items'];
                $orderDetails = $orderPaymentDetails[$index];

                generateReceipt(
                    $conn,
                    $laundry_list_id,
                    $customerId,
                    $customerName,
                    $queueNumber,
                    $orderDetails['payment_status'],
                    $orderDetails['amount_tendered'],
                    $orderDetails['adjusted_price'],
                    $orderDetails['amount_change'],
                    array_merge($order, $items),
                    $orderDetails['deducted_balance']
                );

                // Store order ID for printing
                $allOrderIds[] = $laundry_list_id;
            }

            // Update customer balance (after commit)
            if ($ordersData['use_balance'] && $customerId) {
                $newBalance = 0;
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

            updateInventoryForOrders($conn, array_column($orders, 'order'));

            $orderCount = count($orders);
            // AUDIT LOGGING - Log order creation activity
            if (file_exists(__DIR__ . '/audit-logger.php')) {
                $orderText = $orderCount === 1 ? 'order' : 'orders';
                $totalAmount = number_format($originalGrandTotal, 2);
                $description = 'Created ' . $orderCount . ' laundry ' . $orderText . ' for customer: ' .
                    $customerName . ' (Total: ₱' . $totalAmount . ')';
                logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'create_order', $description);
            }

            $_SESSION['success'] = 'Laundry order' . ($orderCount !== 1 ? 's' : '') . ' created successfully. '
                . $orderCount . ' order' . ($orderCount !== 1 ? 's' : '') . ' processed.';

            // Redirect with all order IDs for printing
            if (count($allOrderIds) > 0) {
                $orderIdsParam = implode(',', $allOrderIds);
                header('Location: ../laundry-list.php?print_receipt=' . $orderIdsParam);
            } else {
                header('Location: ../laundry-list.php');
            }
            exit;
        } catch (Exception $e) {
            $conn->rollback();

            // AUDIT LOGGING - Log creation error
            if (file_exists(__DIR__ . '/audit-logger.php')) {
                $errorDescription = 'Failed to create laundry order for customer: ' . ($customerName ?? 'Unknown') .
                    ' - Error: ' . $e->getMessage();
                logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'order_create_error', $errorDescription);
            }

            $_SESSION['error'] = 'Error creating laundry orders: ' . $e->getMessage();
            header('Location: ../laundry-list.php');
            exit;
        }
    } else {
        $errors = validateOrderInput($_POST);
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            header('Location: ../laundry-list.php');
            exit;
        }

        $customerErrors = validateCustomerInput($_POST);
        if (!empty($customerErrors)) {
            $_SESSION['error'] = implode('<br>', $customerErrors);
            header('Location: ../laundry-list.php');
            exit;
        }

        $items = [
            'tops' => $_POST['tops'] ?? 0,
            'bottoms' => $_POST['bottoms'] ?? 0,
            'undergarments' => $_POST['undergarments'] ?? 0,
            'delicates' => $_POST['delicates'] ?? 0,
            'linens' => $_POST['linens'] ?? 0,
            'curtains_drapes' => $_POST['curtains_drapes'] ?? 0,
            'blankets_comforters' => $_POST['blankets_comforters'] ?? 0,
            'others' => $_POST['others'] ?? 0
        ];

        checkInventoryForOrders($conn, [$_POST]);

        $originalTotalPrice = calculateOrderTotalPrice($conn, $_POST);
        $balanceUsed = isset($_POST['use_balance']) && $_POST['use_balance'] ? min($_POST['balance'] ?? 0, $originalTotalPrice) : 0;
        $adjustedTotalPrice = round($originalTotalPrice - $balanceUsed, 2);
        $payment = processPayment($originalTotalPrice, $_POST['amount_tendered'] ?? 0, $balanceUsed);

        $conn->autocommit(false);
        try {
            $customerId = $_POST['customer_id'];
            if ($_POST['customer_type'] === 'walk_in') {
                $stmt = $conn->prepare("INSERT INTO users (name, contact_num, type, email, password) VALUES (?, ?, 'walk-in', ?, ?)");
                $email = 'walkin_' . time() . '@example.com';
                $password = password_hash(bin2hex(random_bytes(5)), PASSWORD_BCRYPT);
                $stmt->bind_param('ssss', $_POST['customer_name'], $_POST['customer_phone'], $email, $password);
                $stmt->execute();
                $customerId = $conn->insert_id;
            }

            $customerName = $_POST['customer_type'] === 'walk_in' ? $_POST['customer_name'] : null;
            if (!$customerName) {
                $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
                $stmt->bind_param('i', $customerId);
                $stmt->execute();
                $result = $stmt->get_result();
                $customerName = $result->fetch_assoc()['name'];
            }

            $accommodated_by_id = $_SESSION['user_id'];
            $accommodated_by_type = $_SESSION['user_role'];

            $stmt = $conn->prepare("INSERT INTO laundry_lists 
        (customer_id, accommodated_by_id, accommodated_by_type, status, payment_status, amount_tendered, amount_change, total_price, deducted_balance, adjusted_total_price, remarks) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $status = 'Pending';
            $paymentStatus = $payment['status'];
            $amountTendered = round($_POST['amount_tendered'], 2);
            $amountChange = $payment['change'];
            $remarks = $_POST['remarks'] ?? '';

            $stmt->bind_param(
                'iisssddddds',
                $customerId,
                $accommodated_by_id,
                $accommodated_by_type,
                $status,
                $paymentStatus,
                $amountTendered,
                $amountChange,
                $originalTotalPrice,
                $balanceUsed,
                $adjustedTotalPrice,
                $remarks
            );
            $stmt->execute();
            $laundry_list_id = $conn->insert_id;

            $detergentProductId = !empty($_POST['detergent_product_id']) ? $_POST['detergent_product_id'] : null;
            $fabconProductId = (!empty($_POST['fabcon_product_id']) && ($_POST['fabcon_cups'] ?? 0) > 0)
                ? $_POST['fabcon_product_id']
                : null;
            $bleachProductId = (!empty($_POST['bleach_product_id']) && ($_POST['bleach_cups'] ?? 0) > 0)
                ? $_POST['bleach_product_id']
                : null;

            $stmt = $conn->prepare("INSERT INTO laundry_details 
        (laundry_list_id, rounds_of_wash, scoops_of_detergent, dryer_preference, 
        folding_service, separate_whites, is_whites_order, bleach_cups, fabcon_cups, detergent_product_id, 
        fabcon_product_id, bleach_product_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $foldingService = isset($_POST['folding_service']) ? 1 : 0;
            $separateWhites = ($order['separate_whites'] ?? false) ? 1 : 0;
            $isWhitesOrder = 0; // Single orders are never whites orders

            $stmt->bind_param(
                'iiiiiiiiiiii',
                $laundry_list_id,
                $_POST['rounds_of_wash'],
                $_POST['scoops_of_detergent'],
                $_POST['dryer_preference'],
                $foldingService,
                $separateWhites,
                $isWhitesOrder,
                $_POST['bleach_cups'] ?? 0,
                $_POST['fabcon_cups'] ?? 0,
                $detergentProductId,
                $fabconProductId,
                $bleachProductId
            );
            $stmt->execute();

            storeLaundryItems($conn, $laundry_list_id, $items);

            // Commit first, then get queue number
            $conn->commit();

            // Now get the queue number after commit
            $queueStmt = $conn->prepare("SELECT queue_number FROM laundry_lists WHERE id = ?");
            $queueStmt->bind_param('i', $laundry_list_id);
            $queueStmt->execute();
            $queueResult = $queueStmt->get_result();
            $queueNumber = $queueResult->fetch_assoc()['queue_number'];

            generateReceipt($conn, $laundry_list_id, $customerId, $customerName, $queueNumber, $paymentStatus, $amountTendered, $adjustedTotalPrice, $amountChange, array_merge($_POST, $items), $balanceUsed);

            // Update customer balance (after commit)
            if (isset($_POST['use_balance']) && $customerId) {
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

            updateInventoryForOrders($conn, [$_POST]);

            $_SESSION['success'] = 'Laundry order created successfully.';
            header('Location: ../laundry-list.php');
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = 'Error creating laundry order: ' . $e->getMessage();
            header('Location: ../laundry-list.php');
            exit;
        }
    }
}
