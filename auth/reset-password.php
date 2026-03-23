<?php
session_start();
require_once '../config/db_conn.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function renderAlert($type, $message)
{
    if (!empty($message)) {
        $icon = $type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
        $bgColor = $type === 'success' ? 'bg-success' : 'bg-danger';
        echo '
        <div class="alert ' . $bgColor . ' text-white alert-dismissible fade show mt-3 animate__animated animate__fadeInDown" role="alert" style="border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
            <i class="' . $icon . ' me-2"></i>
            <span>' . htmlspecialchars($message) . '</span>
        </div>';
    }
}

function getRedirectUrl($role)
{
    switch ($role) {
        case 'admin':
            return 'unified-login.php';
        case 'staff':
            return 'unified-login.php';
        case 'customer':
            return 'unified-login.php';
        default:
            return 'unified-login.php';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $token = $_GET['token'] ?? '';
    $role = $_GET['role'] ?? '';

    if (!empty($token) && !empty($role)) {
        $validRoles = ['admin', 'staff', 'customer'];
        if (!in_array($role, $validRoles)) {
            $_SESSION['error'] = "Invalid user role. Please request a new password reset.";
        } else {
            $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND role = ? AND expires_at > NOW() AND used = 0");
            $stmt->bind_param("ss", $token, $role);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $_SESSION['error'] = "Invalid or expired reset token. Please request a new password reset.";
            } else {
                $tokenData = $result->fetch_assoc();
                $_SESSION['reset_email'] = $tokenData['email'];
                $_SESSION['reset_token'] = $token;
                $_SESSION['reset_role'] = $role;
            }
            $stmt->close();
        }
    } else {
        $_SESSION['error'] = "Invalid reset link. Please check your email.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Invalid CSRF token. Please try again.";
    } else {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $resetToken = $_SESSION['reset_token'] ?? '';
        $resetRole = $_SESSION['reset_role'] ?? '';
        $email = $_SESSION['reset_email'] ?? '';

        if (strlen($password) < 8) {
            $_SESSION['error'] = "Password must be at least 8 characters long.";
        } elseif ($password !== $confirmPassword) {
            $_SESSION['error'] = "Passwords do not match.";
        } elseif (empty($resetToken) || empty($resetRole) || empty($email)) {
            $_SESSION['error'] = "Invalid reset session. Please try again.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $updateSuccess = false;
            if ($resetRole === 'admin') {
                $updateStmt = $conn->prepare("UPDATE admins SET password = ? WHERE email = ?");
            } elseif ($resetRole === 'staff') {
                $updateStmt = $conn->prepare("UPDATE staffs SET password = ? WHERE email = ?");
            } elseif ($resetRole === 'customer') {
                $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            } else {
                $_SESSION['error'] = "Invalid user role.";
            }

            if (isset($updateStmt)) {
                $updateStmt->bind_param("ss", $hashedPassword, $email);

                if ($updateStmt->execute()) {
                    $markUsedStmt = $conn->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
                    $markUsedStmt->bind_param("s", $resetToken);
                    $markUsedStmt->execute();
                    $markUsedStmt->close();

                    $_SESSION['success'] = "Password has been reset successfully. You can now login with your new password.";
                    $_SESSION['redirect_role'] = $resetRole;

                    unset($_SESSION['reset_email'], $_SESSION['reset_token'], $_SESSION['reset_role']);
                } else {
                    $_SESSION['error'] = "Failed to update password. Please try again.";
                }
                $updateStmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <title>Reset Password</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../assets/css/reset-password.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-5">
                <div class="reset-password-card card">
                    <div class="card-header">
                        <a href="/">
                            <img src="../logo.jpg" alt="Laundry Management System Logo" class="img-fluid mb-3" style="max-height: 80px;">
                        </a>
                        <h3 class="fw-bold" style="color: var(--primary-color) !important;">Reset Password</h3>
                        <p class="text-muted">Enter your new password</p>
                    </div>

                    <div class="card-body p-4">
                        <?php if (isset($_SESSION['success'])): ?>
                            <?php renderAlert('success', $_SESSION['success']); ?>
                            <?php
                            $redirectRole = $_SESSION['redirect_role'] ?? 'admin';
                            unset($_SESSION['success'], $_SESSION['redirect_role']);
                            ?>
                            <script>
                                setTimeout(function() {
                                    window.location.href = '<?php echo getRedirectUrl($redirectRole); ?>';
                                }, 3000);
                            </script>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error'])): ?>
                            <?php renderAlert('error', $_SESSION['error']); ?>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>

                        <?php if (!isset($_SESSION['success'])): ?>
                            <form method="POST" action="reset-password.php">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                                <div class="mb-4">
                                    <label for="password" class="form-label">New Password</label>
                                    <div class="input-group">
                                        <input id="password"
                                            type="password"
                                            name="password"
                                            required
                                            class="form-control"
                                            placeholder="Enter your new password"
                                            minlength="8">
                                        <span class="input-group-text password-toggle" onclick="togglePassword('password', 'toggleIcon1')">
                                            <i id="toggleIcon1" class="fas fa-eye text-secondary"></i>
                                        </span>
                                    </div>
                                    <div class="form-text">
                                        Password must be at least 8 characters long.
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label">Confirm Password</label>
                                    <div class="input-group">
                                        <input id="confirm_password"
                                            type="password"
                                            name="confirm_password"
                                            required
                                            class="form-control"
                                            placeholder="Confirm your new password"
                                            minlength="8">
                                        <span class="input-group-text password-toggle" onclick="togglePassword('confirm_password', 'toggleIcon2')">
                                            <i id="toggleIcon2" class="fas fa-eye text-secondary"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-sync-alt me-2"></i>Reset Password
                                    </button>
                                </div>

                                <div class="text-center">
                                    <?php
                                    $backUrl = isset($_SESSION['reset_role']) ? getRedirectUrl($_SESSION['reset_role']) : '../auth/unified-login.php';
                                    ?>
                                    <a href="<?php echo $backUrl; ?>" class="btn btn-link">
                                        <i class="fas fa-arrow-left me-2"></i>Back to Login
                                    </a>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="text-center">
                                <p class="text-muted">Redirecting to login page...</p>
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="footer p-3">
                        <p class="mb-0 small text-muted"><?php echo date('Y'); ?> Laundry Management System</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId, iconId) {
            const passwordField = document.getElementById(fieldId);
            const toggleIcon = document.getElementById(iconId);

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;

            if (confirmPassword && password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });

        document.getElementById('password').addEventListener('input', function() {
            const confirmPassword = document.getElementById('confirm_password');
            if (confirmPassword.value) {
                confirmPassword.dispatchEvent(new Event('input'));
            }
        });
    </script>
</body>

</html>