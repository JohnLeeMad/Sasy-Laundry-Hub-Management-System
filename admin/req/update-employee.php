<?php
session_start();
require_once '../../config/db_conn.php';
require_once __DIR__ . '/audit-logger.php';

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setError('Invalid request method.');
    redirectToEmployees();
}

date_default_timezone_set('Asia/Manila');

// Get and sanitize input data
$input = sanitizeEmployeeInput($_POST);

// Validate input data
if (!validateEmployeeInput($input)) {
    redirectToEmployees();
}

// ⭐ NEW: PROTECTION - Prevent self role change
$currentUserId = $_SESSION['user_id'] ?? null;
$currentUserRole = $_SESSION['user_role'] ?? null;
$currentTable = ($currentUserRole === 'admin') ? 'admins' : 'staffs';

$isOwnAccount = ($input['id'] == $currentUserId && $input['source_table'] == $currentTable);

if ($isOwnAccount && roleChanged($input)) {
    setError('You cannot change your own role.');
    redirectToEmployees();
}
// ⭐ END NEW CODE

try {
    $conn->begin_transaction();

    // Check if this would remove the last admin
    if (roleChanged($input) && $input['role'] === 'Staff') {
        $currentRole = ucfirst($input['type']);
        if ($currentRole === 'Admin') {
            // Count remaining active admins (excluding archived)
            $countQuery = "SELECT COUNT(*) as admin_count FROM admins WHERE archived = 0";
            $countResult = $conn->query($countQuery);
            $adminCount = $countResult->fetch_assoc()['admin_count'];

            if ($adminCount <= 1) {
                $conn->rollback();
                setError('Cannot change role. There must be at least one Admin account in the system.');
                redirectToEmployees();
            }
        }
    }

    // Check if email exists in other records
    if (emailExistsInOtherRecords($conn, $input)) {
        $conn->rollback();
        setError('Email already exists for another user.');
        redirectToEmployees();
    }

    // Get original user data for audit log
    $originalData = getUserOriginalData($conn, $input);

    // Update the user record
    if (updateUserRecord($conn, $input)) {
        // If role changed, move the user to the correct table
        if (roleChanged($input)) {
            if (!moveUserToCorrectTable($conn, $input)) {
                $conn->rollback();
                setError('Failed to move user to new role table.');
                redirectToEmployees();
            }
        }

        // AUDIT LOGGING
        if (file_exists(__DIR__ . '/audit-logger.php')) {
            $auditDescription = generateAuditDescription($originalData, $input, $isOwnAccount);
            logActivity(
                $_SESSION['user_id'],
                $_SESSION['user_role'],
                $_SESSION['user_name'],
                'user_updated',
                $auditDescription
            );
        }

        $conn->commit();
        setSuccess($isOwnAccount ? 'Your account has been updated successfully!' : 'User updated successfully!');
    } else {
        $conn->rollback();
        setError('Failed to update user.');
    }

    redirectToEmployees();
} catch (Exception $e) {
    $conn->rollback();

    // AUDIT LOGGING - Log error
    if (file_exists(__DIR__ . '/audit-logger.php')) {
        $errorDescription = 'Error updating user: ' . $e->getMessage();
        logActivity(
            $_SESSION['user_id'],
            $_SESSION['user_role'],
            $_SESSION['user_name'],
            'user_error',
            $errorDescription
        );
    }

    setError('An error occurred: ' . $e->getMessage());
    redirectToEmployees();
}

/**
 * Get original user data for audit logging
 */
function getUserOriginalData($conn, $input)
{
    $table = $input['source_table'];
    $query = "SELECT name, email, contact_num, password FROM $table WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $input['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Generate audit description based on changes
 */
function generateAuditDescription($originalData, $input, $isOwnAccount)
{
    $changes = [];

    if ($originalData['name'] !== $input['name']) {
        $changes[] = "name: {$originalData['name']} → {$input['name']}";
    }

    if ($originalData['email'] !== $input['email']) {
        $changes[] = "email: {$originalData['email']} → {$input['email']}";
    }

    if ($originalData['contact_num'] !== $input['contact_num']) {
        $changes[] = "contact number: {$originalData['contact_num']} → {$input['contact_num']}";
    }

    if (roleChanged($input)) {
        $changes[] = "role: {$input['type']} → {$input['role']}";
    }

    if (!empty($input['password'])) {
        $changes[] = "password: updated";
    }

    $prefix = $isOwnAccount ? 'Updated own account' : "Updated user {$originalData['name']}";
    $description = $prefix . ': ' . implode(', ', $changes);
    return $description;
}

/**
 * Sanitizes employee input data
 */
function sanitizeEmployeeInput($postData)
{
    return [
        'id' => filter_var($postData['id'] ?? null, FILTER_VALIDATE_INT),
        'name' => trim(filter_var($postData['name'] ?? '', FILTER_SANITIZE_STRING)),
        'email' => filter_var($postData['email'] ?? '', FILTER_SANITIZE_EMAIL),
        'contact_num' => trim(filter_var($postData['contact_num'] ?? '', FILTER_SANITIZE_STRING)),
        'role' => trim(filter_var($postData['role'] ?? '', FILTER_SANITIZE_STRING)),
        'password' => trim(filter_var($postData['password'] ?? '', FILTER_SANITIZE_STRING)),
        'confirm_password' => trim(filter_var($postData['confirm_password'] ?? '', FILTER_SANITIZE_STRING)),
        'type' => trim(filter_var($postData['type'] ?? '', FILTER_SANITIZE_STRING)),
        'source_table' => trim(filter_var($postData['source_table'] ?? '', FILTER_SANITIZE_STRING)),
        'is_own_account' => filter_var($postData['is_own_account'] ?? '0', FILTER_SANITIZE_STRING)
    ];
}

/**
 * Validates employee input data
 */
function validateEmployeeInput($input)
{
    // Check required fields
    if (
        !$input['id'] || !$input['name'] || !$input['email'] ||
        !$input['contact_num'] || !$input['role'] || !$input['source_table']
    ) {
        setError('Please fill all required fields.');
        return false;
    }

    // Validate email format
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        setError('Invalid email format.');
        return false;
    }

    // Validate password if provided
    if (!empty($input['password'])) {
        if (strlen($input['password']) < 8) {
            setError('Password must be at least 8 characters long.');
            return false;
        }

        if ($input['password'] !== $input['confirm_password']) {
            setError('Passwords do not match.');
            return false;
        }
    }

    // Validate role
    if (!in_array($input['role'], ['Admin', 'Staff'])) {
        setError('Invalid role specified.');
        return false;
    }

    return true;
}

/**
 * Checks if email exists in other records
 */
function emailExistsInOtherRecords($conn, $input)
{
    $tablesToCheck = ['admins', 'staffs'];
    $email = $input['email'];
    $id = $input['id'];
    $sourceTable = $input['source_table'];

    foreach ($tablesToCheck as $table) {
        // Skip checking the same record
        if ($table === $sourceTable) {
            $query = "SELECT id FROM $table WHERE email = ? AND id != ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('si', $email, $id);
        } else {
            // Check other tables for the email
            $query = "SELECT id FROM $table WHERE email = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('s', $email);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return true;
        }
    }

    return false;
}

/**
 * Updates the user record in the database
 */
function updateUserRecord($conn, $input)
{
    date_default_timezone_set('Asia/Manila');

    $table = $input['source_table'];
    $query = "UPDATE $table SET 
              name = ?, email = ?, contact_num = ?, updated_at = ?";

    $params = [$input['name'], $input['email'], $input['contact_num'], date('Y-m-d H:i:s')];
    $paramTypes = 'ssss';

    // Add password to update if provided
    if (!empty($input['password'])) {
        $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
        $query .= ", password = ?";
        $params[] = $hashedPassword;
        $paramTypes .= 's';
    }

    $query .= " WHERE id = ?";
    $params[] = $input['id'];
    $paramTypes .= 'i';

    $stmt = $conn->prepare($query);
    $stmt->bind_param($paramTypes, ...$params);

    return $stmt->execute();
}

/**
 * Checks if the role has changed
 */
function roleChanged($input)
{
    $currentRole = ucfirst($input['type']); // 'admin' -> 'Admin'
    $newRole = $input['role'];

    return $currentRole !== $newRole;
}

/**
 * Moves user to the correct table when role changes
 */
function moveUserToCorrectTable($conn, $input)
{
    $sourceTable = $input['source_table'];
    $targetTable = ($input['role'] === 'Admin') ? 'admins' : 'staffs';

    // 1. Get user data from source table
    $selectQuery = "SELECT * FROM $sourceTable WHERE id = ?";
    $stmt = $conn->prepare($selectQuery);
    $stmt->bind_param('i', $input['id']);
    $stmt->execute();
    $userData = $stmt->get_result()->fetch_assoc();

    if (!$userData) return false;

    // 2. Insert into target table
    $insertQuery = "INSERT INTO $targetTable 
                   (name, contact_num, email, password, created_at, updated_at) 
                   VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param(
        'sssss',
        $userData['name'],
        $userData['contact_num'],
        $userData['email'],
        $userData['password'],
        $userData['created_at']
    );

    if (!$stmt->execute()) return false;

    // 3. Delete from source table
    $deleteQuery = "DELETE FROM $sourceTable WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param('i', $input['id']);

    return $stmt->execute();
}

/**
 * Sets error message in session
 */
function setError($message)
{
    $_SESSION['error'] = htmlspecialchars($message);
}

/**
 * Sets success message in session
 */
function setSuccess($message)
{
    $_SESSION['success'] = htmlspecialchars($message);
}

/**
 * Redirects to employees page
 */
function redirectToEmployees()
{
    header('Location: ../employees.php');
    exit;
}
