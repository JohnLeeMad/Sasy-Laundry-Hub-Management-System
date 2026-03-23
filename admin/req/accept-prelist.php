<?php
require_once '../../config/db_conn.php';
require_once __DIR__ . '/audit-logger.php'; // Add audit logger
session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'You must be logged in to accept pre-listed orders';
    header('Location: ../auth/login.php');
    exit();
}

function validateAcceptInput($input)
{
    $errors = [];

    if (empty($input['prelist_id'])) {
        $errors[] = 'Invalid prelist ID.';
    }

    if (empty($input['customer_id']) || !is_numeric($input['customer_id'])) {
        $errors[] = 'Invalid customer ID.';
    }

    if (empty($input['amount_tendered']) || $input['amount_tendered'] < 0) {
        $errors[] = 'Amount tendered must be a positive number.';
    }

    $totalPrice = floatval($input['adjusted_total_price'] ?? 0);
    if ($input['amount_tendered'] < $totalPrice * 0.5) {
        $errors[] = 'Amount tendered must be at least 50% of the total price.';
    }

    return $errors;
}

function validateCustomerExists($conn, $customerId)
{
    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->bind_param('i', $customerId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

function getPrelistItems($conn, $prelist_order_id)
{
    $stmt = $conn->prepare("SELECT tops, bottoms, undergarments, delicates, linens, curtains_drapes, blankets_comforters, others FROM prelist_items WHERE prelist_order_id = ?");
    $stmt->bind_param('i', $prelist_order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc() ?? [];
}

// Get customer name for audit logging
function getCustomerName($conn, $customerId)
{
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->bind_param('i', $customerId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result ? $result['name'] : 'Unknown Customer';
}

function generateReceipt($conn, $laundryListId, $customerId, $queueNumber, $paymentStatus, $amountTendered, $totalPrice, $amountChange, $input)
{
    // Fetch customer name
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->bind_param('i', $customerId);
    $stmt->execute();
    $result = $stmt->get_result();
    $customerName = $result->fetch_assoc()['name'];

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

    // Get prelist items
    $items = getPrelistItems($conn, $input['prelist_id']);

    $orderDetails = [];

    // Add clothing items if they exist
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
        $orderDetails[] = 'Clothing & Household Items:';
        foreach ($clothingItems as $name => $quantity) {
            if ($quantity > 0) {
                $orderDetails[] = "$name: $quantity";
            }
        }
    }

    // Add rounds of wash with price
    $washPrice = ($input['rounds_of_wash'] * $prices['wash_per_round']);
    $orderDetails[] = sprintf('Washing Round(s): %d x ₱%.2f = ₱%.2f', $input['rounds_of_wash'], $prices['wash_per_round'], $washPrice);

    // Add dryer preference with price
    $dryerPrice = ($input['dryer_preference'] * $prices['dryer_per_round']);
    $orderDetails[] = sprintf('Drying Round(s): %d x ₱%.2f = ₱%.2f', $input['dryer_preference'], $prices['dryer_per_round'], $dryerPrice);

    // Add folding service with price
    $foldingService = isset($input['folding_service']) && $input['folding_service'] ? 'Yes' : 'No';
    $foldingPrice = $foldingService === 'Yes' ? $prices['folding_service'] : 0;
    $orderDetails[] = sprintf('Folding Service: %s%s', $foldingService, $foldingPrice > 0 ? sprintf(' - ₱%.2f', $foldingPrice) : '');

    // Add separate whites with price
    $separateWhites = isset($input['separate_whites']) && $input['separate_whites'] ? 'Yes' : 'No';
    $separateWhitesPrice = $separateWhites === 'Yes' ? $prices['separate_whites'] : 0;
    $orderDetails[] = sprintf('Separate Whites: %s%s', $separateWhites, $separateWhitesPrice > 0 ? sprintf(' - ₱%.2f', $separateWhitesPrice) : '');

    // Add detergent with price
    $detergentName = isset($input['detergent_product_id']) ? ($productNames[$input['detergent_product_id']] ?? 'Unknown Detergent') : 'Unknown Detergent';
    $detergentPrice = isset($input['detergent_product_id']) ? ($input['scoops_of_detergent'] * $productPrices[$input['detergent_product_id']]) : 0;
    $orderDetails[] = sprintf('Detergent: %d scoop(s) (%s) x ₱%.2f = ₱%.2f', $input['scoops_of_detergent'], $detergentName, $productPrices[$input['detergent_product_id']] ?? 0, $detergentPrice);

    // Add fabric conditioner with price if applicable
    if (($input['fabcon_cups'] ?? 0) > 0) {
        $fabconName = isset($input['fabcon_product_id']) ? ($productNames[$input['fabcon_product_id']] ?? 'Unknown Fabric Conditioner') : 'Unknown Fabric Conditioner';
        $fabconPrice = isset($input['fabcon_product_id']) ? ($input['fabcon_cups'] * $productPrices[$input['fabcon_product_id']]) : 0;
        $orderDetails[] = sprintf('Fabric Conditioner: %d cup(s) (%s) x ₱%.2f = ₱%.2f', $input['fabcon_cups'], $fabconName, $productPrices[$input['fabcon_product_id']] ?? 0, $fabconPrice);
    }

    // Add bleach with price if applicable
    if (($input['bleach_cups'] ?? 0) > 0) {
        $bleachName = isset($input['bleach_product_id']) ? ($productNames[$input['bleach_product_id']] ?? 'Unknown Bleach') : 'Unknown Bleach';
        $bleachPrice = isset($input['bleach_product_id']) ? ($input['bleach_cups'] * $productPrices[$input['bleach_product_id']]) : 0;
        $orderDetails[] = sprintf('Bleach: %d cup(s) (%s) x ₱%.2f = ₱%.2f', $input['bleach_cups'], $bleachName, $productPrices[$input['bleach_product_id']] ?? 0, $bleachPrice);
    }

    // Add remarks if exists
    if (!empty($input['remarks'])) {
        $orderDetails[] = 'Remarks: ' . $input['remarks'];
    }

    // Only show balance information if balance was used
    $deductedBalance = $input['deducted_balance'] ?? 0;
    if ($deductedBalance > 0) {
        $original_price = ($deductedBalance + $input['total_price']) ?? 0;
        $orderDetails[] = 'Original Price: ₱' . number_format($original_price, 2);
        $orderDetails[] = 'Balance Used: -₱' . number_format($deductedBalance, 2);
    }

    // Check if change was stored as balance
    $changeStoredAsBalance = $input['change_stored_as_balance'] ?? 0;
    if ($amountChange > 0 && $changeStoredAsBalance == 1) {
        $orderDetails[] = 'Change ₱' . number_format($amountChange, 2) . ' stored as customer balance';
    }

    $orderDetailsText = implode("\n", $orderDetails);

    // Use MySQL datetime format for created_at column
    $date = new DateTime('now', new DateTimeZone('Asia/Manila'));
    $createdAt = $date->format('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO receipts (laundry_list_id, customer_id, customer_name, queue_number, payment_status, amount_tendered, total_price, amount_change, order_details, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('iisissdsss', $laundryListId, $customerId, $customerName, $queueNumber, $paymentStatus, $amountTendered, $totalPrice, $amountChange, $orderDetailsText, $createdAt);
    $stmt->execute();
}

function storeChangeAsBalance($conn, $customerId, $amountChange)
{
    if ($amountChange > 0) {
        $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt->bind_param('di', $amountChange, $customerId);
        $stmt->execute();
        return true;
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prelistId = $_POST['prelist_id'];

    // Fetch pre-listed order data first to get the correct customer_id
    $stmt = $conn->prepare("
        SELECT po.*, pd.*, pi.*, pr.*
        FROM prelist_orders po
        LEFT JOIN prelist_details pd ON po.id = pd.prelist_order_id
        LEFT JOIN prelist_items pi ON po.id = pi.prelist_order_id
        LEFT JOIN prelist_receipts pr ON po.id = pr.prelist_order_id
        WHERE po.id = ?
    ");
    $stmt->bind_param('i', $prelistId);
    $stmt->execute();
    $prelistResult = $stmt->get_result();
    $prelistData = $prelistResult->fetch_assoc();

    if (!$prelistData) {
        $_SESSION['error'] = 'Pre-listed order not found.';

        // AUDIT LOGGING - Log error for non-existent prelist
        if (file_exists(__DIR__ . '/audit-logger.php')) {
            $errorDescription = 'Attempted to accept non-existent pre-listed order #' . $prelistId;
            logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'prelist_error', $errorDescription);
        }

        header('Location: ../prelisted-orders.php');
        exit;
    }

    // Use customer_id from the database, not from POST
    $customerId = $prelistData['customer_id'];
    $customerType = $_POST['customer_type'] ?? 'Registered';

    // Merge POST data with prelist data, but ensure customer_id comes from DB
    $input = array_merge($_POST, $prelistData);
    $input['customer_id'] = $customerId;
    $input['status'] = 'Pending';
    $input['separate_whites'] = isset($_POST['separate_whites']) ? (int)$_POST['separate_whites'] : (isset($prelistData['separate_whites']) ? (int)$prelistData['separate_whites'] : 0);
    $input['is_whites_order'] = isset($_POST['is_whites_order']) ? (int)$_POST['is_whites_order'] : (isset($prelistData['is_whites_order']) ? (int)$prelistData['is_whites_order'] : 0);

    $errors = validateAcceptInput($input);
    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);

        // AUDIT LOGGING - Log validation errors
        if (file_exists(__DIR__ . '/audit-logger.php')) {
            $errorDescription = 'Validation error accepting pre-listed order #' . $prelistId . ': ' . implode(', ', $errors);
            logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'prelist_error', $errorDescription);
        }

        header('Location: ../prelisted-orders.php');
        exit;
    }

    // Use values directly from the prelist data
    $originalTotalPrice = floatval($prelistData['total_price']);
    $deductedBalance = floatval($input['deducted_balance'] ?? 0);
    $adjustedTotalPrice = floatval($prelistData['adjusted_total_price']);
    $amountTendered = floatval($input['amount_tendered']);
    $amountChange = max(0, $amountTendered - $adjustedTotalPrice);
    $paymentStatus = ($amountTendered >= $adjustedTotalPrice) ? 'Paid' : 'Unpaid';

    // Generate a new queue number
    $queueNumber = 'Q' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

    $conn->autocommit(false);
    try {
        // Get the current user's ID and role from session
        $accommodated_by_id = $_SESSION['user_id'];
        $accommodated_by_type = $_SESSION['user_role'];

        // Insert into laundry_lists
        $stmt = $conn->prepare("INSERT INTO laundry_lists 
            (customer_id, accommodated_by_id, accommodated_by_type, status, payment_status, 
            amount_tendered, amount_change, total_price, adjusted_total_price, 
            deducted_balance, remarks, created_at, change_stored_as_balance) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");

        $changeStoredAsBalance = 0;
        $customerIdInt = (int)$customerId;
        $stmt->bind_param(
            'iisssdddddsi',
            $customerIdInt,
            $accommodated_by_id,
            $accommodated_by_type,
            $input['status'],
            $paymentStatus,
            $amountTendered,
            $amountChange,
            $originalTotalPrice,
            $adjustedTotalPrice,
            $deductedBalance,
            $input['remarks'],
            $changeStoredAsBalance
        );

        if (!$stmt->execute()) {
            throw new Exception('Failed to insert into laundry_lists: ' . $stmt->error);
        }
        $laundryListId = $conn->insert_id;

        // Insert into laundry_details
        $detergentProductId = !empty($input['detergent_product_id']) ? $input['detergent_product_id'] : null;
        $fabconProductId = (!empty($input['fabcon_product_id']) && $input['fabcon_cups'] > 0) ? $input['fabcon_product_id'] : null;
        $bleachProductId = (!empty($input['bleach_product_id']) && $input['bleach_cups'] > 0) ? $input['bleach_product_id'] : null;
        $separateWhites = isset($input['separate_whites']) ? (int)$input['separate_whites'] : 0;
        $isWhitesOrder = isset($input['is_whites_order']) ? (int)$input['is_whites_order'] : 0;

        $stmt = $conn->prepare("INSERT INTO laundry_details 
            (laundry_list_id, rounds_of_wash, scoops_of_detergent, dryer_preference, folding_service, 
            fabcon_cups, bleach_cups, detergent_product_id, fabcon_product_id, bleach_product_id, 
            separate_whites, is_whites_order) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param('iiiiiiiiiiii', 
            $laundryListId, 
            $input['rounds_of_wash'], 
            $input['scoops_of_detergent'], 
            $input['dryer_preference'], 
            $input['folding_service'], 
            $input['fabcon_cups'], 
            $input['bleach_cups'], 
            $detergentProductId, 
            $fabconProductId, 
            $bleachProductId, 
            $separateWhites,
            $isWhitesOrder
        );

        if (!$stmt->execute()) {
            throw new Exception('Failed to insert into laundry_details: ' . $stmt->error);
        }

        // Generate receipt
        generateReceipt($conn, $laundryListId, $customerIdInt, $queueNumber, $paymentStatus, $amountTendered, $adjustedTotalPrice, $amountChange, $input);

        // Store change as balance if applicable
        $shouldStoreChangeAsBalance = ($amountChange > 0 && isset($input['change_stored_as_balance']) && $input['change_stored_as_balance'] == 1 && $customerType != 'walk-in');
        if ($shouldStoreChangeAsBalance) {
            storeChangeAsBalance($conn, $customerIdInt, $amountChange);
            $stmt = $conn->prepare("UPDATE laundry_lists SET change_stored_as_balance = 1 WHERE id = ?");
            $stmt->bind_param('i', $laundryListId);
            $stmt->execute();
        }

        // Delete pre-listed order data
        $stmt = $conn->prepare("DELETE FROM prelist_orders WHERE id = ?");
        $stmt->bind_param('i', $prelistId);
        $stmt->execute();
        $stmt = $conn->prepare("DELETE FROM prelist_details WHERE prelist_order_id = ?");
        $stmt->bind_param('i', $prelistId);
        $stmt->execute();
        $stmt = $conn->prepare("DELETE FROM prelist_items WHERE prelist_order_id = ?");
        $stmt->bind_param('i', $prelistId);
        $stmt->execute();
        $stmt = $conn->prepare("DELETE FROM prelist_receipts WHERE prelist_order_id = ?");
        $stmt->bind_param('i', $prelistId);
        $stmt->execute();

        // AUDIT LOGGING - Log successful prelist acceptance
        if (file_exists(__DIR__ . '/audit-logger.php')) {
            $customerName = getCustomerName($conn, $customerIdInt);
            $description = 'Accepted pre-listed order for customer: ' . $customerName .
                ' - Total: ₱' . number_format($adjustedTotalPrice, 2) .
                ', Tendered: ₱' . number_format($amountTendered, 2);
            logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'accept_prelist', $description);
        }

        $conn->commit();
        $_SESSION['success'] = 'Pre-listed order accepted successfully.';
        // Redirect to print receipt for the accepted order
        header('Location: ../laundry-list.php?print_receipt=' . $laundryListId);
        exit;
    } catch (Exception $e) {
        $conn->rollback();

        // AUDIT LOGGING - Log error during acceptance
        if (file_exists(__DIR__ . '/audit-logger.php')) {
            $errorDescription = 'Failed to accept pre-listed order #' . $prelistId . ': ' . $e->getMessage();
            logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'prelist_error', $errorDescription);
        }

        $_SESSION['error'] = 'Error accepting pre-listed order: ' . $e->getMessage();
        error_log('Error in accept-prelist.php: ' . $e->getMessage());
        header('Location: ../prelisted-orders.php');
        exit;
    }
}
?>