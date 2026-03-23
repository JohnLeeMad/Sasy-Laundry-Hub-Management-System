<?php
// customer-auth-checker.php - Updated for unified login
require_once '../config/db_conn.php';

function autoLoginCustomer($conn)
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

            // Set session variables (consistent with unified login)
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = 'customer';

            // Customer-specific session variables
            $_SESSION['customer_logged_in'] = true;
            $_SESSION['customer_id'] = $user['id'];
            $_SESSION['customer_name'] = $user['name'];
            $_SESSION['customer_email'] = $user['email'];

            // Refresh token (optional security measure)
            $new_token = bin2hex(random_bytes(32));
            $expires = time() + (30 * 24 * 60 * 60);

            $update_stmt = $conn->prepare("UPDATE customer_tokens SET token = ?, expires_at = ? WHERE token = ?");
            $update_stmt->bind_param("sss", $new_token, date('Y-m-d H:i:s', $expires), $token);
            $update_stmt->execute();

            // Update cookie
            $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
            setcookie('customer_remember', $new_token, $expires, '/', '', $secure, true);

            return true;
        } else {
            // Invalid token, clear cookie
            setcookie('customer_remember', '', time() - 3600, '/', '', true, true);
        }
    }
    return false;
}

// Run auto-login check
autoLoginCustomer($conn);
