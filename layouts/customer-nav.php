<?php
require_once '../customer/req/chat-functions.php';
$customer_unread_count = 0;
if (isset($_SESSION['customer_logged_in']) && $_SESSION['customer_logged_in']) {
    $customer_id = $_SESSION['user_id'];
    $chat_room = getChatRoom($conn, $customer_id);
    if ($chat_room) {
        $customer_unread_count = getUnreadCount($conn, $chat_room['id'], false);
    }
}
?>

<link href="../admin/assets/css/admin-sidenav.css" rel="stylesheet">
<link href="../customer/assets/css/nav-mobile.css" rel="stylesheet">

<div class="header-container">
    <?php if (isset($header)): ?>
        <header class="bg-white shadow-sm">
            <div class="container-fluid py-3 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <button class="btn me-2" style="color: #644499;" id="mobileSidebarToggle">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h1 class="h3 mb-0" style="color: #644499;">
                            <?php echo htmlspecialchars($header); ?>
                        </h1>
                    </div>

                    <div class="d-flex align-items-center">
                        <a href="../customer/customer-chat.php" class="chat-button">
                            <div class="chat-button-content">
                                <i class="fas fa-comments"></i>
                                <span class="chat-text">Support Chat</span>
                                <?php if ($customer_unread_count > 0): ?>
                                    <span class="chat-badge"><?php echo $customer_unread_count; ?></span>
                                <?php endif; ?>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </header>
    <?php endif; ?>
</div>

<nav class="sidebar" id="adminSidebar">
    <div class="sidebar-header d-flex align-items-center">
        <img src="../logo.jpg" alt="Logo" class="img-fluid me-2" style="height: 70px;">
        <span class="fw-bold fs-5 d-none d-lg-inline">Sasy Laundry Hub</span>
        <button class="btn btn-link ms-auto d-lg-none" id="sidebarCollapseBtn" style="color: #644499;">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <div class="sidebar-user">
        <div class="d-flex align-items-center">
            <div class="avatar rounded-circle d-flex align-items-center justify-content-center"
                style="width: 45px; height: 45px;">
                <span class="fw-bold text-white"><?php echo substr($_SESSION['customer_details']['name'] ?? 'A', 0, 1); ?></span>
            </div>
            <div class="ms-3 d-none d-lg-block">
                <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['customer_details']['name'] ?? 'customer'); ?></div>
                <small><?php echo htmlspecialchars($_SESSION['customer_details']['email'] ?? 'customer@example.com'); ?></small>
            </div>
        </div>
    </div>

    <ul class="sidebar-nav list-unstyled">
        <li class="sidebar-item">
            <a href="../customer/index.php" class="sidebar-link d-flex align-items-center">
                <i class="fas fa-home-alt me-3"></i>
                <span class="d-none d-lg-inline">Home</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="../customer/orders.php" class="sidebar-link d-flex align-items-center">
                <i class="fas fa-box me-3"></i>
                <span class="d-none d-lg-inline">My Orders</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="../customer/prelist-orders.php" class="sidebar-link d-flex align-items-center">
                <i class="fas fa-clipboard-list me-3"></i>
                <span class="d-none d-lg-inline">Pre-listed Orders</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="../customer/change-password.php" class="sidebar-link d-flex align-items-center">
                <i class="fas fa-lock me-3"></i>
                <span class="d-none d-lg-inline">Change Password</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <button type="button" class="sidebar-link d-flex align-items-center bg-transparent border-0 w-100 text-start" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt me-3"></i>
            <span class="d-none d-lg-inline">Log Out</span>
        </button>
    </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('adminSidebar');
        const mainContent = document.querySelector('.main-content');
        const sidebarToggleBtn = document.getElementById('sidebarCollapseBtn');

        if (sidebarToggleBtn) {
            sidebarToggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('expanded');
            });
        }

        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.sidebar-link');

        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href) {
                const linkPath = new URL(href, window.location.origin).pathname;
                if (currentPath === linkPath) {
                    link.classList.add('active');
                    const parentItem = link.closest('.sidebar-item');
                    if (parentItem) {
                        parentItem.classList.add('active-parent');
                    }
                }
            }
        });
    });
document.addEventListener('DOMContentLoaded', function() {
    const swalStyle = document.createElement('style');
    swalStyle.textContent = `
        .swal2-container {
            z-index: 99999 !important;
        }
    `;
    document.head.appendChild(swalStyle);
});
</script>