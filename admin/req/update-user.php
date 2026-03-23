<?php
require_once '../../config/db_conn.php';
require_once __DIR__ . '/audit-logger.php'; // Add audit logger
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $contactNum = $_POST['contact_num'];
    $email = $_POST['email'];
    $type = $_POST['type'];
    $originalType = $_POST['original_type'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    try {
        // Get original user data for audit log
        $originalQuery = $conn->prepare("SELECT name, email, contact_num, type FROM users WHERE id = ?");
        $originalQuery->bind_param('i', $id);
        $originalQuery->execute();
        $originalResult = $originalQuery->get_result();
        $originalData = $originalResult->fetch_assoc();

        // Check if changing from walk-in to registered
        $requiresPassword = ($originalType === 'walk-in' && $type === 'registered');

        // Validate password if required or provided
        if ($requiresPassword && empty($password)) {
            $_SESSION['error'] = 'Password is required when changing from Walk-In to Registered customer.';
            header('Location: ../customers.php');
            exit;
        }

        if (!empty($password)) {
            // Validate password length
            if (strlen($password) < 8) {
                $_SESSION['error'] = 'Password must be at least 8 characters long.';
                header('Location: ../customers.php');
                exit;
            }

            // Validate password match
            if ($password !== $confirmPassword) {
                $_SESSION['error'] = 'Passwords do not match.';
                header('Location: ../customers.php');
                exit;
            }

            // Update with password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name = ?, contact_num = ?, email = ?, type = ?, password = ? WHERE id = ?");
            $stmt->bind_param('sssssi', $name, $contactNum, $email, $type, $hashedPassword, $id);
        } else {
            // Update without password
            $stmt = $conn->prepare("UPDATE users SET name = ?, contact_num = ?, email = ?, type = ? WHERE id = ?");
            $stmt->bind_param('ssssi', $name, $contactNum, $email, $type, $id);
        }

        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // AUDIT LOGGING - Log user update
            if (file_exists(__DIR__ . '/audit-logger.php')) {
                $changes = [];

                if ($originalData['name'] !== $name) {
                    $changes[] = "name: {$originalData['name']} → {$name}";
                }

                if ($originalData['email'] !== $email) {
                    $changes[] = "email: {$originalData['email']} → {$email}";
                }

                if ($originalData['contact_num'] !== $contactNum) {
                    $changes[] = "contact number: {$originalData['contact_num']} → {$contactNum}";
                }

                if ($originalData['type'] !== $type) {
                    $changes[] = "type: {$originalData['type']} → {$type}";
                }

                if (!empty($password)) {
                    $changes[] = "password: updated";
                }

                $auditDescription = "Updated customer {$originalData['name']}: " . implode(', ', $changes);
                logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'customer_updated', $auditDescription);
            }

            $_SESSION['success'] = 'User updated successfully.';
        } else {
            $_SESSION['success'] = 'No changes were made.';
        }

        header('Location: ../customers.php');
        exit;
    } catch (Exception $e) {
        // AUDIT LOGGING - Log error
        if (file_exists(__DIR__ . '/audit-logger.php')) {
            $errorDescription = 'Failed to update customer: ' . $e->getMessage();
            logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'customer_error', $errorDescription);
        }

        $_SESSION['error'] = 'Error updating user: ' . $e->getMessage();
        header('Location: ../customers.php');
        exit;
    }
}
