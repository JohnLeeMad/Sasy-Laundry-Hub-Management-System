<?php
$header = 'Change Password';
ob_start();
require_once '../config/db_conn.php';
session_start();

if (!isset($_SESSION['customer_id'])) {
    $_SESSION['error'] = ['You must be logged in to change your password.'];
    header('Location: ../auth/unified-login.php');
    exit();
}

$customer_id = $_SESSION['customer_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = trim($_POST['current_password'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    $errors = [];

    if (empty($current_password)) {
        $errors[] = 'Current password is required.';
    }

    if (empty($new_password)) {
        $errors[] = 'New password is required.';
    } elseif (strlen($new_password) < 8) {
        $errors[] = 'New password must be at least 8 characters long.';
    }

    if ($new_password !== $confirm_password) {
        $errors[] = 'New password and confirmation do not match.';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param('i', $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user || !password_verify($current_password, $user['password'])) {
            $errors[] = 'Current password is incorrect.';
        }
    }

    if (empty($errors)) {
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param('si', $new_password_hash, $customer_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = ['Password changed successfully.'];
            header('Location: change-password.php');
            exit();
        } else {
            $errors[] = 'Failed to update password. Please try again.';
        }
    }

    if (!empty($errors)) {
        $_SESSION['error'] = $errors;
        header('Location: change-password.php');
        exit();
    }
}

?>

<link href="assets/css/laundry-modals.css" rel="stylesheet">
<link href="assets/css/pass-mobile.css" rel="stylesheet">

<div class="container-fluid px-4 py-4">
    <?php if (isset($_SESSION['success'])): ?>
        <?php
        if (is_array($_SESSION['success'])) {
            foreach ($_SESSION['success'] as $success) {
                renderAlert('success', $success);
            }
        } else {
            renderAlert('success', $_SESSION['success']);
        }
        unset($_SESSION['success']);
        ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <?php
        if (is_array($_SESSION['error'])) {
            foreach ($_SESSION['error'] as $error) {
                renderAlert('error', $error);
            }
        } else {
            renderAlert('error', $_SESSION['error']);
        }
        unset($_SESSION['error']);
        ?>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1" style="color: var(--primary-color);">
                        <i class="fas fa-lock me-2"></i>Update Password
                    </h4>
                    <small>Update your account password</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="change-password.php">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <small class="text-muted">Must be at least 8 characters long.</small>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$slot = ob_get_clean();
include '../layouts/app-customer.php';

function renderAlert($type, $message)
{
    if (!empty($message)) {
        if (is_array($message)) {
            foreach ($message as $msg) {
                if (!empty(trim($msg))) {
                    $icon = $type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
                    $alertClass = $type === 'success' ? 'alert-success' : 'alert-error';
                    $title = $type === 'success' ? 'Success' : 'Error';

                    echo '
                    <div class="alert ' . $alertClass . '" role="alert" data-auto-dismiss="4000">
                        <i class="' . $icon . ' alert-icon"></i>
                        <div class="alert-content">
                            <span class="alert-title">' . $title . '</span>
                            <span>' . htmlspecialchars(trim($msg)) . '</span>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <div class="alert-progress"><div class="alert-progress-bar"></div></div>
                    </div>';
                }
            }
        }
        else if (strpos($message, '-for-') !== false) {
            $errors = explode('-for-', $message);
            foreach ($errors as $error) {
                if (!empty(trim($error))) {
                    $icon = $type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
                    $alertClass = $type === 'success' ? 'alert-success' : 'alert-error';
                    $title = $type === 'success' ? 'Success' : 'Error';

                    echo '
                    <div class="alert ' . $alertClass . '" role="alert" data-auto-dismiss="4000">
                        <i class="' . $icon . ' alert-icon"></i>
                        <div class="alert-content">
                            <span class="alert-title">' . $title . '</span>
                            <span>' . htmlspecialchars(trim($error)) . '</span>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <div class="alert-progress"><div class="alert-progress-bar"></div></div>
                    </div>';
                }
            }
        }
        else {
            $icon = $type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
            $alertClass = $type === 'success' ? 'alert-success' : 'alert-error';
            $title = $type === 'success' ? 'Success' : 'Error';

            echo '
            <div class="alert ' . $alertClass . '" role="alert" data-auto-dismiss="4000">
                <i class="' . $icon . ' alert-icon"></i>
                <div class="alert-content">
                    <span class="alert-title">' . $title . '</span>
                    <span>' . htmlspecialchars($message) . '</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <div class="alert-progress"><div class="alert-progress-bar"></div></div>
            </div>';
        }
    }
}
?>