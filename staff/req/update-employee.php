<?php
// filepath: c:\xampp\htdocs\laundry-v4\admin\req\update-employee.php
session_start();
require_once '../../config/db_conn.php';

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setError('Invalid request method.');
    redirectToEmployees();
}

date_default_timezone_set('Asia/Manila');
$currentTime = date('Y-m-d h:i:s A'); // Example: 2025-04-29 03:45:12 PM

// Get and sanitize input data
$input = sanitizeEmployeeInput($_POST);

// Validate input data
if (!validateEmployeeInput($input)) {
    redirectToEmployees();
}

try {
    // Begin transaction in case we need to move between tables
    $conn->begin_transaction();

    // Check if email exists in either table (except for current employee)
    if (emailExistsInOtherRecords($conn, $input)) {
        setError('Email already exists for another employee.');
        redirectToEmployees();
    }

    // Update the employee record
    if (updateEmployeeRecord($conn, $input)) {
        // If role changed, move the employee to the correct table
        if (roleChanged($input)) {
            if (!moveEmployeeToCorrectTable($conn, $input)) {
                $conn->rollback();
                setError('Failed to move employee to new role table.');
                redirectToEmployees();
            }
        }

        $conn->commit();
        setSuccess('Employee updated successfully!');
    } else {
        $conn->rollback();
        setError('Failed to update employee.');
    }

    redirectToEmployees();
} catch (Exception $e) {
    $conn->rollback();
    setError('An error occurred: ' . $e->getMessage());
    redirectToEmployees();
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
        'source_table' => trim(filter_var($postData['source_table'] ?? '', FILTER_SANITIZE_STRING))
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

        // Check if passwords match
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

    foreach ($tablesToCheck as $table) {
        $query = "SELECT id FROM $table WHERE email = ? AND id != ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('si', $email, $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return true;
        }
    }

    return false;
}

/**
 * Updates the employee record in the database
 */
function updateEmployeeRecord($conn, $input)
{
    // Set timezone to Philippines
    date_default_timezone_set('Asia/Manila');

    $table = $input['source_table'];
    $query = "UPDATE $table SET 
              name = ?, email = ?, contact_num = ?, updated_at = ?";

    // Use 24-hour format for the database
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
 * Moves employee to the correct table when role changes
 */
function moveEmployeeToCorrectTable($conn, $input)
{
    $sourceTable = $input['source_table'];
    $targetTable = ($input['role'] === 'Admin') ? 'admins' : 'staffs';

    // 1. Get employee data from source table
    $selectQuery = "SELECT * FROM $sourceTable WHERE id = ?";
    $stmt = $conn->prepare($selectQuery);
    $stmt->bind_param('i', $input['id']);
    $stmt->execute();
    $employeeData = $stmt->get_result()->fetch_assoc();

    if (!$employeeData) return false;

    // 2. Insert into target table
    $insertQuery = "INSERT INTO $targetTable 
                   (name, contact_num, email, password, created_at, updated_at) 
                   VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param(
        'sssss',
        $employeeData['name'],
        $employeeData['contact_num'],
        $employeeData['email'],
        $employeeData['password'],
        $employeeData['created_at']
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
