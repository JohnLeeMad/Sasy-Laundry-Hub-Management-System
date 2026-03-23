<?php
require_once '../customer/req/chat-functions.php';
$total_unread_chats = getTotalUnreadCount($conn, true);

?>

<link href="../admin/assets/css/admin-sidenav.css" rel="stylesheet">

<!-- Page Header -->
<div class="header-container">
    <?php if (isset($header)): ?>
        <header class="bg-white shadow-sm">
            <div class="container-fluid py-3 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0" style="color: #644499;"><?php echo htmlspecialchars($header); ?></h1>
                    <div class="d-flex gap-2">
                        <a href="../staff/chat-list.php" class="chat-button">
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
    <!-- Logo and brand section -->
    <div class="sidebar-header d-flex align-items-center">
        <img src="../logo.jpg" alt="Logo" class="img-fluid me-2" style="height: 70px;">
        <span class="fw-bold fs-5 d-none d-lg-inline">Sasy Laundry Hub</span>
        <button class="btn btn-link text-white ms-auto d-lg-none" id="sidebarCollapseBtn">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- Admin profile summary -->
    <div class="sidebar-user">
        <div class="d-flex align-items-center">
            <div class="avatar rounded-circle d-flex align-items-center justify-content-center"
                style="width: 45px; height: 45px;">
                <span class="fw-bold text-white"><?php echo substr($_SESSION['staff_details']['name'] ?? 'A', 0, 1); ?></span>
            </div>
            <div class="ms-3 d-none d-lg-block">
                <div class="fw-bold">Staff <?php echo htmlspecialchars($_SESSION['staff_details']['name'] ?? 'Staff'); ?></div>
                <small><?php echo htmlspecialchars($_SESSION['staff_details']['email'] ?? 'staff@example.com'); ?></small>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <ul class="sidebar-nav list-unstyled">
        <li class="sidebar-item">
            <a href="../staff/dashboard.php" class="sidebar-link d-flex align-items-center">
                <i class="fas fa-tachometer-alt me-3"></i>
                <span class="d-none d-lg-inline">View Dashboard</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="../staff/laundry-list.php" class="sidebar-link d-flex align-items-center">
                <i class="fas fa-tshirt me-3"></i>
                <span class="d-none d-lg-inline">Laundry List</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="../staff/prelisted-orders.php" class="sidebar-link d-flex align-items-center">
                <i class="fas fa-clipboard-list me-3"></i>
                <span class="d-none d-lg-inline">Pre-listed Orders</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="../staff/supply-list.php" class="sidebar-link d-flex align-items-center">
                <i class="fas fa-boxes me-3"></i>
                <span class="d-none d-lg-inline">Supply List</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="../staff/inventory.php" class="sidebar-link d-flex align-items-center">
                <i class="fas fa-warehouse me-3"></i>
                <span class="d-none d-lg-inline">Inventory</span>
            </a>
        </li>
        <!-- <li class="sidebar-item">
            <a href="../staff/reports.php" class="sidebar-link d-flex align-items-center">
                <i class="fas fa-file-alt me-3"></i>
                <span class="d-none d-lg-inline">Reports</span>
            </a>
        </li> -->
        <!-- <li class="sidebar-item">
            <a href="../staff/customers.php" class="sidebar-link d-flex align-items-center">
                <i class="fas fa-users me-3"></i>
                <span class="d-none d-lg-inline">Customer Accounts</span>
            </a>
        </li> -->
        <!-- <li class="sidebar-item">
            <a href="../staff/employees.php" class="sidebar-link d-flex align-items-center">
                <i class="fas fa-users me-3"></i>
                <span class="d-none d-lg-inline">Employee Accounts</span>
            </a>
        </li> -->
        <!-- <li class="sidebar-item">
            <a href="../staff/prices-settings.php" class="sidebar-link d-flex align-items-center">
                <i class="fas fa-dollar-sign me-3"></i>
                <span class="d-none d-lg-inline">Manage Prices</span>
            </a>
        </li> -->
    </ul>

    <!-- Bottom actions -->
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

        // Toggle sidebar on button click
        if (sidebarToggleBtn) {
            sidebarToggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('expanded');
            });
        }

        // Add active class to current page link - Enhanced version
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.sidebar-link');

        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href) {
                // Extract page name from href
                const pageName = href.split('/').pop();
                // Check if current path contains the page name
                if (currentPath.includes(pageName)) {
                    link.classList.add('active');
                    // Make sure parent sidebar-item also has active class if needed
                    const parentItem = link.closest('.sidebar-item');
                    if (parentItem) {
                        parentItem.classList.add('active-parent');
                    }
                }
            }
        });

        // Log the current path for debugging (can be removed in production)
        console.log('Current path:', currentPath);
        const activeLinks = document.querySelectorAll('.sidebar-link.active');
        console.log('Active links found:', activeLinks.length);
    });
</script>