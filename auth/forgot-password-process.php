<?php
session_start();
require_once 'phpmailer/src/PHPMailer.php';
require_once 'phpmailer/src/SMTP.php';
require_once 'phpmailer/src/Exception.php';
require_once '../config/db_conn.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

date_default_timezone_set('Asia/Manila');

function getUserRole($conn, $email)
{
    $stmt = $conn->prepare("SELECT email FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        return 'admin';
    }

    $stmt = $conn->prepare("SELECT email FROM staffs WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        return 'staff';
    }

    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        return 'customer';
    }

    return false;
}

function storeResetToken($conn, $email, $token, $role)
{
    $updateStmt = $conn->prepare("UPDATE password_resets SET used = 1 WHERE email = ? AND role = ? AND used = 0");
    $updateStmt->bind_param("ss", $email, $role);
    $updateStmt->execute();
    $updateStmt->close();

    $expiresAt = date('Y-m-d H:i:s', time() + 3600);

    $stmt = $conn->prepare("INSERT INTO password_resets (email, token, role, expires_at, used, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
    $stmt->bind_param("ssss", $email, $token, $role, $expiresAt);

    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

function sendPasswordResetEmail($email, $role, $token)
{
    try {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'jonleemad17@gmail.com';
        $mail->Password   = 'ylaf cxiu kpxt qbdi';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        $mail->setFrom('no-reply@sasylaundry.neustpenaranda.online', 'Sasy Laundry Hub');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = "Password Reset Request - Sasy Laundry Hub";

        if ($role === 'customer') {
            $greeting = "Dear Valued Customer";
            $roleDisplayName = "Customer";
        } elseif ($role === 'admin') {
            $greeting = "Dear Administrator";
            $roleDisplayName = "Administrator";
        } else {
            $greeting = "Dear Staff Member";
            $roleDisplayName = "Staff";
        }

        $resetLink = "https://sasylaundry.neustpenaranda.online/auth/reset-password.php?token=$token&role=$role";

        $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #007bff;'>Password Reset Request</h2>
            <p>$greeting,</p>
            <p>We received a request to reset your password for your $roleDisplayName account at Sasy Laundry Hub.</p>
            
            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . $resetLink . "' style='background-color: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>Reset Password</a>
            </div>
            
            <p>If the button doesn't work, you can also copy and paste the following link into your browser:</p>
            <p><a href='" . $resetLink . "'>" . $resetLink . "</a></p>
            
            <p><strong>Important:</strong></p>
            <ul>
                <li>This link will expire in 1 hour for security reasons</li>
                <li>If you didn't request this password reset, please ignore this email</li>
                <li>For security, do not share this link with anyone</li>
            </ul>
            
            <p>If you continue to have trouble, please contact our support team.</p>
            <p>Best regards,<br>Sasy Laundry Hub Team</p>
        </div>";

        $mail->AltBody = "$greeting,\n\n" .
            "We received a request to reset your password for your $roleDisplayName account at Sasy Laundry Hub.\n\n" .
            "Click this link to reset your password:\n$resetLink\n\n" .
            "Important:\n" .
            "- This link will expire in 1 hour\n" .
            "- If you didn't request this, please ignore this email\n" .
            "- Do not share this link with anyone\n\n" .
            "Best regards,\nSasy Laundry Hub Team";

        return $mail->send();
    } catch (Exception $e) {
        error_log("Password reset email sending failed: " . $e->getMessage());
        return false;
    }
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $source = $_POST['source'] ?? 'admin';
    $_SESSION['error'] = "Invalid CSRF token. Please try again.";
    header("Location: forgot-password.php?source=" . urlencode($source));
    exit();
}

$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$source = $_POST['source'] ?? 'admin';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Invalid email format";
    header("Location: forgot-password.php?source=" . urlencode($source));
    exit();
}

$userRole = getUserRole($conn, $email);

if (!$userRole) {
    $_SESSION['error'] = "No account found with that email address.";
    header("Location: forgot-password.php?source=" . urlencode($source));
    exit();
}

if ($source === 'customer' && $userRole !== 'customer') {
    $_SESSION['error'] = "No customer account found with that email address.";
    header("Location: forgot-password.php?source=" . urlencode($source));
    exit();
} elseif ($source === 'admin' && $userRole === 'customer') {
    $_SESSION['error'] = "No admin/staff account found with that email address.";
    header("Location: forgot-password.php?source=" . urlencode($source));
    exit();
}

try {
    $resetToken = bin2hex(random_bytes(32));

    if (!storeResetToken($conn, $email, $resetToken, $userRole)) {
        throw new Exception("Failed to store reset token in database.");
    }

    if (sendPasswordResetEmail($email, $userRole, $resetToken)) {
        $_SESSION['success'] = "Password reset link has been sent to your email address. Please check your inbox and follow the instructions.";
        header("Location: forgot-password.php?source=" . urlencode($source));
        exit();
    } else {
        throw new Exception("Failed to send reset email.");
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Failed to send reset email. Please try again later.";
    error_log("Password reset process error: " . $e->getMessage());
    header("Location: forgot-password.php?source=" . urlencode($source));
    exit();
}