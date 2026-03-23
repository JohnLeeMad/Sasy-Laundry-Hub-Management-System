<?php
require_once '../../config/db_conn.php';
require_once __DIR__ . '/audit-logger.php'; // Add audit logger

session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'You must be logged in to edit laundry orders';
    header('Location: ../auth/login.php');
    exit();
}

function validateUpdateInput($input)
{
    $errors = [];

    if (empty($input['laundry_id'])) {
        $errors[] = 'Invalid laundry ID.';
    }

    if (empty($input['status']) || !in_array($input['status'], ['Pending', 'Ongoing', 'Ready for Pickup', 'Claimed', 'Unclaimed'])) {
        $errors[] = 'Invalid status.';
    }

    if ($input['status'] === 'Claimed') {
        $totalPrice = calculatePureOriginalTotalPrice($input);
        if ($input['amount_tendered'] < $totalPrice) {
            $errors[] = 'Cannot mark order as Claimed until full payment is received.';
        }
    }

    if ($input['current_status'] === 'Pending') {
        if (empty($input['rounds_of_wash']) || $input['rounds_of_wash'] < 1 || $input['rounds_of_wash'] > 4) {
            $errors[] = 'Rounds of wash must be between 1 and 4.';
        }

        if (empty($input['scoops_of_detergent']) || $input['scoops_of_detergent'] < 1 || $input['scoops_of_detergent'] > 10) {
            $errors[] = 'Scoops of detergent must be between 1 and 10.';
        }

        if (!isset($input['dryer_preference']) || $input['dryer_preference'] < 0 || $input['dryer_preference'] > 2) {
            $errors[] = 'Dryer preference must be between 0 and 2.';
        }

        if (($input['fabcon_cups'] ?? 0) < 0 || ($input['fabcon_cups'] ?? 0) > 10) {
            $errors[] = 'Fabcon cups must be between 0 and 10.';
        }

        if (($input['bleach_cups'] ?? 0) < 0 || ($input['bleach_cups'] ?? 0) > 5) {
            $errors[] = 'Bleach cups must be between 0 and 5.';
        }

        // Validate separate_whites
        if (!isset($input['separate_whites']) || !in_array($input['separate_whites'], [0, 1])) {
            $errors[] = 'Separate whites must be 0 or 1.';
        }
    }

    if (empty($input['amount_tendered']) || $input['amount_tendered'] < 0) {
        $errors[] = 'Amount tendered must be a positive number.';
    }

    return $errors;
}

// NEW FUNCTION: Calculate pure original total without any balance deductions
function calculatePureOriginalTotalPrice($input)
{
    global $conn;

    $priceQuery = $conn->query("SELECT item_name, price FROM laundry_prices");
    $prices = [];
    while ($row = $priceQuery->fetch_assoc()) {
        $prices[$row['item_name']] = $row['price'];
    }

    $detergentPrice = isset($input['detergent_product_id']) ? getProductPrice($input['detergent_product_id']) : $prices['detergent_per_scoop'];
    $fabconPrice = isset($input['fabcon_product_id']) ? getProductPrice($input['fabcon_product_id']) : $prices['fabcon_per_cup'];
    $bleachPrice = isset($input['bleach_product_id']) ? getProductPrice($input['bleach_product_id']) : $prices['zonrox_per_cup'];

    $subtotal = ($input['rounds_of_wash'] * $prices['wash_per_round']) +
        ($input['scoops_of_detergent'] * $detergentPrice) +
        ($input['dryer_preference'] * $prices['dryer_per_round']) +
        ($input['fabcon_cups'] * $fabconPrice) +
        ($input['bleach_cups'] * $bleachPrice) +
        (isset($input['folding_service']) ? $prices['folding_service'] : 0) +
        (isset($input['separate_whites']) && $input['separate_whites'] ? $prices['separate_whites'] : 0);

    return $subtotal; // Return pure total without any deductions
}

// MODIFIED FUNCTION: This is the old function, kept for backward compatibility but now calls the pure calculation
function calculateUpdateTotalPrice($input)
{
    return calculatePureOriginalTotalPrice($input);
}

function getProductPrice($productId)
{
    global $conn;
    $query = $conn->prepare("SELECT unit_price FROM supply_products WHERE id = ?");
    $query->bind_param('i', $productId);
    $query->execute();
    $result = $query->get_result();
    return $result->fetch_assoc()['unit_price'] ?? 0;
}

function checkInventory($conn, $input)
{
    $productIds = array_filter([
        $input['detergent_product_id'] ?? null,
        $input['fabcon_product_id'] ?? null,
        $input['bleach_product_id'] ?? null
    ]);

    if (empty($productIds)) {
        return [];
    }

    $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
    $inventoryQuery = $conn->prepare("SELECT product_id, available_units FROM inventory WHERE product_id IN ($placeholders)");
    $inventoryQuery->bind_param(str_repeat('i', count($productIds)), ...$productIds);
    $inventoryQuery->execute();
    $result = $inventoryQuery->get_result();

    $inventory = [];
    while ($row = $result->fetch_assoc()) {
        $inventory[$row['product_id']] = $row['available_units'];
    }
    return $inventory;
}

function getLaundryItems($conn, $laundry_list_id)
{
    $stmt = $conn->prepare("SELECT tops, bottoms, undergarments, delicates, linens, curtains_drapes, blankets_comforters, others FROM laundry_items WHERE laundry_list_id = ?");
    $stmt->bind_param('i', $laundry_list_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc() ?? [];
}

function generateOrUpdateReceipt($conn, $laundry_list_id, $customer_id, $queue_number, $payment_status, $amount_tendered, $total_price, $amount_change, $input)
{
    // Fetch customer name
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->bind_param('i', $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer_name = $result->fetch_assoc()['name'];

    // Fetch laundry prices
    $priceQuery = $conn->query("SELECT item_name, price FROM laundry_prices");
    $prices = [];
    while ($row = $priceQuery->fetch_assoc()) {
        $prices[$row['item_name']] = $row['price'];
    }

    // Fetch product names and prices
    $productIds = array_filter([
        $input['detergent_product_id'] ?? null,
        $input['fabcon_product_id'] ?? null,
        $input['bleach_product_id'] ?? null
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

    // Fetch laundry items
    $items = getLaundryItems($conn, $laundry_list_id);

    // Get original total price and deducted balance
    $originalTotalPrice = calculatePureOriginalTotalPrice($input);
    $deductedBalance = $input['deducted_balance'] ?? 0;
    $change_stored_as_balance = $input['change_stored_as_balance'] ?? 0;

    // Get current Philippine time
    $date = new DateTime('now', new DateTimeZone('Asia/Manila'));
    $modifiedDate = $date->format('M d, Y - h:i A');

    // Build order details
    $order_details = [];

    // Clothing and household items
    $hasClothingItems = false;
    $clothingItems = [
        'Tops' => $items['tops'] ?? 0,
        'Bottoms' => $items['bottoms'] ?? 0,
        'Undergarments' => $items['undergarments'] ?? 0,
        'Delicates' => $items['delicates'] ?? 0,
        'Linens' => $items['linens'] ?? 0,
        'Curtains & Drapes' => $items['curtains_drapes'] ?? 0,
        'Blankets & Comforters' => $items['blankets_comforters'] ?? 0,
        'Others' => $items['others'] ?? 0
    ];

    foreach ($clothingItems as $quantity) {
        if ($quantity > 0) {
            $hasClothingItems = true;
            break;
        }
    }

    if ($hasClothingItems) {
        $order_details[] = 'Clothing & Household Items:';
        foreach ($clothingItems as $name => $quantity) {
            if ($quantity > 0) {
                $order_details[] = "$name: $quantity";
            }
        }
    }

    // Add rounds of wash with price
    $washPrice = ($input['rounds_of_wash'] * $prices['wash_per_round']);
    $order_details[] = sprintf('Washing Round(s): %d x ₱%.2f = ₱%.2f', $input['rounds_of_wash'], $prices['wash_per_round'], $washPrice);

    // Add dryer preference with price
    $dryerPrice = ($input['dryer_preference'] * $prices['dryer_per_round']);
    $order_details[] = sprintf('Drying Round(s): %d x ₱%.2f = ₱%.2f', $input['dryer_preference'], $prices['dryer_per_round'], $dryerPrice);

    // Add folding service with price
    $foldingService = isset($input['folding_service']) && $input['folding_service'] ? 'Yes' : 'No';
    $foldingPrice = $foldingService === 'Yes' ? $prices['folding_service'] : 0;
    $order_details[] = sprintf('Folding Service: %s%s', $foldingService, $foldingPrice > 0 ? sprintf(' - ₱%.2f', $foldingPrice) : '');

    // Add separate whites without price
    $separateWhites = isset($input['separate_whites']) && $input['separate_whites'] ? 'Yes' : 'No';
    $order_details[] = sprintf('Separate Whites: %s', $separateWhites);

    // Add detergent with price
    $detergentName = isset($input['detergent_product_id']) ? ($productNames[$input['detergent_product_id']] ?? 'Unknown Detergent') : 'Unknown Detergent';
    $detergentPrice = isset($input['detergent_product_id']) ? ($input['scoops_of_detergent'] * $productPrices[$input['detergent_product_id']]) : 0;
    $order_details[] = sprintf('Detergent: %d scoop(s) (%s) x ₱%.2f = ₱%.2f', $input['scoops_of_detergent'], $detergentName, $productPrices[$input['detergent_product_id']] ?? 0, $detergentPrice);

    // Add fabric conditioner with price if applicable
    if (($input['fabcon_cups'] ?? 0) > 0) {
        $fabconName = isset($input['fabcon_product_id']) ? ($productNames[$input['fabcon_product_id']] ?? 'Unknown Fabric Conditioner') : 'Unknown Fabric Conditioner';
        $fabconPrice = isset($input['fabcon_product_id']) ? ($input['fabcon_cups'] * $productPrices[$input['fabcon_product_id']]) : 0;
        $order_details[] = sprintf('Fabric Conditioner: %d cup(s) (%s) x ₱%.2f = ₱%.2f', $input['fabcon_cups'], $fabconName, $productPrices[$input['fabcon_product_id']] ?? 0, $fabconPrice);
    }

    // Add bleach with price if applicable
    if (($input['bleach_cups'] ?? 0) > 0) {
        $bleachName = isset($input['bleach_product_id']) ? ($productNames[$input['bleach_product_id']] ?? 'Unknown Bleach') : 'Unknown Bleach';
        $bleachPrice = isset($input['bleach_product_id']) ? ($input['bleach_cups'] * $productPrices[$input['bleach_product_id']]) : 0;
        $order_details[] = sprintf('Bleach: %d cup(s) (%s) x ₱%.2f = ₱%.2f', $input['bleach_cups'], $bleachName, $productPrices[$input['bleach_product_id']] ?? 0, $bleachPrice);
    }

    // Add remarks if exists
    if (!empty($input['remarks'])) {
        $order_details[] = 'Remarks: ' . $input['remarks'];
    }

    // Show balance and price details if balance was used
    if ($deductedBalance > 0) {
        $order_details[] = 'Original Price: ₱' . number_format($originalTotalPrice, 2);
        $order_details[] = 'Balance Used: -₱' . number_format($deductedBalance, 2);
    }

    // Show change or balance stored
    if ($amount_change > 0 && $change_stored_as_balance == 1) {
        $order_details[] = 'Change ₱' . number_format($amount_change, 2) . ' stored as customer balance';
    } elseif ($amount_change > 0) {
        $order_details[] = 'Change: ₱' . number_format($amount_change, 2);
    }

    // Check for modifications
    $checkStmt = $conn->prepare("SELECT amount_tendered, order_details FROM receipts WHERE laundry_list_id = ?");
    $checkStmt->bind_param('i', $laundry_list_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        $existing_receipt = $result->fetch_assoc();
        $existing_amount_tendered = $existing_receipt['amount_tendered'];
        $existing_order_details = $existing_receipt['order_details'];

        // Extract existing modification messages
        preg_match_all('/Updated due to (order modification|amount tendered modification) - Last Modified: .+/', $existing_order_details, $matches);
        $existing_modifications = $matches[0];

        $modification_messages = [];

        // Compare actual values for order details changes
        $currentDetailsStmt = $conn->prepare("SELECT rounds_of_wash, scoops_of_detergent, dryer_preference, folding_service, fabcon_cups, bleach_cups, detergent_product_id, fabcon_product_id, bleach_product_id, separate_whites FROM laundry_details WHERE laundry_list_id = ?");
        $currentDetailsStmt->bind_param('i', $laundry_list_id);
        $currentDetailsStmt->execute();
        $currentDetailsResult = $currentDetailsStmt->get_result();
        $currentDetails = $currentDetailsResult->fetch_assoc();

        if ($currentDetails) {
            $order_details_changed = (
                intval($input['rounds_of_wash']) != intval($currentDetails['rounds_of_wash']) ||
                intval($input['scoops_of_detergent']) != intval($currentDetails['scoops_of_detergent']) ||
                intval($input['dryer_preference']) != intval($currentDetails['dryer_preference']) ||
                intval($input['folding_service'] ?? 0) != intval($currentDetails['folding_service'] ?? 0) ||
                intval($input['fabcon_cups'] ?? 0) != intval($currentDetails['fabcon_cups'] ?? 0) ||
                intval($input['bleach_cups'] ?? 0) != intval($currentDetails['bleach_cups'] ?? 0) ||
                intval($input['detergent_product_id'] ?? 0) != intval($currentDetails['detergent_product_id'] ?? 0) ||
                intval($input['fabcon_product_id'] ?? 0) != intval($currentDetails['fabcon_product_id'] ?? 0) ||
                intval($input['bleach_product_id'] ?? 0) != intval($currentDetails['bleach_product_id'] ?? 0) ||
                intval($input['separate_whites'] ?? 0) != intval($currentDetails['separate_whites'] ?? 0)
            );
        }

        if ($order_details_changed) {
            $modification_messages[] = 'Updated due to order modification - Last Modified: ' . $modifiedDate;
        } else {
            foreach ($existing_modifications as $msg) {
                if (strpos($msg, 'order modification') !== false) {
                    $modification_messages[] = $msg;
                    break;
                }
            }
        }

        // Check if amount tendered changed
        $amount_tendered_changed = floatval($amount_tendered) != floatval($existing_amount_tendered);

        if ($amount_tendered_changed) {
            $modification_messages[] = 'Updated due to amount tendered modification (Previous: ₱' . number_format($existing_amount_tendered, 2) . ') - Last Modified: ' . $modifiedDate;
        } else {
            foreach ($existing_modifications as $msg) {
                if (strpos($msg, 'amount tendered modification') !== false) {
                    $modification_messages[] = $msg;
                    break;
                }
            }
        }

        // Add modification messages if any
        if (!empty($modification_messages)) {
            $order_details[] = '';
            foreach ($modification_messages as $msg) {
                $order_details[] = $msg;
            }
        }
    }

    $order_details_text = implode("\n", $order_details);

    // Update or insert receipt
    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE receipts SET customer_id = ?, customer_name = ?, queue_number = ?, payment_status = ?, amount_tendered = ?, total_price = ?, amount_change = ?, order_details = ? WHERE laundry_list_id = ?");
        $stmt->bind_param('isissddsi', $customer_id, $customer_name, $queue_number, $payment_status, $amount_tendered, $total_price, $amount_change, $order_details_text, $laundry_list_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO receipts (laundry_list_id, customer_id, customer_name, queue_number, payment_status, amount_tendered, total_price, amount_change, order_details) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iisissdds', $laundry_list_id, $customer_id, $customer_name, $queue_number, $payment_status, $amount_tendered, $total_price, $amount_change, $order_details_text);
    }
    $stmt->execute();
}

// NEW FUNCTION: Store amount_change to user's balance
function storeChangeAsBalance($conn, $customer_id, $amount_change)
{
    if ($amount_change > 0) {
        $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt->bind_param('di', $amount_change, $customer_id);
        $stmt->execute();
        return true;
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $laundryId = $_POST['laundry_id'];
    $customerId = null;
    $currentQuery = $conn->prepare("SELECT l.status, l.customer_id, l.accommodated_by_id, l.accommodated_by_type, 
                                          l.queue_number, ld.rounds_of_wash, ld.scoops_of_detergent, ld.dryer_preference, 
                                          ld.folding_service, ld.fabcon_cups, ld.bleach_cups,
                                          ld.detergent_product_id, ld.fabcon_product_id, ld.bleach_product_id,
                                          ld.separate_whites,
                                          l.total_price AS current_total_price, l.adjusted_total_price AS current_adjusted_total_price, 
                                          l.deducted_balance, l.amount_change AS previous_change, l.change_stored_as_balance,
                                          l.amount_tendered, l.payment_status  -- ADDED THESE TWO FIELDS
                                   FROM laundry_lists l 
                                   JOIN laundry_details ld ON l.id = ld.laundry_list_id 
                                   WHERE l.id = ?");
    $currentQuery->bind_param('i', $laundryId);
    $currentQuery->execute();
    $currentResult = $currentQuery->get_result();
    $currentData = $currentResult->fetch_assoc();
    $currentStatus = $currentData['status'];
    $customerId = $currentData['customer_id'];
    $previousChange = floatval($currentData['previous_change']);
    $changeStoredAsBalance = intval($currentData['change_stored_as_balance'] ?? 0);
    $customerType = $_POST['customer_type'] ?? 'Registered';

    $_POST['current_status'] = $currentStatus;

    // Set separate_whites from POST or default to current value
    $_POST['separate_whites'] = isset($_POST['separate_whites']) ? intval($_POST['separate_whites']) : ($currentData['separate_whites'] ?? 0);

    // Fetch all orders for the same customer to check total tendered amount
    $customerOrdersQuery = $conn->prepare("SELECT l.id, l.adjusted_total_price, l.amount_tendered 
                                          FROM laundry_lists l 
                                          WHERE l.customer_id = ? AND l.id != ?");
    $customerOrdersQuery->bind_param('ii', $customerId, $laundryId);
    $customerOrdersQuery->execute();
    $customerOrdersResult = $customerOrdersQuery->get_result();
    $customerOrders = $customerOrdersResult->fetch_all(MYSQLI_ASSOC);

    $errors = validateUpdateInput($_POST);
    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
        header('Location: ../laundry-list.php');
        exit;
    }

    if (in_array($currentStatus, ['Ongoing', 'Ready for Pickup', 'Claimed', 'Unclaimed'])) {
        $_POST['rounds_of_wash'] = $currentData['rounds_of_wash'];
        $_POST['scoops_of_detergent'] = $currentData['scoops_of_detergent'];
        $_POST['dryer_preference'] = $currentData['dryer_preference'];
        $_POST['folding_service'] = $currentData['folding_service'];
        $_POST['fabcon_cups'] = $currentData['fabcon_cups'];
        $_POST['bleach_cups'] = $currentData['bleach_cups'];
        $_POST['detergent_product_id'] = $currentData['detergent_product_id'];
        $_POST['fabcon_product_id'] = $currentData['fabcon_product_id'];
        $_POST['bleach_product_id'] = $currentData['bleach_product_id'];
        $_POST['separate_whites'] = $currentData['separate_whites'];
    }

    // In the inventory check section for Pending status:
    if ($currentStatus === 'Pending') {
        $inventory = checkInventory($conn, $_POST);

        // Detergent inventory check
        if (
            isset($_POST['detergent_product_id']) && $_POST['detergent_product_id'] &&
            ($_POST['scoops_of_detergent'] > $currentData['scoops_of_detergent'] ||
                $_POST['detergent_product_id'] != $currentData['detergent_product_id'])
        ) {
            $detergentStock = $inventory[$_POST['detergent_product_id']] ?? 0;
            $detergentNeeded = $_POST['scoops_of_detergent'] - $currentData['scoops_of_detergent'];

            if ($detergentNeeded > $detergentStock) {
                $_SESSION['error'] = 'Insufficient detergent stock. Only ' . $detergentStock . ' units available.';
                header('Location: ../laundry-list.php');
                exit;
            }
        }

        // Fabcon inventory check
        if (
            isset($_POST['fabcon_product_id']) && $_POST['fabcon_product_id'] &&
            (($_POST['fabcon_cups'] > ($currentData['fabcon_cups'] ?? 0)) ||
                $_POST['fabcon_product_id'] != ($currentData['fabcon_product_id'] ?? null))
        ) {
            $fabconStock = $inventory[$_POST['fabcon_product_id']] ?? 0;
            $currentFabconCups = $currentData['fabcon_cups'] ?? 0;
            $fabconNeeded = $_POST['fabcon_cups'] - $currentFabconCups;

            if ($fabconNeeded > $fabconStock) {
                $_SESSION['error'] = 'Insufficient fabric conditioner stock. Only ' . $fabconStock . ' units available.';
                header('Location: ../laundry-list.php');
                exit;
            }
        }

        // Bleach inventory check
        if (
            isset($_POST['bleach_product_id']) && $_POST['bleach_product_id'] &&
            (($_POST['bleach_cups'] > ($currentData['bleach_cups'] ?? 0)) ||
                $_POST['bleach_product_id'] != ($currentData['bleach_product_id'] ?? null))
        ) {
            $bleachStock = $inventory[$_POST['bleach_product_id']] ?? 0;
            $currentBleachCups = $currentData['bleach_cups'] ?? 0;
            $bleachNeeded = $_POST['bleach_cups'] - $currentBleachCups;

            if ($bleachNeeded > $bleachStock) {
                $_SESSION['error'] = 'Insufficient bleach stock. Only ' . $bleachStock . ' units available.';
                header('Location: ../laundry-list.php');
                exit;
            }
        }
    }

    // Calculate the pure original total price (without any balance deductions)
    $originalTotalPrice = calculatePureOriginalTotalPrice($_POST);

    // Get deducted balance from frontend (or recalculate if needed)
    $deductedBalance = floatval($_POST['deducted_balance'] ?? 0);

    // Calculate adjusted total price (what the customer actually needs to pay)
    $adjustedTotalPrice = max(0, $originalTotalPrice - $deductedBalance);

    // Use the user-entered amount_tendered for the current order
    $totalTendered = floatval($_POST['amount_tendered']);

    // Validate total tendered against all orders
    $allOrders = array_merge([['id' => $laundryId, 'adjusted_total_price' => $adjustedTotalPrice, 'amount_tendered' => $totalTendered]], $customerOrders);
    $totalAdjustedPrices = array_sum(array_column($allOrders, 'adjusted_total_price'));
    $totalTenderedAcrossOrders = array_sum(array_column($allOrders, 'amount_tendered'));

    if ($totalTenderedAcrossOrders < $totalAdjustedPrices) {
        // Check if the current order's amount_tendered is sufficient for itself
        if ($totalTendered < $adjustedTotalPrice * 0.5) {
            $_SESSION['error'] = 'Amount tendered is less than the minimum required payment for this order.';
            header('Location: ../laundry-list.php');
            exit;
        }
        // Optionally, warn the user that other orders may have insufficient payment
        $_SESSION['warning'] = 'Warning: The total tendered amount across all orders is less than the total adjusted prices. Other orders may remain unpaid.';
    }

    // Payment calculations based on the adjusted total price and user-entered amount_tendered
    $amountChange = max(0, $totalTendered - $adjustedTotalPrice);
    $paymentStatus = ($totalTendered >= $adjustedTotalPrice) ? 'Paid' : 'Unpaid';

    if ($_POST['status'] === 'Claimed' && $paymentStatus !== 'Paid') {
        $_SESSION['error'] = 'Cannot mark order as Claimed until full payment is received.';
        header('Location: ../laundry-list.php');
        exit;
    }

    // Check if we should store change as balance
    $shouldStoreChangeAsBalance = (
        $amountChange > 0 &&
        isset($_POST['change_stored_as_balance']) &&
        $_POST['change_stored_as_balance'] == 1 &&
        $customerType != 'walk-in'
    );

    $conn->autocommit(false);
    try {
        // Check if change was already stored
        if ($shouldStoreChangeAsBalance) {
            $checkStmt = $conn->prepare("SELECT change_stored_as_balance FROM laundry_lists WHERE id = ?");
            $checkStmt->bind_param('i', $laundryId);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            $currentStatus = $result->fetch_assoc()['change_stored_as_balance'] ?? 0;

            if ($currentStatus == 1) {
                $shouldStoreChangeAsBalance = false;
            }
        }

        if ($shouldStoreChangeAsBalance) {
            storeChangeAsBalance($conn, $customerId, $amountChange);
            $changeStoredAsBalance = 1;
        }

        // Update laundry_lists table
        $stmt = $conn->prepare("UPDATE laundry_lists SET status = ?, payment_status = ?, amount_tendered = ?, amount_change = ?, total_price = ?, adjusted_total_price = ?, remarks = ?, change_stored_as_balance = ? WHERE id = ?");
        $stmt->bind_param(
            'ssddddsii',
            $_POST['status'],
            $paymentStatus,
            $totalTendered,
            $amountChange,
            $originalTotalPrice,
            $adjustedTotalPrice,
            $_POST['remarks'],
            $changeStoredAsBalance,
            $laundryId
        );
        $stmt->execute();

        $roundsOfWash = $_POST['rounds_of_wash'];
        $scoopsOfDetergent = $_POST['scoops_of_detergent'];
        $dryerPreference = $_POST['dryer_preference'];
        $foldingService = $_POST['folding_service'] ?? 0;
        $fabconCups = $_POST['fabcon_cups'] ?? 0;
        $bleachCups = $_POST['bleach_cups'] ?? 0;
        $separateWhites = $_POST['separate_whites'] ?? 0;

        $detergentProductId = !empty($_POST['detergent_product_id']) ? $_POST['detergent_product_id'] : null;
        $scoopsOfDetergent = $detergentProductId ? $scoopsOfDetergent : 0;

        $fabconProductId = (!empty($_POST['fabcon_product_id']) && $fabconCups > 0)
            ? $_POST['fabcon_product_id']
            : null;

        $bleachProductId = (!empty($_POST['bleach_product_id']) && $bleachCups > 0)
            ? $_POST['bleach_product_id']
            : null;

        $stmt = $conn->prepare("UPDATE laundry_details SET 
            rounds_of_wash = ?, 
            scoops_of_detergent = ?, 
            dryer_preference = ?, 
            folding_service = ?, 
            fabcon_cups = ?, 
            bleach_cups = ?,
            detergent_product_id = ?,
            fabcon_product_id = ?,
            bleach_product_id = ?,
            separate_whites = ?
            WHERE laundry_list_id = ?");

        $stmt->bind_param(
            'iiiiiiiiiii',
            $roundsOfWash,
            $scoopsOfDetergent,
            $dryerPreference,
            $foldingService,
            $fabconCups,
            $bleachCups,
            $detergentProductId,
            $fabconProductId,
            $bleachProductId,
            $separateWhites,
            $laundryId
        );
        $stmt->execute();

        // Inventory management (same as before)
        if ($currentStatus === 'Pending') {
            if ($detergentProductId && $scoopsOfDetergent > 0) {
                $currentDetergentQty = $currentData['scoops_of_detergent'] ?? 0;
                $currentDetergentId = $currentData['detergent_product_id'] ?? null;

                if ($detergentProductId == $currentDetergentId) {
                    $quantityChange = $currentDetergentQty - $scoopsOfDetergent;
                    if ($quantityChange != 0) {
                        $stmt = $conn->prepare("UPDATE inventory SET available_units = available_units + ? WHERE product_id = ?");
                        $stmt->bind_param('ii', $quantityChange, $detergentProductId);
                        $stmt->execute();
                    }
                } elseif ($currentDetergentId) {
                    $stmt = $conn->prepare("UPDATE inventory SET available_units = available_units + ? WHERE product_id = ?");
                    $stmt->bind_param('ii', $currentDetergentQty, $currentDetergentId);
                    $stmt->execute();

                    $stmt = $conn->prepare("UPDATE inventory SET available_units = available_units - ? WHERE product_id = ?");
                    $stmt->bind_param('ii', $scoopsOfDetergent, $detergentProductId);
                    $stmt->execute();
                } else {
                    $stmt = $conn->prepare("UPDATE inventory SET available_units = available_units - ? WHERE product_id = ?");
                    $stmt->bind_param('ii', $scoopsOfDetergent, $detergentProductId);
                    $stmt->execute();
                }
            } elseif ($currentData['detergent_product_id'] && $currentData['scoops_of_detergent'] > 0) {
                $stmt = $conn->prepare("UPDATE inventory SET available_units = available_units + ? WHERE product_id = ?");
                $stmt->bind_param('ii', $currentData['scoops_of_detergent'], $currentData['detergent_product_id']);
                $stmt->execute();
            }

            if ($fabconProductId && $fabconCups > 0) {
                $currentFabconQty = $currentData['fabcon_cups'] ?? 0;
                $currentFabconId = $currentData['fabcon_product_id'] ?? null;

                if ($fabconProductId == $currentFabconId) {
                    $quantityChange = $currentFabconQty - $fabconCups;
                    if ($quantityChange != 0) {
                        $stmt = $conn->prepare("UPDATE inventory SET available_units = available_units + ? WHERE product_id = ?");
                        $stmt->bind_param('ii', $quantityChange, $fabconProductId);
                        $stmt->execute();
                    }
                } elseif ($currentFabconId) {
                    $stmt = $conn->prepare("UPDATE inventory SET available_units = available_units + ? WHERE product_id = ?");
                    $stmt->bind_param('ii', $currentFabconQty, $currentFabconId);
                    $stmt->execute();

                    $stmt = $conn->prepare("UPDATE inventory SET available_units = available_units - ? WHERE product_id = ?");
                    $stmt->bind_param('ii', $fabconCups, $fabconProductId);
                    $stmt->execute();
                } else {
                    $stmt = $conn->prepare("UPDATE inventory SET available_units = available_units - ? WHERE product_id = ?");
                    $stmt->bind_param('ii', $fabconCups, $fabconProductId);
                    $stmt->execute();
                }
            } elseif ($currentData['fabcon_product_id'] && $currentData['fabcon_cups'] > 0) {
                $stmt = $conn->prepare("UPDATE inventory SET available_units = available_units + ? WHERE product_id = ?");
                $stmt->bind_param('ii', $currentData['fabcon_cups'], $currentData['fabcon_product_id']);
                $stmt->execute();
            }

            if ($bleachProductId && $bleachCups > 0) {
                $currentBleachQty = $currentData['bleach_cups'] ?? 0;
                $currentBleachId = $currentData['bleach_product_id'] ?? null;

                if ($bleachProductId == $currentBleachId) {
                    $quantityChange = $currentBleachQty - $bleachCups;
                    if ($quantityChange != 0) {
                        $stmt = $conn->prepare("UPDATE inventory SET available_units = available_units + ? WHERE product_id = ?");
                        $stmt->bind_param('ii', $quantityChange, $bleachProductId);
                        $stmt->execute();
                    }
                } elseif ($currentBleachId) {
                    $stmt = $conn->prepare("UPDATE inventory SET available_units = available_units + ? WHERE product_id = ?");
                    $stmt->bind_param('ii', $currentBleachQty, $currentBleachId);
                    $stmt->execute();

                    $stmt = $conn->prepare("UPDATE inventory SET available_units = available_units - ? WHERE product_id = ?");
                    $stmt->bind_param('ii', $bleachCups, $bleachProductId);
                    $stmt->execute();
                } else {
                    $stmt = $conn->prepare("UPDATE inventory SET available_units = available_units - ? WHERE product_id = ?");
                    $stmt->bind_param('ii', $bleachCups, $bleachProductId);
                    $stmt->execute();
                }
            } elseif ($currentData['bleach_product_id'] && $currentData['bleach_cups'] > 0) {
                $stmt = $conn->prepare("UPDATE inventory SET available_units = available_units + ? WHERE product_id = ?");
                $stmt->bind_param('ii', $currentData['bleach_cups'], $currentData['bleach_product_id']);
                $stmt->execute();
            }
        }

        // Generate receipt with the adjusted total price (the amount customer actually pays)
        generateOrUpdateReceipt($conn, $laundryId, $currentData['customer_id'], $currentData['queue_number'], $paymentStatus, $totalTendered, $adjustedTotalPrice, $amountChange, $_POST);

        // Get customer name for audit log
        $customerStmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
        $customerStmt->bind_param('i', $currentData['customer_id']);
        $customerStmt->execute();
        $customerResult = $customerStmt->get_result();
        $customerName = $customerResult->fetch_assoc()['name'] ?? 'Unknown';

        $conn->commit();

        // Get customer name for audit log
        $customerStmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
        $customerStmt->bind_param('i', $currentData['customer_id']);
        $customerStmt->execute();
        $customerResult = $customerStmt->get_result();
        $customerName = $customerResult->fetch_assoc()['name'] ?? 'Unknown';

        // Check if status changed to "Claimed" and trigger receipt printing
        $statusChangedToClaimed = ($currentStatus !== 'Claimed' && $_POST['status'] === 'Claimed');

        // ENHANCED AUDIT LOGGING - Track specific changes
        $changes = [];

        // Check status change
        if ($currentStatus !== $_POST['status']) {
            $changes[] = "Status: {$currentStatus} → {$_POST['status']}";
        }

        // Check amount tendered change
        if (floatval($currentData['amount_tendered'] ?? 0) !== floatval($totalTendered)) {
            $changes[] = "Amount Tendered: ₱" . number_format($currentData['amount_tendered'] ?? 0, 2) .
                " → ₱" . number_format($totalTendered, 2);
        }

        // Check payment status change
        $currentPaymentStatus = $currentData['payment_status'] ?? 'Unknown';
        if ($currentPaymentStatus !== $paymentStatus) {
            $changes[] = "Payment Status: {$currentPaymentStatus} → {$paymentStatus}";
        }

        // Check laundry details changes for Pending orders
        if ($currentStatus === 'Pending') {
            $detailChanges = [];

            if (intval($currentData['rounds_of_wash'] ?? 0) !== intval($_POST['rounds_of_wash'])) {
                $detailChanges[] = "Wash Rounds: {$currentData['rounds_of_wash']} → {$_POST['rounds_of_wash']}";
            }

            if (intval($currentData['scoops_of_detergent'] ?? 0) !== intval($_POST['scoops_of_detergent'])) {
                $detailChanges[] = "Detergent Scoops: {$currentData['scoops_of_detergent']} → {$_POST['scoops_of_detergent']}";
            }

            if (intval($currentData['dryer_preference'] ?? 0) !== intval($_POST['dryer_preference'])) {
                $detailChanges[] = "Dryer Rounds: {$currentData['dryer_preference']} → {$_POST['dryer_preference']}";
            }

            if (intval($currentData['folding_service'] ?? 0) !== intval($_POST['folding_service'] ?? 0)) {
                $oldFolding = $currentData['folding_service'] ? 'Yes' : 'No';
                $newFolding = ($_POST['folding_service'] ?? 0) ? 'Yes' : 'No';
                $detailChanges[] = "Folding Service: {$oldFolding} → {$newFolding}";
            }

            if (intval($currentData['fabcon_cups'] ?? 0) !== intval($_POST['fabcon_cups'] ?? 0)) {
                $detailChanges[] = "Fabcon Cups: {$currentData['fabcon_cups']} → {$_POST['fabcon_cups']}";
            }

            if (intval($currentData['bleach_cups'] ?? 0) !== intval($_POST['bleach_cups'] ?? 0)) {
                $detailChanges[] = "Bleach Cups: {$currentData['bleach_cups']} → {$_POST['bleach_cups']}";
            }

            if (intval($currentData['separate_whites'] ?? 0) !== intval($_POST['separate_whites'] ?? 0)) {
                $oldSeparate = $currentData['separate_whites'] ? 'Yes' : 'No';
                $newSeparate = ($_POST['separate_whites'] ?? 0) ? 'Yes' : 'No';
                $detailChanges[] = "Separate Whites: {$oldSeparate} → {$newSeparate}";
            }

            // Check product changes
            if (($currentData['detergent_product_id'] ?? null) != ($_POST['detergent_product_id'] ?? null)) {
                $oldDetergentId = $currentData['detergent_product_id'] ?? null;
                $newDetergentId = $_POST['detergent_product_id'] ?? null;

                $oldDetergentName = 'None';
                $newDetergentName = 'None';

                if ($oldDetergentId) {
                    $stmt = $conn->prepare("SELECT name FROM supply_products WHERE id = ?");
                    $stmt->bind_param('i', $oldDetergentId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $oldDetergentName = $result->fetch_assoc()['name'] ?? 'Unknown';
                }

                if ($newDetergentId) {
                    $stmt = $conn->prepare("SELECT name FROM supply_products WHERE id = ?");
                    $stmt->bind_param('i', $newDetergentId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $newDetergentName = $result->fetch_assoc()['name'] ?? 'Unknown';
                }

                $detailChanges[] = "Detergent Product: {$oldDetergentName} → {$newDetergentName}";
            }

            if (($currentData['fabcon_product_id'] ?? null) != ($_POST['fabcon_product_id'] ?? null)) {
                $oldFabconId = $currentData['fabcon_product_id'] ?? null;
                $newFabconId = $_POST['fabcon_product_id'] ?? null;

                $oldFabconName = 'None';
                $newFabconName = 'None';

                if ($oldFabconId) {
                    $stmt = $conn->prepare("SELECT name FROM supply_products WHERE id = ?");
                    $stmt->bind_param('i', $oldFabconId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $oldFabconName = $result->fetch_assoc()['name'] ?? 'Unknown';
                }

                if ($newFabconId) {
                    $stmt = $conn->prepare("SELECT name FROM supply_products WHERE id = ?");
                    $stmt->bind_param('i', $newFabconId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $newFabconName = $result->fetch_assoc()['name'] ?? 'Unknown';
                }

                $detailChanges[] = "Fabcon Product: {$oldFabconName} → {$newFabconName}";
            }

            if (($currentData['bleach_product_id'] ?? null) != ($_POST['bleach_product_id'] ?? null)) {
                $oldBleachId = $currentData['bleach_product_id'] ?? null;
                $newBleachId = $_POST['bleach_product_id'] ?? null;

                $oldBleachName = 'None';
                $newBleachName = 'None';

                if ($oldBleachId) {
                    $stmt = $conn->prepare("SELECT name FROM supply_products WHERE id = ?");
                    $stmt->bind_param('i', $oldBleachId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $oldBleachName = $result->fetch_assoc()['name'] ?? 'Unknown';
                }

                if ($newBleachId) {
                    $stmt = $conn->prepare("SELECT name FROM supply_products WHERE id = ?");
                    $stmt->bind_param('i', $newBleachId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $newBleachName = $result->fetch_assoc()['name'] ?? 'Unknown';
                }

                $detailChanges[] = "Bleach Product: {$oldBleachName} → {$newBleachName}";
            }

            if (!empty($detailChanges)) {
                $changes[] = "Laundry Details: " . implode(", ", $detailChanges);
            }
        }

        // Check remarks change
        $currentRemarks = $currentData['remarks'] ?? '';
        $newRemarks = $_POST['remarks'] ?? '';
        if ($currentRemarks !== $newRemarks) {
            $changes[] = "Remarks updated";
        }

        // Check balance deduction change
        $currentDeduction = floatval($currentData['deducted_balance'] ?? 0);
        $newDeduction = floatval($_POST['deducted_balance'] ?? 0);
        if ($currentDeduction !== $newDeduction) {
            $changes[] = "Balance Deduction: ₱" . number_format($currentDeduction, 2) .
                " → ₱" . number_format($newDeduction, 2);
        }

        // Check change storage option
        $currentChangeStorage = intval($currentData['change_stored_as_balance'] ?? 0);
        $newChangeStorage = intval($_POST['change_stored_as_balance'] ?? 0);
        if ($currentChangeStorage !== $newChangeStorage) {
            $oldStorage = $currentChangeStorage ? 'Yes' : 'No';
            $newStorage = $newChangeStorage ? 'Yes' : 'No';
            $changes[] = "Change Stored as Balance: {$oldStorage} → {$newStorage}";
        }

        // Log the changes
        if (file_exists(__DIR__ . '/audit-logger.php')) {
            $description = 'Updated laundry order for customer: ' . $customerName;

            if (!empty($changes)) {
                $description .= ' - Changes: ' . implode('; ', $changes);
            } else {
                $description .= ' - No significant changes detected';
            }

            logActivity(
                $_SESSION['user_id'],
                $_SESSION['user_role'],
                $_SESSION['user_name'],
                'update_order',
                $description
            );
        }

        // If status changed to Claimed, trigger final receipt printing
        if ($statusChangedToClaimed) {
            $_SESSION['success'] = 'Laundry order updated and marked as Claimed successfully.';
            header('Location: ../laundry-list.php?print_final_receipt=' . $laundryId);
            exit;
        } else {
            $_SESSION['success'] = 'Laundry order updated successfully.';
            header('Location: ../laundry-list.php');
            exit;
        }
    } catch (Exception $e) {
        $conn->rollback();

        // AUDIT LOGGING - Log update error with details of attempted changes
        if (file_exists(__DIR__ . '/audit-logger.php')) {
            $errorDescription = 'Failed to update laundry order for customer: ' . ($customerName ?? 'Unknown') .
                ' - Attempted status: ' . ($_POST['status'] ?? 'Unknown') .
                ' - Error: ' . $e->getMessage();
            logActivity(
                $_SESSION['user_id'],
                $_SESSION['user_role'],
                $_SESSION['user_name'],
                'order_update_error',
                $errorDescription
            );
        }

        $_SESSION['error'] = 'Error updating laundry order: ' . $e->getMessage();
        header('Location: ../laundry-list.php');
        exit;
    }
}
