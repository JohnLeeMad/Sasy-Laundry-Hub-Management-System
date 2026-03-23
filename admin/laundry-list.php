<?php
session_start();
include 'req/admin-auth-check.php';

$header = 'Laundry List';  // Dynamic Header
ob_start();  // Start output buffering to capture the page's content
require_once 'req/laundry-list.php';

// Pagination variables
$total = count($laundryLists); // Total records
$perPage = 10; // Records per page
$totalPages = ceil($total / $perPage); // Total pages
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page
$start = ($page - 1) * $perPage; // Start record index
$end = min($start + $perPage - 1, $total); // End record number

// Get filter values - now using status filter instead of active_filter
$statusFilter = isset($_GET['status_filter']) ? $_GET['status_filter'] : 'Pending'; // Default to Pending orders

function renderAlert($type, $message)
{
    if (!empty($message)) {
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

function renderPagination($currentPage, $totalPages)
{
    if ($totalPages <= 1) return;

    $statusFilter = isset($_GET['status_filter']) ? $_GET['status_filter'] : 'Pending';
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'); // Get current date or use today
    $search = isset($_GET['search']) ? $_GET['search'] : '';

    echo '<nav aria-label="Page navigation">';
    echo '<ul class="pagination justify-content-center mt-3 custom-pagination">';

    // Previous button
    $prevDisabled = $currentPage <= 1 ? 'disabled' : '';
    echo '<li class="page-item ' . $prevDisabled . '">';
    echo '<a class="page-link" href="?page=' . ($currentPage - 1) . '&status_filter=' . urlencode($statusFilter) .
        '&date=' . urlencode($date) . '&search=' . urlencode($search) . '" tabindex="-1">Previous</a>';
    echo '</li>';

    // Page numbers
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = $i == $currentPage ? 'active' : '';
        echo '<li class="page-item ' . $active . '">';
        echo '<a class="page-link" href="?page=' . $i . '&status_filter=' . urlencode($statusFilter) .
            '&date=' . urlencode($date) . '&search=' . urlencode($search) . '">' . $i . '</a>';
        echo '</li>';
    }

    // Next button
    $nextDisabled = $currentPage >= $totalPages ? 'disabled' : '';
    echo '<li class="page-item ' . $nextDisabled . '">';
    echo '<a class="page-link" href="?page=' . ($currentPage + 1) . '&status_filter=' . urlencode($statusFilter) .
        '&date=' . urlencode($date) . '&search=' . urlencode($search) . '">Next</a>';
    echo '</li>';

    echo '</ul>';
    echo '</nav>';
}

function renderStatusBadge($status)
{
    $statusColors = [
        'Pre-listed' => 'secondary',
        'Pending' => 'warning',
        'Ongoing' => 'info',
        'Ready for Pickup' => 'success',
        'Claimed' => 'primary',
        'Unclaimed' => 'danger',
        'Cancelled' => 'dark'
    ];
    $color = $statusColors[$status] ?? 'secondary';
    return '<span class="badge bg-' . $color . '">' . htmlspecialchars($status) . '</span>';
}

// Function to render status dropdown
function renderStatusDropdown($list)
{
    global $statusFilter;

    $currentStatus = $list['status'];
    $orderId = $list['id'];

    // Cannot change status if already cancelled
    if ($currentStatus === 'Cancelled') {
        return renderStatusBadge($currentStatus);
    }

    // Define available status transitions
    $statusOptions = [
        'Pre-listed' => ['Pending'],
        'Pending' => ['Ongoing'],
        'Ongoing' => ['Ready for Pickup'],
        'Ready for Pickup' => ['Claimed', 'Unclaimed'],
        'Unclaimed' => ['Claimed'],
        'Claimed' => []
    ];

    $availableTransitions = $statusOptions[$currentStatus] ?? [];

    // If no available transitions, just show the badge
    if (empty($availableTransitions)) {
        return renderStatusBadge($currentStatus);
    }

    $statusColors = [
        'Pre-listed' => 'secondary',
        'Pending' => 'warning',
        'Ongoing' => 'info',
        'Ready for Pickup' => 'success',
        'Claimed' => 'primary',
        'Unclaimed' => 'danger'
    ];
    $currentColor = $statusColors[$currentStatus] ?? 'secondary';

    // Simple dropdown implementation
    $dropdown = '
    <div class="status-dropdown-container">
        <div class="status-badge-clickable badge bg-' . $currentColor . '" 
             onclick="toggleStatusDropdown(' . $orderId . ')">
            ' . htmlspecialchars($currentStatus) . ' <i class="fas fa-chevron-down ms-1"></i>
        </div>
        <div class="status-dropdown-menu" id="statusDropdown_' . $orderId . '">';

    foreach ($availableTransitions as $newStatus) {
        $newColor = $statusColors[$newStatus] ?? 'secondary';
        $dropdown .= '
            <button type="button" class="status-change-option" 
                    onclick="confirmStatusChange(' . $orderId . ', \'' .
            htmlspecialchars(addslashes($currentStatus)) . '\', \'' .
            htmlspecialchars(addslashes($newStatus)) . '\', \'' .
            htmlspecialchars(addslashes($list['queue_number'])) . '\')">
                <span class="badge bg-' . $newColor . ' me-2">→</span>
                ' . $newStatus . '
            </button>';
    }

    $dropdown .= '
        </div>
    </div>';

    return $dropdown;
}

function renderTableRow($list)
{
    // Check if order is cancelled
    $isCancelled = ($list['status'] === 'Cancelled');
    $rowClass = $isCancelled ? 'table-secondary' : '';

    echo '
    <tr class="' . $rowClass . '">
        <td class="text-center">
            Dropped: ' . date("M d, Y - h:i A", strtotime($list['created_at'])) . '<br>
            Last Modified: ' . date("M d, Y - h:i A", strtotime($list['updated_at'])) . '
            ' . ($isCancelled && $list['cancelled_at'] ? '<br><small class="text-danger">Cancelled: ' . date("M d, Y - h:i A", strtotime($list['cancelled_at'])) . '</small>' : '') . '
        </td>
        <td class="text-center">#' . htmlspecialchars($list['queue_number']) . '</td>
        <td class="text-center">' . htmlspecialchars($list['customer_name'] ?? 'N/A') . '</td>
        <td class="text-center">' . htmlspecialchars($list['customer_phone'] ?? 'N/A') . '</td>
        <td class="text-center">' . renderStatusDropdown($list) . '</td>
        <td>
            <div class="d-flex gap-2 justify-content-center align-items-center">';

    $status = $list['status'];

    // Edit Order button - Show for all statuses except Cancelled
    if (!$isCancelled) {
        echo '
                <button
                    class="modern-action-btn edit-laundry-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#editLaundryModal"
                    data-id="' . htmlspecialchars($list['id']) . '"
                    data-customer-name="' . htmlspecialchars($list['customer_name'] ?? '') . '"
                    data-customer-id="' . htmlspecialchars($list['customer_id'] ?? '') . '"
                    data-customer-type="' . htmlspecialchars($list['customer_type'] ?? '') . '"
                    data-customer-phone="' . htmlspecialchars($list['customer_phone'] ?? '') . '"
                    data-status="' . htmlspecialchars($list['status']) . '"
                    data-total-price="' . htmlspecialchars($list['total_price'] ?? '0.00') . '"
                    data-adjusted-total-price="' . htmlspecialchars($list['adjusted_total_price'] ?? '0.00') . '"
                    data-deducted-balance="' . htmlspecialchars($list['deducted_balance'] ?? '0.00') . '"
                    data-payment-status="' . htmlspecialchars($list['payment_status'] ?? 'Unpaid') . '"
                    data-change-stored-as-balance="' . htmlspecialchars($list['change_stored_as_balance'] ?? '0') . '"
                    data-remarks="' . htmlspecialchars($list['remarks'] ?? '') . '"
                    data-accommodated-by="' . htmlspecialchars($list['accommodated_by'] ?? 'System') . '"
                    data-queue-number="' . htmlspecialchars($list['queue_number'] ?? 'N/A') . '"
                    data-rounds-of-wash="' . htmlspecialchars($list['rounds_of_wash'] ?? '1') . '"
                    data-scoops-of-detergent="' . htmlspecialchars($list['scoops_of_detergent'] ?? '1') . '"
                    data-dryer-preference="' . htmlspecialchars($list['dryer_preference'] ?? '0') . '"
                    data-folding-service="' . htmlspecialchars($list['folding_service'] ?? '0') . '"
                    data-separate-whites="' . htmlspecialchars($list['separate_whites'] ?? '0') . '"
                    data-is-whites-order="' . htmlspecialchars($list['is_whites_order'] ?? '0') . '"
                    data-fabcon-cups="' . htmlspecialchars($list['fabcon_cups'] ?? '0') . '"
                    data-bleach-cups="' . htmlspecialchars($list['bleach_cups'] ?? '0') . '"
                    data-detergent-product-id="' . htmlspecialchars($list['detergent_product_id'] ?? '') . '"
                    data-fabcon-product-id="' . htmlspecialchars($list['fabcon_product_id'] ?? '') . '"
                    data-bleach-product-id="' . htmlspecialchars($list['bleach_product_id'] ?? '') . '"
                    data-process-payment="' . htmlspecialchars($list['process_payment'] ?? '0') . '"
                    data-amount-tendered="' . htmlspecialchars($list['amount_tendered'] ?? '0.00') . '"
                    data-change="' . htmlspecialchars($list['amount_change'] ?? '0.00') . '"
                    title="Edit Order">
                    <i class="fas fa-tasks"></i>
                </button>';
    }

    // View Quotation button - Show for all statuses
    echo '
                <button
                    class="modern-action-btn modern-action-btn-success view-receipt-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#viewReceiptModal"
                    data-id="' . htmlspecialchars($list['id']) . '"
                    data-customer-name="' . htmlspecialchars($list['customer_name'] ?? '') . '"
                    data-queue-number="' . htmlspecialchars($list['queue_number'] ?? 'N/A') . '"
                    data-receipt-number="' . htmlspecialchars($list['receipt_number'] ?? 'N/A') . '"
                    data-receipt-payment-status="' . htmlspecialchars($list['payment_status'] ?? 'Unpaid') . '"
                    data-receipt-amount-tendered="' . htmlspecialchars($list['amount_tendered'] ?? '0.00') . '"
                    data-receipt-total-price="' . htmlspecialchars($list['total_price'] ?? '0.00') . '"
                    data-receipt-amount-change="' . htmlspecialchars($list['amount_change'] ?? '0.00') . '"
                    data-receipt-order-details="' . htmlspecialchars($list['order_details'] ?? 'No details') . '"
                    data-receipt-created-at="' . htmlspecialchars($list['receipt_created_at'] ?? 'N/A') . '"
                    data-accommodated-by="' . htmlspecialchars($list['accommodated_by'] ?? 'System') . '"
                    data-is-whites-order="' . htmlspecialchars($list['is_whites_order'] ?? '0') . '"
                    title="View Quotation">                    
                    <i class="fas fa-receipt"></i>
                </button>';

    // Print Receipt button - Show for Pending, Ongoing, Ready for Pickup, Claimed
    if (in_array($status, ['Pending', 'Ongoing', 'Ready for Pickup', 'Claimed'])) {
        echo '
                <button
                    class="modern-action-btn modern-action-btn-info print-receipt-btn"
                    data-order-id="' . htmlspecialchars($list['id']) . '"
                    data-order-status="' . htmlspecialchars($list['status']) . '"
                    title="Print Receipt">
                    <i class="fas fa-print"></i>
                </button>';
    }

    // Cancel Order button - Show ONLY for Pending status
    if ($status === 'Pending') {
        echo '
                <button
                    class="modern-action-btn modern-action-btn-danger cancel-laundry-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#cancelLaundryModal"
                    data-id="' . htmlspecialchars($list['id']) . '"
                    data-customer-name="' . htmlspecialchars($list['customer_name'] ?? '') . '"
                    data-customer-id="' . htmlspecialchars($list['customer_id'] ?? '') . '"
                    data-status="' . htmlspecialchars($list['status']) . '"
                    data-queue-number="' . htmlspecialchars($list['queue_number'] ?? 'N/A') . '"
                    data-deducted-balance="' . htmlspecialchars($list['deducted_balance'] ?? '0.00') . '"
                    title="Cancel Order">
                    <i class="fas fa-ban"></i>
                </button>';
    }

    // View Cancellation Details button - Show ONLY for Cancelled status
    if ($isCancelled) {
        echo '
                <button
                    class="modern-action-btn modern-action-btn-secondary"
                    onclick="showCancellationDetails(' . htmlspecialchars($list['queue_number']) . ', \'' .
            htmlspecialchars(addslashes($list['cancellation_reason'] ?? 'N/A')) . '\', \'' .
            htmlspecialchars(addslashes($list['cancellation_notes'] ?? 'No additional notes')) . '\', \'' .
            htmlspecialchars(addslashes($list['cancelled_by'] ?? 'System')) . '\')"
                    title="View Cancellation Details">
                    <i class="fas fa-info-circle"></i>
                </button>';
    }

    echo '
            </div>
        </td>
    </tr>';
}

// Filter the laundry lists based on status_filter
function filterLaundryLists($lists, $statusFilter, $search = '')
{
    // First filter by status
    $filtered = array_filter($lists, function ($list) use ($statusFilter) {
        if ($statusFilter == 'all') {
            return true; // Show all orders
        } else {
            return $list['status'] == $statusFilter;
        }
    });

    // Then filter by search term if provided
    if (!empty($search)) {
        $filtered = array_filter($filtered, function ($list) use ($search) {
            $searchLower = strtolower($search);
            return (
                strpos(strtolower($list['customer_id']), $searchLower) !== false ||
                strpos(strtolower($list['customer_name'] ?? ''), $searchLower) !== false ||
                strpos(strtolower($list['status']), $searchLower) !== false
            );
        });
    }

    // Sort by queue_number in ascending order
    usort($filtered, function ($a, $b) {
        return $a['queue_number'] <=> $b['queue_number'];
    });

    return $filtered;
}

// Filter and paginate the data
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filteredLists = filterLaundryLists($laundryLists, $statusFilter, $search);
$total = count($filteredLists);
$totalPages = ceil($total / $perPage);
$page = min(max(1, $page), max(1, $totalPages));
$paginatedLists = array_slice($filteredLists, ($page - 1) * $perPage, $perPage);

// Get counts for each status for tabs
$statusCounts = [
    'all' => count($laundryLists),
    'Pre-listed' => count(array_filter($laundryLists, function ($list) {
        return $list['status'] === 'Pre-listed';
    })),
    'Pending' => count(array_filter($laundryLists, function ($list) {
        return $list['status'] === 'Pending';
    })),
    'Ongoing' => count(array_filter($laundryLists, function ($list) {
        return $list['status'] === 'Ongoing';
    })),
    'Ready for Pickup' => count(array_filter($laundryLists, function ($list) {
        return $list['status'] === 'Ready for Pickup';
    })),
    'Claimed' => count(array_filter($laundryLists, function ($list) {
        return $list['status'] === 'Claimed';
    })),
    'Unclaimed' => count(array_filter($laundryLists, function ($list) {
        return $list['status'] === 'Unclaimed';
    })),
    'Cancelled' => count(array_filter($laundryLists, function ($list) {
        return $list['status'] === 'Cancelled';
    }))
];

?>
<link href="assets/css/laundry-modals.css" rel="stylesheet">
<link href="assets/css/laundry-actions.css" rel="stylesheet">
<style>
    /* Simple Status Dropdown Styles */
    .status-dropdown-container {
        position: relative;
        display: inline-block;
    }

    .status-badge-clickable {
        cursor: pointer;
        transition: all 0.2s ease;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        border: none;
    }

    .status-badge-clickable:hover {
        opacity: 0.8;
        transform: translateY(-1px);
    }

    .status-dropdown-menu {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        min-width: 160px;
        margin-top: 2px;
    }

    .status-dropdown-menu.show {
        display: block;
    }

    .status-change-form {
        margin: 0;
    }

    .status-change-option {
        width: 100%;
        padding: 0.5rem 1rem;
        border: none;
        background: none;
        text-align: left;
        cursor: pointer;
        display: flex;
        align-items: center;
        font-size: 0.875rem;
        color: #333;
        transition: background-color 0.2s;
    }

    .status-change-option:hover {
        background-color: #f8f9fa;
        color: #644499;
    }

    .status-change-option .badge {
        font-size: 0.7rem;
        padding: 0.2rem 0.4rem;
    }

    body.status-dropdown-open::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 999;
        background: transparent;
    }

    .custom-pagination .page-link {
        color: #6c757d;
        background-color: #ffffff;
        border: 1px solid #dee2e6;
        padding: 0.5rem 0.75rem;
        transition: all 0.2s ease-in-out;
    }

    .custom-pagination .page-link:hover {
        color: #644499;
        background-color: #f8f9fa;
        border-color: #644499;
    }

    .custom-pagination .page-item.active .page-link {
        background-color: #644499;
        border-color: #644499;
        color: #ffffff;
    }

    .custom-pagination .page-item.disabled .page-link {
        color: #6c757d;
        background-color: #ffffff;
        border-color: #dee2e6;
        opacity: 0.6;
    }

    .custom-pagination .page-link:focus {
        box-shadow: 0 0 0 0.2rem rgba(50, 34, 102, 0.25);
        outline: none;
    }

    .modern-action-btn-warning {
        background-color: #ffc107;
        color: #000;
    }

    .modern-action-btn-warning:hover {
        background-color: #e0a800;
    }
</style>

<div class="container-fluid">
    <?php if (isset($_SESSION['success'])): ?>
        <?php renderAlert('success', $_SESSION['success']); ?>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <?php renderAlert('error', $_SESSION['error']); ?>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="card mt-4 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center" style="background-color: var(--primary-color); color: white;">
            <h3 class="mb-0">Laundry Records</h3>
            <div class="d-flex align-items-center">
                <form method="GET" class="d-flex align-items-center me-2">
                    <input
                        type="hidden"
                        name="status_filter"
                        value="<?php echo htmlspecialchars($statusFilter); ?>">
                    <input
                        type="date"
                        name="date"
                        value="<?php echo htmlspecialchars($date); ?>"
                        class="form-control form-control-sm me-2"
                        style="width: 140px;"
                        onchange="this.form.submit()">
                    <div class="input-group input-group-sm" style="width: 180px;">
                        <span class="input-group-text" style="background-color: var(--accent-color); color: white;">
                            <i class="fas fa-search"></i>
                        </span>
                        <input
                            type="text"
                            id="searchLaundry"
                            placeholder="Search..."
                            value="<?php echo htmlspecialchars($search); ?>"
                            class="form-control"
                            onkeyup="filterLaundryTable()">
                    </div>
                </form>
                <button
                    class="btn btn-light"
                    style="background-color: var(--accent-color); color: white; white-space: nowrap;"
                    data-bs-toggle="modal"
                    data-bs-target="#createLaundryModal">
                    <i class="fas fa-plus me-2"></i> Create Laundry Order
                </button>
            </div>
        </div>

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mt-2 px-3 pt-2">
            <li class="nav-item">
                <a class="nav-link <?php echo $statusFilter == 'Pending' ? 'active fw-bold' : ''; ?>"
                    style="color: <?php echo $statusFilter == 'Pending' ? '#644499' : 'black'; ?>;"
                    href="?status_filter=Pending&date=<?php echo htmlspecialchars($date); ?>&search=<?php echo urlencode($search); ?>">
                    Pending <span class="badge bg-warning"><?php echo $statusCounts['Pending']; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $statusFilter == 'Ongoing' ? 'active fw-bold' : ''; ?>"
                    style="color: <?php echo $statusFilter == 'Ongoing' ? '#644499' : 'black'; ?>;"
                    href="?status_filter=Ongoing&date=<?php echo htmlspecialchars($date); ?>&search=<?php echo urlencode($search); ?>">
                    Ongoing <span class="badge bg-info"><?php echo $statusCounts['Ongoing']; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $statusFilter == 'Ready for Pickup' ? 'active fw-bold' : ''; ?>"
                    style="color: <?php echo $statusFilter == 'Ready for Pickup' ? '#644499' : 'black'; ?>;"
                    href="?status_filter=Ready for Pickup&date=<?php echo htmlspecialchars($date); ?>&search=<?php echo urlencode($search); ?>">
                    Ready for Pickup <span class="badge bg-success"><?php echo $statusCounts['Ready for Pickup']; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $statusFilter == 'Claimed' ? 'active fw-bold' : ''; ?>"
                    style="color: <?php echo $statusFilter == 'Claimed' ? '#644499' : 'black'; ?>;"
                    href="?status_filter=Claimed&date=<?php echo htmlspecialchars($date); ?>&search=<?php echo urlencode($search); ?>">
                    Claimed <span class="badge bg-primary"><?php echo $statusCounts['Claimed']; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $statusFilter == 'Unclaimed' ? 'active fw-bold' : ''; ?>"
                    style="color: <?php echo $statusFilter == 'Unclaimed' ? '#644499' : 'black'; ?>;"
                    href="?status_filter=Unclaimed&date=<?php echo htmlspecialchars($date); ?>&search=<?php echo urlencode($search); ?>">
                    Unclaimed <span class="badge bg-danger"><?php echo $statusCounts['Unclaimed']; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $statusFilter == 'Cancelled' ? 'active fw-bold' : ''; ?>"
                    style="color: <?php echo $statusFilter == 'Cancelled' ? '#644499' : 'black'; ?>;"
                    href="?status_filter=Cancelled&date=<?php echo htmlspecialchars($date); ?>&search=<?php echo urlencode($search); ?>">
                    Cancelled <span class="badge bg-dark"><?php echo $statusCounts['Cancelled']; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $statusFilter == 'all' ? 'active fw-bold' : ''; ?>"
                    style="color: <?php echo $statusFilter == 'all' ? '#644499' : 'black'; ?>;"
                    href="?status_filter=all&date=<?php echo htmlspecialchars($date); ?>&search=<?php echo urlencode($search); ?>">
                    All Orders <span class="badge bg-secondary"><?php echo $statusCounts['all']; ?></span>
                </a>
            </li>
        </ul>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered" id="laundryTable">
                    <thead style="background-color: var(--primary-color); color: white;">
                        <tr>
                            <th class="sortable text-center" data-sort-col="0" data-sort-dir="asc">Dates <i class="fas fa-sort"></i></th>
                            <th class="sortable text-center" data-sort-col="1" data-sort-dir="asc">Queue # <i class="fas fa-sort"></i></th>
                            <th class="sortable text-center" data-sort-col="2" data-sort-dir="asc">Customer Name <i class="fas fa-sort"></i></th>
                            <th class="text-center">Phone Number</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($paginatedLists)): ?>
                            <tr>
                                <td colspan="6" class="text-center">
                                    <?php if ($statusFilter == 'all'): ?>
                                        No laundry orders found for the selected date.
                                    <?php else: ?>
                                        No <?php echo htmlspecialchars($statusFilter); ?> orders found for the selected date.
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($paginatedLists as $list): ?>
                                <?php renderTableRow($list); ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr id="noSearchResults" style="display: none;">
                            <td colspan="6" class="text-center">No laundry orders found matching your search criteria.</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <!-- Pagination -->
            <?php renderPagination($page, $totalPages); ?>
        </div>
    </div>
</div>

<?php
$slot = ob_get_clean();
include '../layouts/app.php';
?>

<!-- Modals -->
<?php include 'modals/create-laundry.php'; ?>
<?php include 'modals/edit-laundry.php'; ?>
<?php include 'modals/view-receipt.php'; ?>
<?php include 'modals/cancel-laundry.php'; ?>

<script src="../assets/js/jquery.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>
    // Simple dropdown functionality
    let currentOpenDropdown = null;

    function toggleStatusDropdown(orderId) {
        const dropdown = document.getElementById('statusDropdown_' + orderId);

        if (currentOpenDropdown && currentOpenDropdown !== dropdown) {
            currentOpenDropdown.classList.remove('show');
            document.body.classList.remove('status-dropdown-open');
        }

        if (dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
            document.body.classList.remove('status-dropdown-open');
            currentOpenDropdown = null;
        } else {
            dropdown.classList.add('show');
            document.body.classList.add('status-dropdown-open');
            currentOpenDropdown = dropdown;
        }
    }

    document.addEventListener('click', function(e) {
        if (currentOpenDropdown && !e.target.closest('.status-dropdown-container')) {
            currentOpenDropdown.classList.remove('show');
            document.body.classList.remove('status-dropdown-open');
            currentOpenDropdown = null;
        }
    });

    document.body.addEventListener('click', function(e) {
        if (e.target === document.body && document.body.classList.contains('status-dropdown-open')) {
            if (currentOpenDropdown) {
                currentOpenDropdown.classList.remove('show');
                document.body.classList.remove('status-dropdown-open');
                currentOpenDropdown = null;
            }
        }
    });

    // Search functionality
    function filterLaundryTable() {
        const input = document.getElementById("searchLaundry");
        const filter = input.value.toUpperCase();
        const table = document.getElementById("laundryTable");
        const rows = table.querySelectorAll('tbody tr');
        const noResultsRow = document.getElementById("noSearchResults");

        let visibleRows = 0;

        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            const match = Array.from(cells).some(cell =>
                cell.textContent.toUpperCase().includes(filter)
            );

            if (match) {
                row.style.display = "";
                visibleRows++;
            } else {
                row.style.display = "none";
            }
        });

        if (visibleRows === 0 && filter !== "") {
            noResultsRow.style.display = "table-row";
        } else {
            noResultsRow.style.display = "none";
        }
    }

    // Sorting functionality
    document.querySelectorAll('#laundryTable .sortable').forEach(header => {
        header.addEventListener('click', () => {
            const table = document.getElementById('laundryTable');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr:not(#noSearchResults):not([style*="display: none"])'));
            const colIndex = parseInt(header.dataset.sortCol);
            let dir = header.dataset.sortDir === 'asc' ? 1 : -1;

            header.dataset.sortDir = header.dataset.sortDir === 'asc' ? 'desc' : 'asc';

            document.querySelectorAll('#laundryTable .sortable i').forEach(icon => {
                icon.classList.remove('fa-sort-up', 'fa-sort-down');
                icon.classList.add('fa-sort');
            });
            const icon = header.querySelector('i');
            icon.classList.remove('fa-sort');
            icon.classList.add(header.dataset.sortDir === 'asc' ? 'fa-sort-up' : 'fa-sort-down');

            rows.sort((a, b) => {
                let aText = a.cells[colIndex].textContent.trim();
                let bText = b.cells[colIndex].textContent.trim();

                if (colIndex === 0) {
                    const aMatch = aText.match(/Dropped: (.*?) /);
                    const bMatch = bText.match(/Dropped: (.*?) /);
                    if (aMatch && bMatch) {
                        const aDateStr = aMatch[1];
                        const bDateStr = bMatch[1];
                        const aDate = new Date(aDateStr);
                        const bDate = new Date(bDateStr);
                        if (!isNaN(aDate.getTime()) && !isNaN(bDate.getTime())) {
                            return dir * (aDate - bDate);
                        }
                    }
                    return dir * aText.localeCompare(bText, undefined, {
                        numeric: true,
                        sensitivity: 'base'
                    });
                }

                if (colIndex === 1) {
                    const aNum = parseInt(aText.replace('#', ''), 10);
                    const bNum = parseInt(bText.replace('#', ''), 10);
                    if (!isNaN(aNum) && !isNaN(bNum)) {
                        return dir * (aNum - bNum);
                    }
                    return dir * aText.localeCompare(bText, undefined, {
                        numeric: true,
                        sensitivity: 'base'
                    });
                }

                return dir * aText.localeCompare(bText, undefined, {
                    numeric: true,
                    sensitivity: 'base'
                });
            });

            rows.forEach(row => tbody.appendChild(row));
        });
    });

    // Print Receipt functionality
    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('click', function(e) {
            if (e.target.closest('.print-receipt-btn')) {
                const button = e.target.closest('.print-receipt-btn');
                const orderId = button.dataset.orderId;
                const orderStatus = button.dataset.orderStatus;

                const receiptType = (orderStatus === 'Claimed') ? 'final' : 'initial';

                const originalHTML = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                button.disabled = true;

                const printWindow = window.open(
                    `req/auto-print-receipt.php?order_id=${orderId}&type=${receiptType}`,
                    `receipt_${orderId}`,
                    'width=400,height=300,scrollbars=no,toolbar=no,location=no'
                );

                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.disabled = false;
                }, 3000);

                if (printWindow) {
                    printWindow.focus();
                }
            }
        });

        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('print_receipt')) {
            const orderId = urlParams.get('print_receipt');
            const printWindow = window.open(
                `req/auto-print-receipt.php?order_id=${orderId}&type=initial`,
                `receipt_${orderId}`,
                'width=400,height=300,scrollbars=no,toolbar=no,location=no'
            );

            if (printWindow) {
                printWindow.focus();
            }

            const newUrl = window.location.pathname + window.location.search.replace(/[?&]print_receipt=[^&]*/, '').replace(/^&/, '?');
            window.history.replaceState({}, document.title, newUrl);
        }

        if (urlParams.get('print_final_receipt')) {
            const orderId = urlParams.get('print_final_receipt');
            const printWindow = window.open(
                `req/auto-print-receipt.php?order_id=${orderId}&type=final`,
                `receipt_${orderId}`,
                'width=400,height=300,scrollbars=no,toolbar=no,location=no'
            );

            if (printWindow) {
                printWindow.focus();
            }

            const newUrl = window.location.pathname + window.location.search.replace(/[?&]print_final_receipt=[^&]*/, '').replace(/^&/, '?');
            window.history.replaceState({}, document.title, newUrl);
        }
    });

    function printMultipleReceipts(orderIds, type = 'initial') {
        const orderIdArray = orderIds.split(',');

        orderIdArray.forEach((orderId, index) => {
            setTimeout(() => {
                const printWindow = window.open(
                    `req/auto-print-receipt.php?order_id=${orderId}&type=${type}`,
                    `receipt_${orderId}`,
                    'width=400,height=300,scrollbars=no,toolbar=no,location=no'
                );

                if (printWindow) {
                    printWindow.focus();
                }
            }, index * 2000);
        });
    }

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('print_receipt')) {
        const orderIds = urlParams.get('print_receipt');

        if (orderIds.includes(',')) {
            printMultipleReceipts(orderIds, 'initial');
        } else {
            const printWindow = window.open(
                `req/auto-print-receipt.php?order_id=${orderIds}&type=initial`,
                `receipt_${orderIds}`,
                'width=400,height=300,scrollbars=no,toolbar=no,location=no'
            );

            if (printWindow) {
                printWindow.focus();
            }
        }

        const newUrl = window.location.pathname + window.location.search.replace(/[?&]print_receipt=[^&]*/, '').replace(/^&/, '?');
        window.history.replaceState({}, document.title, newUrl);
    }
</script>