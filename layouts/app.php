<?php
require_once '../config/db_conn.php';

$role = $_SESSION['super_admin_logged_in'] ?? false ? 'super_admin' : ($_SESSION['admin_logged_in'] ?? false ? 'admin' : ($_SESSION['staff_logged_in'] ?? false ? 'staff' : 'guest'));

$allowed_roles = ['super_admin', 'admin', 'staff'];

if (!in_array($role, $allowed_roles)) {
    header('Location: ../auth/unified-login.php');
    exit();
}

require_once '../customer/req/chat-functions.php';
$total_unread_chats = getTotalUnreadCount($conn, true);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
    <title>LMS - <?php echo ucfirst($header); ?></title>
    <link rel="icon" href="../image.jpg" type="image/jpeg">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>


    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-5-theme/1.3.0/select2-bootstrap-5-theme.min.css" rel="stylesheet">

    <link href="../assets/css/app.css" rel="stylesheet">
</head>

<body class="font-sans antialiased">
    <div class="d-flex">
        <?php if ($role === 'super_admin'): ?>
            <?php include '../layouts/super-admin-nav.php'; ?>
        <?php elseif ($role === 'admin'): ?>
            <?php include '../layouts/admin-nav.php'; ?>
        <?php elseif ($role === 'staff'): ?>
            <?php include '../layouts/staff-nav.php'; ?>
        <?php else: ?>
            <?php include '../layouts/guest.php'; ?>
        <?php endif; ?>

        <div class="main-content flex-grow-1">
            <nav class="navbar navbar-expand-lg top-navbar d-lg-none">
                <div class="container-fluid">
                    <button class="btn" id="mobileSidebarToggle">
                        <i class="fas fa-bars" style="color: #644499;"></i>
                    </button>
                    <span class="navbar-brand mb-0 h1" style="color: #644499;">LMS <?php echo ucfirst($role); ?></span>
                    <div class="dropdown">
                        <button class="btn" type="button" id="quickActionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v" style="color: #644499;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="quickActionDropdown">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="chat-list.php">Customer Chats <?php if ($total_unread_chats > 0) echo '(' . $total_unread_chats . ')'; ?></a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form action="../auth/unified-logout.php" method="POST">
                                    <button class="dropdown-item" type="submit">Log Out</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <main class="container-fluid p-4 gradient-bg">
                <?php if (isset($slot)) echo $slot; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="../assets/js/sweetAlert2.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileToggleBtn = document.getElementById('mobileSidebarToggle');
            const sidebar = document.getElementById('adminSidebar');

            if (mobileToggleBtn && sidebar) {
                mobileToggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('expanded');
                });
            }

            const metaThemeColor = document.createElement('meta');
            metaThemeColor.setAttribute('name', 'theme-color');
            metaThemeColor.setAttribute('content', '#644499');
            document.head.appendChild(metaThemeColor);

            let alertContainer = document.querySelector('.alert-container');
            if (!alertContainer) {
                alertContainer = document.createElement('div');
                alertContainer.className = 'alert-container';
                document.body.appendChild(alertContainer);
            }

            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alertContainer.appendChild(alert);

                setTimeout(() => {
                    alert.classList.add('show');
                }, 10);

                const dismissTime = parseInt(alert.getAttribute('data-auto-dismiss')) || 2000;

                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.classList.remove('show');
                        alert.classList.add('hide');

                        setTimeout(() => {
                            if (alert.parentNode) {
                                alert.parentNode.removeChild(alert);
                            }
                        }, 400);
                    }
                }, dismissTime);
            });

            document.querySelectorAll('.alert .btn-close').forEach(closeBtn => {
                closeBtn.addEventListener('click', function() {
                    const alert = this.closest('.alert');
                    alert.classList.remove('show');
                    alert.classList.add('hide');

                    setTimeout(() => {
                        if (alert.parentNode) {
                            alert.parentNode.removeChild(alert);
                        }
                    }, 400);
                });
            });
        });
    </script>
</body>

</html>