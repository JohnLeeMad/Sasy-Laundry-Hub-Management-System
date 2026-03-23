<?php
session_start();
require_once '../../config/db_conn.php';
require_once __DIR__ . '/audit-logger.php'; // Add audit logger

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $amount = $_POST['amount'];
    $billDate = $_POST['bill_date'];
    $description = isset($_POST['description']) ? $_POST['description'] : null;

    // Only check for monthly duplicates for Electricity and Water bills
    if ($type === 'Electricity' || $type === 'Water') {
        // Check if a bill of the same type already exists for the same month and year
        $checkQuery = "SELECT COUNT(*) as count FROM utility_bills 
                       WHERE type = ? 
                       AND YEAR(bill_date) = YEAR(?) 
                       AND MONTH(bill_date) = MONTH(?)";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("sss", $type, $billDate, $billDate);
        $checkStmt->execute();
        $result = $checkStmt->get_result()->fetch_assoc();

        if ($result['count'] > 0) {
            $_SESSION['error'] = ucfirst(strtolower($type)) . ' bill for ' . date('F Y', strtotime($billDate)) . ' already exists';

            // AUDIT LOGGING - Log duplicate attempt
            if (file_exists(__DIR__ . '/audit-logger.php')) {
                $errorDescription = 'Attempted to add duplicate ' . strtolower($type) .
                    ' bill for ' . date('F Y', strtotime($billDate));
                logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'utility_error', $errorDescription);
            }

            header('Location: ../reports.php');
            exit;
        }
    }

    // Proceed with insertion
    $query = "INSERT INTO utility_bills (type, amount, bill_date, description) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sdss", $type, $amount, $billDate, $description);

    if ($stmt->execute()) {
        $_SESSION['success'] = ucfirst(strtolower($type)) . ' expense added successfully';

        // AUDIT LOGGING - Log successful utility bill addition
        if (file_exists(__DIR__ . '/audit-logger.php')) {
            $logDescription = 'Added ' . strtolower($type) . ' bill: ₱' . number_format($amount, 2) .
                ' for ' . date('F Y', strtotime($billDate));
            if ($description) {
                $logDescription .= ' - ' . $description;
            }
            logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'add_utility', $logDescription);
        }
    } else {
        $_SESSION['error'] = 'Error adding expense';

        // AUDIT LOGGING - Log error
        if (file_exists(__DIR__ . '/audit-logger.php')) {
            $errorDescription = 'Failed to add ' . strtolower($type) . ' bill: ' . $stmt->error;
            logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'utility_error', $errorDescription);
        }
    }

    header('Location: ../reports.php');
    exit;
}
