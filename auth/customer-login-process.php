<?php
session_start();

// Database connection
require_once '../config/db_conn.php';

// Function to redirect with error
function redirectWithError($message, $location = '../auth/customer-login.php')
{
    $_SESSION['error'] = $message;
    $_SESSION['old']['email'] = $_POST['email'] ?? '';
    header("Location: $location");
    exit();
}

// Function to check if user is blocked
function isUserBlocked($email, $conn)
{
    $stmt = $conn->prepare("SELECT failed_attempts, last_failed_at FROM failed_logins WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $failedAttempts = $row['failed_attempts'];
        $lastFailedAt = strtotime($row['last_failed_at']);
        $blockDuration = 15 * 60; // 15 minutes
        $maxAttempts = 5;

        if ($failedAttempts >= $maxAttempts && (time() - $lastFailedAt) < $blockDuration) {
            return true;
        }

        $remainingAttempts = $maxAttempts - $failedAttempts;
        if ($remainingAttempts <= 3) {
            $_SESSION['warning'] = "Warning: You have $remainingAttempts login attempts remaining.";
        }
    }
    return false;
}

// Function to record failed login
function recordFailedLogin($email, $conn)
{
    $stmt = $conn->prepare("INSERT INTO failed_logins (email, failed_attempts, last_failed_at) 
                           VALUES (?, 1, NOW()) 
                           ON DUPLICATE KEY UPDATE 
                           failed_attempts = failed_attempts + 1, 
                           last_failed_at = NOW()");
    $stmt->bind_param("s", $email);
    $stmt->execute();
}

// Function to reset failed logins
function resetFailedLogins($email, $conn)
{
    $stmt = $conn->prepare("DELETE FROM failed_logins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
}

// Function to set session variables with consistent naming
function setCustomerSessionVariables($user, $conn)
{
    unset($_SESSION['old']); // Clear old data

    // Common session variables (shared with admin/staff system)
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = 'customer';
    $_SESSION['success'] = 'Customer login successful!';

    // Customer-specific session variables (these should be used in customer pages)
    $_SESSION['customer_logged_in'] = true;
    $_SESSION['customer_id'] = $user['id'];
    $_SESSION['customer_name'] = $user['name'];
    $_SESSION['customer_email'] = $user['email'];
}

// Function to create remember token
function createRememberToken($user_id, $conn)
{
    // Generate secure token
    $token = bin2hex(random_bytes(32));
    $expires = time() + (30 * 24 * 60 * 60); // 30 days

    // Clean up any expired tokens first
    $cleanup_stmt = $conn->prepare("DELETE FROM customer_tokens WHERE expires_at < NOW()");
    $cleanup_stmt->execute();

    // Insert new token
    $stmt = $conn->prepare("INSERT INTO customer_tokens (customer_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $token, date('Y-m-d H:i:s', $expires));

    if ($stmt->execute()) {
        // Set cookie - secure, httponly, with proper domain/path
        $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        setcookie('customer_remember', $token, $expires, '/', '', $secure, true);
        return true;
    }
    return false;
}

// Function to validate remember token and auto-login
function validateRememberToken($conn)
{
    if (isset($_COOKIE['customer_remember']) && !isset($_SESSION['customer_logged_in'])) {
        $token = $_COOKIE['customer_remember'];

        // Clean up expired tokens
        $cleanup_stmt = $conn->prepare("DELETE FROM customer_tokens WHERE expires_at < NOW()");
        $cleanup_stmt->execute();

        // Validate token
        $stmt = $conn->prepare("SELECT ct.customer_id, u.id, u.name, u.email, u.type 
                               FROM customer_tokens ct 
                               JOIN users u ON ct.customer_id = u.id 
                               WHERE ct.token = ? AND ct.expires_at > NOW() 
                               AND u.type = 'registered' 
                               AND u.verification_token IS NULL 
                               LIMIT 1");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Set session variables
            setCustomerSessionVariables($user, $conn);

            // Refresh token (optional security measure)
            createRememberToken($user['id'], $conn);

            return true;
        } else {
            // Invalid token, clear cookie
            setcookie('customer_remember', '', time() - 3600, '/', '', true, true);
        }
    }
    return false;
}

// CSRF Token Validation
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    redirectWithError('Invalid CSRF token. Please try again.');
}

// Input Validation
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    redirectWithError('Email and password are required.');
}

// Check if user is blocked
if (isUserBlocked($email, $conn)) {
    redirectWithError('Too many failed login attempts. Please try again after 15 minutes.');
}

// Prepare SQL to prevent injection
$stmt = $conn->prepare("SELECT id, name, email, password, type 
                       FROM users 
                       WHERE email = ? 
                       AND type = 'registered' 
                       AND verification_token IS NULL 
                       LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists
if ($result->num_rows === 0) {
    recordFailedLogin($email, $conn);
    redirectWithError('Invalid email or password, or account not verified.');
}

// Verify password
$user = $result->fetch_assoc();
if (!password_verify($password, $user['password'])) {
    recordFailedLogin($email, $conn);
    redirectWithError('Invalid email or password.');
}

// Reset failed login attempts on successful login
resetFailedLogins($email, $conn);

// Set session variables
setCustomerSessionVariables($user, $conn);

// Create remember token if "Remember me" is checked
if (isset($_POST['remember']) && $_POST['remember'] === 'on') {
    createRememberToken($user['id'], $conn);
}

// Redirect to customer dashboard
header('Location: ../customer/index.php');
exit();
