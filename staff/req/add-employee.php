<?php
// filepath: c:\xampp\htdocs\laundry-v4\admin\req\add-employee.php
session_start();
require_once '../../config/db_conn.php';

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method.';
    header('Location: ../employees.php');
    exit;
}

// Sanitize and validate input data
$name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$contact_num = trim(filter_input(INPUT_POST, 'contact_num', FILTER_SANITIZE_STRING));
$role = trim(filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING));
$password = trim(filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING));
$confirm_password = trim(filter_input(INPUT_POST, 'confirm_password', FILTER_SANITIZE_STRING));
$type = strtolower($role); // 'Admin' -> 'admin', 'Staff' -> 'staff'

// Basic validation
if (empty($name) || empty($email) || empty($contact_num) || empty($role) || empty($password)) {
    $_SESSION['error'] = 'Please fill all required fields.';
    header('Location: ../employees.php');
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Invalid email format.';
    header('Location: ../employees.php');
    exit;
}

// Validate password
if (strlen($password) < 8) {
    $_SESSION['error'] = 'Password must be at least 8 characters long.';
    header('Location: ../employees.php');
    exit;
}

// Validate password match
if ($password !== $confirm_password) {
    $_SESSION['error'] = 'Passwords do not match.';
    header('Location: ../employees.php');
    exit;
}

// Validate role
if (!in_array($role, ['Admin', 'Staff'])) {
    $_SESSION['error'] = 'Invalid role specified.';
    header('Location: ../employees.php');
    exit;
}

try {
    // Determine the correct table based on role
    $table = ($role === 'Admin') ? 'admins' : 'staffs';

    // Check if email already exists
    $checkEmailQuery = "SELECT id FROM $table WHERE email = ?";
    $stmt = $conn->prepare($checkEmailQuery);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = 'Email already exists.';
        header('Location: ../employees.php');
        exit;
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Get current timestamp
    $currentTime = date('Y-m-d H:i:s');

    // Insert the new employee
    $insertQuery = "INSERT INTO $table 
                   (name, contact_num, email, password, created_at, updated_at) 
                   VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param('ssssss', $name, $contact_num, $email, $hashedPassword, $currentTime, $currentTime);

    if ($stmt->execute()) {
        $_SESSION['success'] = ucfirst($type) . ' added successfully!';
    } else {
        $_SESSION['error'] = 'Failed to add ' . $type . ': ' . $conn->error;
    }

    $stmt->close();
    $conn->close();

    header('Location: ../employees.php');
    exit;
} catch (Exception $e) {
    $_SESSION['error'] = 'An error occurred: ' . $e->getMessage();
    header('Location: ../employees.php');
    exit;
}
