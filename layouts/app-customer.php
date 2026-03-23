<?php
require_once '../auth/customer-auth-checker.php';

$role = $_SESSION['customer_logged_in'] ?? false ? 'customer' : 'guest';

$allowed_roles = ['customer'];

if (!in_array($role, $allowed_roles)) {
    header('Location: ../auth/unified-login.php');
    exit();
}
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

    <link href="../assets/css/app.css" rel="stylesheet">
</head>

<body class="font-sans antialiased">
    <div class="d-flex">
        <?php include '../layouts/customer-nav.php'; ?>

        <div class="main-content flex-grow-1">


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

            const alerts = document.querySelectorAll('.alert:not(#reviewAlert)');
            alerts.forEach(alert => {
                alertContainer.appendChild(alert);

                setTimeout(() => {
                    alert.classList.add('show');
                }, 10);

                const dismissTime = parseInt(alert.getAttribute('data-auto-dismiss'));
                if (!isNaN(dismissTime)) {
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
                }
            });

            document.querySelectorAll('.alert:not(#reviewAlert) .btn-close').forEach(closeBtn => {
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