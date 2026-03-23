<?php
require_once '../customer/req/chat-functions.php';
$total_unread_chats = getTotalUnreadCount($conn, true);

?>

<link href="../admin/assets/css/admin-sidenav.css" rel="stylesheet">

<div class="header-container">
    <?php if (isset($header)): ?>
        <header class="bg-white shadow-sm">
            <div class="container-fluid py-3 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0" style="color: #644499;"><?php echo htmlspecialchars($header); ?></h1>
                    <div class="d-flex gap-2">
                        <a href="../admin/chat-list.php" class="chat-button">
                            <div class="chat-button-content">
                                <i class="fas fa-comments"></i>
                                <span class="chat-text">Customer Chats</span>
                                <?php if ($total_unread_chats > 0): ?>
                                    <span class="chat-badge"><?php echo $total_unread_chats; ?></span>
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
        <button class="btn btn-link text-white ms-auto d-lg-none" id="sidebarCollapseBtn">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <div class="sidebar-user">
        <div class="d-flex align-items-center">
            <div class="avatar rounded-circle d-flex align-items-center justify-content-center"
                style="width: 45px; height: 45px;">
                <span class="fw-bold text-white"><?php echo substr($_SESSION['admin_details']['name'] ?? 'A', 0, 1); ?></span>
            </div>
            <div class="ms-3 d-none d-lg-block">
                <div class="fw-bold">Admin <?php echo htmlspecialchars($_SESSION['admin_details']['name'] ?? 'Admin'); ?></div>
                <small><?php echo htmlspecialchars($_SESSION['admin_details']['email'] ?? 'admin@example.com'); ?></small>
            </div>
        </div>
    </div>

    <ul class="sidebar-nav list-unstyled">
        <li class="sidebar-item">
            <a href="../admin/dashboard.php" class="sidebar-link d-flex align-items-center">
                <i class="fas fa-tachometer-alt me-3"></i>
                <span class="d-none d-lg-inline">View Dashboard</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="../admin/laundry-list.php" class="sidebar-link d-flex align-items-center">
                <i class="fas fa-tshirt me-3"></i>
                <span class="d-none d-lg-inline">Laundry List</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="../admin/prelisted-orders.php" class="sidebar-link d-flex align-items-center">
                <i class="fas fa-clipboard-list me-3"></i>
                <span class="d-none d-lg-inline">Pre-listed Orders</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="../admin/supply-list.php" class="sidebar-link d-flex align-items-center">
                <i class="fas fa-boxes me-3"></i>
                <span class="d-none d-lg-inline">Supply List</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="../admin/inventory.php" class="sidebar-link d-flex align-items-center">
                <i class="fas fa-warehouse me-3"></i>
                <span class="d-none d-lg-inline">Inventory</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="../admin/reports.php" class="sidebar-link d-flex align-items-center">
                <i class="fas fa-file-alt me-3"></i>
                <span class="d-none d-lg-inline">Reports</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="../admin/prices-settings.php" class="sidebar-link d-flex align-items-center">
                <i class="fas fa-dollar-sign me-3"></i>
                <span class="d-none d-lg-inline">Manage Prices</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="../admin/reviews.php" class="sidebar-link d-flex align-items-center">
                <i class="fas fa-star me-3"></i>
                <span class="d-none d-lg-inline">Reviews</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="../admin/customers.php" class="sidebar-link d-flex align-items-center">
                <i class="fas fa-user-friends me-3"></i>
                <span class="d-none d-lg-inline">Customer Accounts</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="../admin/employees.php" class="sidebar-link d-flex align-items-center">
                <i class="fas fa-user-tie me-3"></i>
                <span class="d-none d-lg-inline">Team Accounts</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="../admin/audit-logs.php" class="sidebar-link d-flex align-items-center">
                <i class="fas fa-clipboard-list me-2"></i>
                <span class="d-none d-lg-inline">Audit Logs</span>
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
                const pageName = href.split('/').pop();
                if (currentPath.includes(pageName)) {
                    link.classList.add('active');
                    const parentItem = link.closest('.sidebar-item');
                    if (parentItem) {
                        parentItem.classList.add('active-parent');
                    }
                }
            }
        });

        console.log('Current path:', currentPath);
        const activeLinks = document.querySelectorAll('.sidebar-link.active');
        console.log('Active links found:', activeLinks.length);
    });
</script>