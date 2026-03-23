<?php
session_start();

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Include the renderAlert function
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
    <title>Management Login - Laundry Management System</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">


    <link href="../assets/css/admin-login.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-5">
                <div class="login-card card">
                    <div class="card-header">
                        <a href="/">
                            <img src="../logo.jpg" alt="Laundry Management System Logo" class="img-fluid mb-3" style="max-height: 90px;">
                        </a>
                        <h3 class="fw-bold text-primary" style="color: var(--primary-color) !important;">Laundry Management Login</h3>
                    </div>

                    <div class="card-body p-4">
                        <!-- Alert section for success or error messages -->
                        <?php if (isset($_SESSION['success'])): ?>
                            <?php renderAlert('success', $_SESSION['success']); ?>
                            <?php unset($_SESSION['success']); ?>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error'])): ?>
                            <?php renderAlert('error', $_SESSION['error']); ?>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>

                        <form method="POST" action="../auth/admin-login-process.php" novalidate>
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                            <div class="mb-4">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input id="email"
                                        type="email"
                                        name="email"
                                        value="<?php echo htmlspecialchars($_SESSION['old']['email'] ?? ''); ?>"
                                        required
                                        class="form-control"
                                        autofocus
                                        autocomplete="username"
                                        placeholder="Enter your email">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input id="password"
                                        type="password"
                                        name="password"
                                        required
                                        class="form-control"
                                        autocomplete="current-password"
                                        placeholder="Enter your password">
                                    <span class="input-group-text password-toggle" onclick="togglePassword()">
                                        <i class="fas fa-eye" id="toggleIcon"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">

                                </div>
                                <div class="forgot-password">
                                    <a href="../auth/forgot-password.php?source=admin">Forgot Password?</a>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="login-footer p-3 text-center">
                        <p class="mb-0 small text-muted"><?php echo date('Y'); ?> Laundry Management System</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

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
    </script>
</body>

</html>