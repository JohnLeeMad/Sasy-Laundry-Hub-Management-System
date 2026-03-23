<?php
session_start();
require_once '../../config/db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['order_id'] ?? '';
    $newStatus = $_POST['new_status'] ?? '';
    $currentStatusFilter = $_POST['current_filter'] ?? 'Pending'; // Get current filter
    
    if (empty($orderId) || empty($newStatus)) {
        $_SESSION['error'] = 'Invalid request parameters.';
        header('Location: ../laundry-list.php?status_filter=' . urlencode($currentStatusFilter));
        exit;
    }
    
    // Validate status
    $validStatuses = ['Pre-listed', 'Pending', 'Ongoing', 'Ready for Pickup', 'Claimed', 'Unclaimed'];
    if (!in_array($newStatus, $validStatuses)) {
        $_SESSION['error'] = 'Invalid status.';
        header('Location: ../laundry-list.php?status_filter=' . urlencode($currentStatusFilter));
        exit;
    }
    
    // Get current order data for validation
    $orderData = getOrderData($conn, $orderId);
    if (!$orderData) {
        $_SESSION['error'] = 'Order not found.';
        header('Location: ../laundry-list.php?status_filter=' . urlencode($currentStatusFilter));
        exit;
    }
    
    // Validate status transition
    $validationErrors = validateStatusTransition($orderData, $newStatus);
    if (!empty($validationErrors)) {
        $_SESSION['error'] = implode('<br>', $validationErrors);
        header('Location: ../laundry-list.php?status_filter=' . urlencode($currentStatusFilter));
        exit;
    }
    
    // Determine where to redirect after update
    $redirectFilter = determineRedirectFilter($newStatus, $currentStatusFilter);
    
    // Update the status in the database
    $query = "UPDATE laundry_lists SET status = ?, updated_at = NOW() WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "si", $newStatus, $orderId);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = 'Order status updated successfully.';
            
            // Log the status change
            logStatusChange($conn, $orderId, $orderData['status'], $newStatus, $orderData);
            
            // If status changed to Claimed, trigger final receipt printing
            if ($newStatus === 'Claimed') {
                mysqli_stmt_close($stmt);
                header('Location: ../laundry-list.php?status_filter=' . urlencode($redirectFilter) . '&print_final_receipt=' . $orderId);
                exit;
            }
        } else {
            $_SESSION['error'] = 'Failed to update order status.';
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = 'Database error.';
    }
    
    header('Location: ../laundry-list.php?status_filter=' . urlencode($redirectFilter));
    exit;
} else {
    $_SESSION['error'] = 'Invalid request method.';
    header('Location: ../laundry-list.php?status_filter=Pending');
    exit;
}

function getOrderData($conn, $orderId) {
    $query = "SELECT 
                l.status, 
                l.queue_number,
                l.payment_status, 
                l.amount_tendered, 
                l.adjusted_total_price,
                l.total_price,
                l.deducted_balance,
                l.customer_id,
                u.name as customer_name,
                l.queue_number
              FROM laundry_lists l 
              LEFT JOIN users u ON l.customer_id = u.id 
              WHERE l.id = ?";
    
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) return null;
    
    mysqli_stmt_bind_param($stmt, "i", $orderId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $orderData = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return $orderData;
}

function validateStatusTransition($orderData, $newStatus) {
    $errors = [];
    $currentStatus = $orderData['status'];
    
    // Define valid status transitions
    $validTransitions = [
        'Pre-listed' => ['Pending'],
        'Pending' => ['Ongoing'],
        'Ongoing' => ['Ready for Pickup'],
        'Ready for Pickup' => ['Claimed', 'Unclaimed'],
        'Unclaimed' => ['Claimed'],
        'Claimed' => [] // No transitions from Claimed
    ];
    
    // Check if transition is valid
    if (!in_array($newStatus, $validTransitions[$currentStatus] ?? [])) {
        $errors[] = "Invalid status transition from {$currentStatus} to {$newStatus}.";
        return $errors; // Return early for invalid transitions
    }
    
    // Special validation for Claimed status
    if ($newStatus === 'Claimed') {
        $amountTendered = floatval($orderData['amount_tendered'] ?? 0);
        $adjustedTotalPrice = floatval($orderData['adjusted_total_price'] ?? 0);
        
        // Check if order is fully paid
        if ($amountTendered < $adjustedTotalPrice) {
            $outstandingAmount = $adjustedTotalPrice - $amountTendered;
            $errors[] = "Cannot mark order as Claimed until full payment is received. Outstanding amount: ₱" . number_format($outstandingAmount, 2);
        }
    }
    
    // Validation for Ready for Pickup status
    if ($newStatus === 'Ready for Pickup') {
        // Optional: Add any specific validations for Ready for Pickup status
        // For example, you might want to ensure the order has been processed
        if ($currentStatus !== 'Ongoing') {
            $errors[] = "Order must be in Ongoing status before it can be marked as Ready for Pickup.";
        }
    }
    
    // Validation for Ongoing status
    if ($newStatus === 'Ongoing') {
        // Optional: Add validations for Ongoing status
        // For example, check if inventory is sufficient for the order
        if ($currentStatus !== 'Pending') {
            $errors[] = "Order must be in Pending status before it can be marked as Ongoing.";
        }
    }
    
    return $errors;
}

function logStatusChange($conn, $orderId, $oldStatus, $newStatus, $orderData) {
    // Simple logging - you can enhance this with your audit logger
    $userId = $_SESSION['user_id'] ?? 0;
    $userName = $_SESSION['user_name'] ?? 'Unknown';
    
    $logQuery = "INSERT INTO status_change_logs (order_id, old_status, new_status, changed_by_user_id, changed_by_user_name, changed_at) 
                 VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $logQuery);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "issis", $orderId, $oldStatus, $newStatus, $userId, $userName);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    // Also update your main audit log if available
    if (file_exists(__DIR__ . '/audit-logger.php')) {
        require_once __DIR__ . '/audit-logger.php';
        
        $queueNumber = isset($orderData['queue_number']) ? $orderData['queue_number'] : 'N/A';
        $description = "Order Queue #{$queueNumber} status changed from {$oldStatus} to {$newStatus}";
        logActivity(
            $_SESSION['user_id'] ?? 0, 
            $_SESSION['user_role'] ?? 'Unknown', 
            $_SESSION['user_name'] ?? 'Unknown', 
            'status_change', 
            $description
        );
    }
}

function determineRedirectFilter($newStatus, $currentFilter) {
    // If user was viewing "All Orders", keep them there
    if ($currentFilter === 'all') {
        return 'all';
    }
    
    // Smart redirect based on the new status
    switch ($newStatus) {
        case 'Pending':
        case 'Ongoing':
            return $newStatus; // Stay in the current workflow status
        
        case 'Ready for Pickup':
            // If order is ready, staff might want to see other ready orders
            return 'Ready for Pickup';
            
        case 'Claimed':
            // For claimed orders, redirect back to Pending to see new work
            return 'Pending';
            
        case 'Unclaimed':
            // For unclaimed, stay on Unclaimed to manage them
            return 'Unclaimed';
            
        default:
            return $currentFilter; // Fallback to current filter
    }
}

?>