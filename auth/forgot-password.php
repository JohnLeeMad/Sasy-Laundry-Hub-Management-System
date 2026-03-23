<?php
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$redirect_url = null;
if (isset($_SESSION['super_admin_logged_in']) && $_SESSION['super_admin_logged_in']) {
    $redirect_url = '../super-admin/dashboard.php';
} elseif (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    $redirect_url = '../admin/dashboard.php';
} elseif (isset($_SESSION['staff_logged_in']) && $_SESSION['staff_logged_in']) {
    $redirect_url = '../staff/dashboard.php';
} elseif (isset($_SESSION['customer_logged_in']) && $_SESSION['customer_logged_in']) {
    $redirect_url = '../customer/index.php';
}

if ($redirect_url) {
    echo '<script>window.location.replace("' . $redirect_url . '");</script>';
    exit();
}

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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <title>Forgot Password</title>
    <link rel="icon" href="../image.jpg" type="image/jpeg">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">

    <link href="../assets/css/forgot-password.css" rel="stylesheet">

</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-5">
                <div class="forgot-password-card card">
                    <div class="card-header">
                        <a href="/">
                            <img src="../logo.jpg" alt="Laundry Management System Logo" class="img-fluid mb-3" style="max-height: 80px;">
                        </a>
                        <h3 class="fw-bold text-primary" style="color: var(--primary-color) !important;">Forgot Password</h3>
                        <p class="text-muted">Enter your email to reset your password</p>
                    </div>

                    <div class="card-body p-4">
                        <?php if (isset($_SESSION['success'])): ?>
                            <?php renderAlert('success', $_SESSION['success']); ?>
                            <?php unset($_SESSION['success']); ?>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error'])): ?>
                            <?php renderAlert('error', $_SESSION['error']); ?>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>

                        <form method="POST" action="../auth/forgot-password-process.php" novalidate>
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="source" value="<?php echo htmlspecialchars($_GET['source'] ?? 'admin'); ?>">

                            <div class="mb-4">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input id="email"
                                        type="email"
                                        name="email"
                                        required
                                        class="form-control"
                                        autofocus
                                        placeholder="Enter your registered email">
                                </div>
                                <div class="form-text">
                                    We'll send a password reset code to this email address.
                                </div>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Send Reset Code
                                </button>
                            </div>

                            <div class="text-center">
                                <?php
                                $source = $_GET['source'] ?? 'admin';
                                $backUrl = ($source === 'customer') ? '../auth/unified-login.php' : '../auth/unified-login.php';
                                $backText = ($source === 'customer') ? 'Back to Login' : 'Back to Login';
                                ?>
                                <a href="<?php echo $backUrl; ?>" class="btn btn-link">
                                    <i class="fas fa-arrow-left me-2"></i><?php echo $backText; ?>
                                </a>
                            </div>
                        </form>
                    </div>

                    <div class="footer p-3">
                        <p class="mb-0 small text-muted"><?php echo date('Y'); ?> Laundry Management System</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>