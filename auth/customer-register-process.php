<?php
session_start();

// Database connection
require_once '../config/db_conn.php';
require_once 'phpmailer/src/PHPMailer.php';
require_once 'phpmailer/src/SMTP.php';
require_once 'phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

date_default_timezone_set('Asia/Manila');

// CSRF Token Validation
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['error'] = 'Invalid CSRF token. Please try again.';
    header('Location: customer-register.php');
    exit();
}

// Input Validation
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$contact_num = trim($_POST['contact_num'] ?? '');
$terms = isset($_POST['terms']);

// Store old input for re-population
$_SESSION['old'] = [
    'name' => $name,
    'email' => $email,
    'contact_num' => $contact_num
];

// Validate required fields
if (empty($name) || empty($email) || empty($contact_num)) {
    $_SESSION['error'] = 'All fields are required.';
    header('Location: customer-register.php');
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Please enter a valid email address.';
    header('Location: customer-register.php');
    exit();
}

// Validate terms acceptance
if (!$terms) {
    $_SESSION['error'] = 'You must agree to the Terms of Service and Privacy Policy.';
    header('Location: customer-register.php');
    exit();
}

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['error'] = 'An account with this email already exists.';
    header('Location: customer-register.php');
    exit();
}

// Add check for existing contact number
$stmt = $conn->prepare("SELECT id FROM users WHERE contact_num = ? LIMIT 1");
$stmt->bind_param("s", $contact_num);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['error'] = 'An account with this contact number already exists.';
    header('Location: customer-register.php');
    exit();
}

// Generate verification token
$verification_token = bin2hex(random_bytes(32));
$token_expires = date('Y-m-d H:i:s', strtotime('+24 hours')); // Token expires in 24 hours

// Insert user into database (without password, pending verification)
$stmt = $conn->prepare("INSERT INTO users (name, email, contact_num, type, verification_token, token_expires, created_at) VALUES (?, ?, ?, 'registered', ?, ?, NOW())");
$stmt->bind_param("sssss", $name, $email, $contact_num, $verification_token, $token_expires);

if (!$stmt->execute()) {
    $_SESSION['error'] = 'Registration failed. Please try again.';
    header('Location: customer-register.php');
    exit();
}

function sendVerificationEmail($email, $name, $token)
{
    try {
        $mail = new PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'jonleemad17@gmail.com';
        $mail->Password   = 'ylaf cxiu kpxt qbdi'; // Replace with new app password if needed
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Gmail SSL/TLS settings
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Recipients
        $mail->setFrom('no-reply@sasylaundry.neustpenaranda.online', 'Sasy Laundry Hub');
        $mail->addAddress($email, $name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Verify Your Account - Sasy Laundry Hub";

        // Updated verification link with your live domain
        $verification_link = "https://sasylaundry.neustpenaranda.online/auth/verify-email.php?token=" . $token;

        $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #007bff;'>Welcome to Sasy Laundry Hub!</h2>
            <p>Hello " . htmlspecialchars($name) . ",</p>
            <p>Thank you for registering with us. To complete your registration, please verify your email address by clicking the button below:</p>
            
            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . $verification_link . "' style='background-color: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>Verify Email Address</a>
            </div>
            
            <p>If the button doesn't work, you can also copy and paste the following link into your browser:</p>
            <p><a href='" . $verification_link . "'>" . $verification_link . "</a></p>
            
            <p><strong>Note:</strong> This verification link will expire in 24 hours.</p>
            
            <p>If you didn't create this account, please ignore this email.</p>
            
            <p>Best regards,<br>Sasy Laundry Hub Team</p>
        </div>";

        $mail->AltBody = "Hello " . $name . ",\n\n" .
            "Please verify your email by clicking this link:\n" .
            $verification_link . "\n\n" .
            "This link will expire in 24 hours.\n\n" .
            "Best regards,\nSasy Laundry Hub Team";

        return $mail->send();
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}

// Send verification email
if (sendVerificationEmail($email, $name, $verification_token)) {
    unset($_SESSION['old']); // Clear old input data
    $_SESSION['success'] = 'Registration successful! We\'ve sent a verification email to ' . $email . '. Please check your inbox and click the verification link to complete your registration.';
    header('Location: customer-register.php');
} else {
    $_SESSION['error'] = 'Registration successful, but we couldn\'t send the verification email. Please contact support.';
    header('Location: customer-register.php');
}

exit();
