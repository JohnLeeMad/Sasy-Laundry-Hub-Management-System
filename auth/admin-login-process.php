<?php
session_start();

// Database connection
require_once '../config/db_conn.php';

date_default_timezone_set('Asia/Manila');

// Function to redirect with an error message
function redirectWithError($message, $location = '../auth/admin-login.php')
{
    $_SESSION['error'] = $message;
    header("Location: $location");
    exit();
}

// Function to validate CSRF token
function validateCsrfToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Function to preserve form data
function preserveFormData($email)
{
    $_SESSION['old']['email'] = $email;
}

// Function to check if user is super admin
function isSuperAdmin($email, $conn)
{
    $stmt = $conn->prepare("SELECT id, name, email, password, 'super_admin' as role FROM super_admins WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    return false;
}

// Function to check if user is admin (with archived check)
function isAdmin($email, $conn)
{
    // Check if archived column exists, if not, assume not archived
    $stmt = $conn->prepare("SELECT id, name, email, password, 'admin' as role, 
                           COALESCE(archived, 0) as archived 
                           FROM admins WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Check if account is archived
        if ($user['archived'] == 1) {
            return ['archived' => true];
        }

        return $user;
    }

    return false;
}

// Function to check if user is staff (with archived check)
function isStaff($email, $conn)
{
    // Check if archived column exists, if not, assume not archived
    $stmt = $conn->prepare("SELECT id, name, email, password, 'staff' as role, 
                           COALESCE(archived, 0) as archived 
                           FROM staffs WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Check if account is archived
        if ($user['archived'] == 1) {
            return ['archived' => true];
        }

        return $user;
    }

    return false;
}

// Function to set session variables for the logged-in user
function setSessionVariables($user, $role)
{
    unset($_SESSION['old']); // Clear old data

    // Common session variables for all users
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $role;
    $_SESSION['success'] = ucfirst(str_replace('_', ' ', $role)) . ' login successful!';

    // AUDIT LOGGING - Log successful login
    if (file_exists('../admin/req/audit-logger.php')) {
        include '../admin/req/audit-logger.php';
        logActivity($user['id'], $role, $user['name'], 'login', 'User logged in successfully');
    }

    // Role-specific session variables and redirects
    if ($role === 'super_admin') {
        $_SESSION['super_admin_logged_in'] = true;
        $_SESSION['super_admin_id'] = $user['id'];
        header('Location: ../super-admin/dashboard.php');
    } else if ($role === 'admin') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $user['id'];
        header('Location: ../admin/dashboard.php');
    } else {
        $_SESSION['staff_logged_in'] = true;
        $_SESSION['staff_id'] = $user['id'];
        header('Location: ../staff/dashboard.php');
    }

    exit();
}

// CSRF Token Validation
if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    redirectWithError('Invalid CSRF token. Please try again.');
}

// Input Validation
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

preserveFormData($email);

if (empty($email) || empty($password)) {
    redirectWithError('Email and password are required.');
}

// Function to check if the user is blocked
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

        // Check if the user is blocked
        if ($failedAttempts >= $maxAttempts && (time() - $lastFailedAt) < $blockDuration) {
            return true; // User is blocked
        }

        // Set a warning if attempts are 3 or fewer
        $remainingAttempts = $maxAttempts - $failedAttempts;
        if ($remainingAttempts <= 3) {
            $_SESSION['warning'] = "Warning: You have $remainingAttempts login attempts remaining.";
        }
    }

    return false;
}

// Function to record a failed login attempt
function recordFailedLogin($email, $conn)
{
    $stmt = $conn->prepare("INSERT INTO failed_logins (email, failed_attempts, last_failed_at) 
                            VALUES (?, 1, NOW()) 
                            ON DUPLICATE KEY UPDATE 
                            failed_attempts = failed_attempts + 1, 
                            last_failed_at = NOW()");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    // Removed audit logging for failed attempts to avoid database errors
}

// Function to reset failed login attempts on successful login
function resetFailedLogins($email, $conn)
{
    $stmt = $conn->prepare("DELETE FROM failed_logins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
}

// Check if the user is blocked
if (isUserBlocked($email, $conn)) {
    // Removed audit logging for blocked attempts to avoid database errors
    redirectWithError('Too many failed login attempts. Please try again after 15 minutes.');
}

// Check user in all tables (super_admin has highest priority, then admin, then staff)
$user = isSuperAdmin($email, $conn);
if ($user) {
    if (password_verify($password, $user['password'])) {
        // Successful super admin login
        resetFailedLogins($email, $conn);
        setSessionVariables($user, 'super_admin');
    } else {
        recordFailedLogin($email, $conn);
        redirectWithError('Invalid email or password.');
    }
}

// Check if user is admin
$user = isAdmin($email, $conn);
if ($user) {
    // Check if account is archived
    if (isset($user['archived']) && $user['archived'] === true) {
        // Log the archived account login attempt
        if (file_exists('../admin/req/audit-logger.php')) {
            include '../admin/req/audit-logger.php';
            // Try to get user info for logging, but use email if ID not available
            logActivity(0, 'admin', $email, 'failed_login', 'Attempted login to archived account');
        }
        redirectWithError('This account has been archived and cannot be used to log in. Please contact an administrator.');
    }

    if (password_verify($password, $user['password'])) {
        // Successful admin login
        resetFailedLogins($email, $conn);
        setSessionVariables($user, 'admin');
    } else {
        recordFailedLogin($email, $conn);
        redirectWithError('Invalid email or password.');
    }
}

// Check if user is staff
$user = isStaff($email, $conn);
if ($user) {
    // Check if account is archived
    if (isset($user['archived']) && $user['archived'] === true) {
        // Log the archived account login attempt
        if (file_exists('../admin/req/audit-logger.php')) {
            include '../admin/req/audit-logger.php';
            // Try to get user info for logging, but use email if ID not available
            logActivity(0, 'staff', $email, 'failed_login', 'Attempted login to archived account');
        }
        redirectWithError('This account has been archived and cannot be used to log in. Please contact an administrator.');
    }

    if (password_verify($password, $user['password'])) {
        // Successful staff login
        resetFailedLogins($email, $conn);
        setSessionVariables($user, 'staff');
    } else {
        recordFailedLogin($email, $conn);
        redirectWithError('Invalid email or password.');
    }
}

// If no user found in any table
recordFailedLogin($email, $conn);
redirectWithError('Invalid email or password.');
