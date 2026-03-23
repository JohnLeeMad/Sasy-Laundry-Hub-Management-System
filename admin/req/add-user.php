<?php
require_once '../../config/db_conn.php';
require_once __DIR__ . '/audit-logger.php'; // Add audit logger
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $contactNum = $_POST['contact_num'];
    $email = $_POST['email'];
    $type = $_POST['type'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password

    try {
        // Check if the phone number already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE contact_num = ?");
        $stmt->bind_param('s', $contactNum);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $_SESSION['error'] = 'A user with this phone number already exists.';
            header('Location: ../customers.php');
            exit;
        }

        // Insert the new user
        $stmt = $conn->prepare("INSERT INTO users (name, contact_num, email, type, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $name, $contactNum, $email, $type, $password);
        $stmt->execute();

        // AUDIT LOGGING - Log user creation
        if (file_exists(__DIR__ . '/audit-logger.php')) {
            $auditDescription = 'Added new customer: ' . $name . ' (' . $email . ') - Type: ' . $type;
            logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'customer_created', $auditDescription);
        }

        $_SESSION['success'] = 'User added successfully.';
        header('Location: ../customers.php');
        exit;
    } catch (Exception $e) {
        // AUDIT LOGGING - Log error
        if (file_exists(__DIR__ . '/audit-logger.php')) {
            $errorDescription = 'Failed to add customer: ' . $e->getMessage();
            logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'customer_error', $errorDescription);
        }

        $_SESSION['error'] = 'Error adding user: ' . $e->getMessage();
        header('Location: ../customers.php');
        exit;
    }
}
?>