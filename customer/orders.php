<?php
$header = 'My Orders';
ob_start();
require_once 'req/customer-profile.php';
require_once 'req/customer-orders.php';

// Get filter values - using status_filter instead of active_filter
$statusFilter = isset($_GET['status_filter']) ? $_GET['status_filter'] : 'all'; // Default to all orders
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'); // Default to today's date
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Pagination variables
$perPage = 10; // Records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page
$start = ($page - 1) * $perPage; // Start record index

// Filter function
function filterOrders($orders, $statusFilter, $date = '')
{
    $filtered = array_filter($orders, function ($order) use ($statusFilter, $date) {
        // Filter by status
        $statusMatch = true;
        if ($statusFilter != 'all') {
            $statusMatch = $order['status'] == $statusFilter;
        }

        // Filter by date if provided
        $dateMatch = true;
        if (!empty($date)) {
            $orderDate = date('Y-m-d', strtotime($order['created_at']));
            $dateMatch = $orderDate == $date;
        }

        return $statusMatch && $dateMatch;
    });

    // Sort by created_at descending (newest first)
    usort($filtered, function ($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    return $filtered;
}

// Filter the orders
$filteredOrders = filterOrders($allOrders, $statusFilter, $date);
$total = count($filteredOrders);
$totalPages = ceil($total / $perPage);
$page = min(max(1, $page), max(1, $totalPages));
$paginatedOrders = array_slice($filteredOrders, $start, $perPage);

// Get counts for tabs
$statusCounts = [
    'all' => count(filterOrders($allOrders, 'all', $date)),
    'Pending' => count(filterOrders($allOrders, 'Pending', $date)),
    'Ongoing' => count(filterOrders($allOrders, 'Ongoing', $date)),
    'Ready for Pickup' => count(filterOrders($allOrders, 'Ready for Pickup', $date)),
    'Claimed' => count(filterOrders($allOrders, 'Claimed', $date)),
    'Unclaimed' => count(filterOrders($allOrders, 'Unclaimed', $date)),
    'Cancelled' => count(filterOrders($allOrders, 'Cancelled', $date))
];

// Status badge function
function renderStatusBadge($status)
{
    $statusColors = [
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

// Alert function
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

// Pagination function
function renderPagination($currentPage, $totalPages)
{
    if ($totalPages <= 1) return;

    $statusFilter = isset($_GET['status_filter']) ? $_GET['status_filter'] : 'all';
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    $search = isset($_GET['search']) ? $_GET['search'] : '';

    echo '<nav aria-label="Page navigation">';
    echo '<ul class="pagination justify-content-center mt-3 custom-pagination">';

    // Previous button
    $prevDisabled = $currentPage <= 1 ? 'disabled' : '';
    echo '<li class="page-item ' . $prevDisabled . '">';
    echo '<a class="page-link" href="?page=' . ($currentPage - 1) . '&status_filter=' . $statusFilter . '&date=' . $date . '&search=' . urlencode($search) . '" tabindex="-1">Previous</a>';
    echo '</li>';

    // Page numbers
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = $i == $currentPage ? 'active' : '';
        echo '<li class="page-item ' . $active . '">';
        echo '<a class="page-link" href="?page=' . $i . '&status_filter=' . $statusFilter . '&date=' . $date . '&search=' . urlencode($search) . '">' . $i . '</a>';
        echo '</li>';
    }

    // Next button
    $nextDisabled = $currentPage >= $totalPages ? 'disabled' : '';
    echo '<li class="page-item ' . $nextDisabled . '">';
    echo '<a class="page-link" href="?page=' . ($currentPage + 1) . '&status_filter=' . $statusFilter . '&date=' . $date . '&search=' . urlencode($search) . '">Next</a>';
    echo '</li>';

    echo '</ul>';
    echo '</nav>';
}

// Render table row function
function renderTableRow($order)
{
    // Prepare item details for receipt
    $itemDetails = '';
    if ($order['tops'] > 0) $itemDetails .= "Tops: {$order['tops']}<br>";
    if ($order['bottoms'] > 0) $itemDetails .= "Bottoms: {$order['bottoms']}<br>";
    if ($order['undergarments'] > 0) $itemDetails .= "Undergarments: {$order['undergarments']}<br>";
    if ($order['delicates'] > 0) $itemDetails .= "Delicates: {$order['delicates']}<br>";
    if ($order['linens'] > 0) $itemDetails .= "Linens: {$order['linens']}<br>";
    if ($order['curtains_drapes'] > 0) $itemDetails .= "Curtains & Drapes: {$order['curtains_drapes']}<br>";
    if ($order['blankets_comforters'] > 0) $itemDetails .= "Blankets & Comforters: {$order['blankets_comforters']}<br>";
    if ($order['others'] > 0) $itemDetails .= "Others: {$order['others']} (See remarks)<br>";

    // Check if order is cancelled
    $isCancelled = ($order['status'] === 'Cancelled');
    $rowClass = $isCancelled ? 'table-secondary' : '';

    echo '
    <tr class="' . $rowClass . '">
        <td class="text-center">
            Dropped: ' . date("M d, Y - h:i A", strtotime($order['created_at'])) . '<br>
            Last Modified: ' . date("M d, Y - h:i A", strtotime($order['updated_at'])) . '
            ' . ($isCancelled && $order['cancelled_at'] ? '<br><small class="text-danger">Cancelled: ' . date("M d, Y - h:i A", strtotime($order['cancelled_at'])) . '</small>' : '') . '
        </td>
        <td class="text-center">#' . htmlspecialchars($order['queue_number']) . '</td>
        <td class="text-center">' . renderStatusBadge($order['status']) . '</td>
        <td class="text-center">
            <div class="d-flex gap-2 justify-content-center align-items-center">';

    // View Receipt button - Show for all statuses
    echo '
                <button
                    class="modern-action-btn modern-action-btn-success view-receipt-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#viewReceiptModal"
                    data-id="' . htmlspecialchars($order['id']) . '"
                    data-customer-name="' . htmlspecialchars($order['customer_name'] ?? '') . '"
                    data-queue-number="' . htmlspecialchars($order['queue_number'] ?? 'N/A') . '"
                    data-receipt-number="' . htmlspecialchars($order['receipt_number'] ?? 'N/A') . '"
                    data-receipt-payment-status="' . htmlspecialchars($order['payment_status'] ?? 'Unpaid') . '"
                    data-receipt-amount-tendered="' . htmlspecialchars($order['amount_tendered'] ?? '0.00') . '"
                    data-receipt-total-price="' . htmlspecialchars($order['total_price'] ?? '0.00') . '"
                    data-receipt-amount-change="' . htmlspecialchars($order['amount_change'] ?? '0.00') . '"
                    data-receipt-order-details="' . htmlspecialchars($order['order_details'] ?? 'No details') . '"
                    data-receipt-created-at="' . htmlspecialchars($order['receipt_created_at'] ?? 'N/A') . '"
                    data-accommodated-by="' . htmlspecialchars($order['accommodated_by'] ?? 'System') . '"
                    data-is-whites-order="' . htmlspecialchars($order['is_whites_order'] ?? '0') . '"
                    data-item-details="' . htmlspecialchars($itemDetails) . '"
                    title="View Receipt">
                    <i class="fas fa-file-invoice"></i>
                </button>';

    // View Cancellation Details button - Show ONLY for Cancelled status
    if ($isCancelled) {
        echo '
                <button
                    class="modern-action-btn modern-action-btn-secondary"
                    onclick="showCancellationDetails(' . htmlspecialchars($order['queue_number']) . ', \'' .
            htmlspecialchars(addslashes($order['cancellation_reason'] ?? 'N/A')) . '\', \'' .
            htmlspecialchars(addslashes($order['cancellation_notes'] ?? 'No additional notes')) . '\', \'' .
            htmlspecialchars(addslashes($order['cancelled_by'] ?? 'System')) . '\')"
                    title="View Cancellation Details">
                    <i class="fas fa-info-circle"></i>
                </button>';
    }

    echo '
            </div>
        </td>
    </tr>';
}
?>

<link href="assets/css/laundry-actions.css" rel="stylesheet">
<link href="assets/css/laundry-modals.css" rel="stylesheet">
<link href="assets/css/orders-mobile.css" rel="stylesheet">

<style>
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
            <h3 class="mb-0">Order Records</h3>
            <div class="d-flex align-items-center">
                <!-- Date Filter and Search Input -->
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
                            id="searchOrders"
                            name="search"
                            placeholder="Search..."
                            value="<?php echo htmlspecialchars($search); ?>"
                            class="form-control"
                            onkeyup="filterTable()">
                    </div>
                </form>
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
            <!-- Desktop Table View -->
            <div class="d-none d-md-block">
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered" id="ordersTable">
                        <thead style="background-color: var(--primary-color); color: white;">
                            <tr>
                                <th class="sortable text-center" data-sort-col="0" data-sort-dir="desc">Dates <i class="fas fa-sort-down"></i></th>
                                <th class="sortable text-center" data-sort-col="1" data-sort-dir="asc">Queue # <i class="fas fa-sort"></i></th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($paginatedOrders)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">
                                        <?php if (!empty($date) && $date != date('Y-m-d')): ?>
                                            <?php if ($statusFilter == 'all'): ?>
                                                No orders found for <?php echo date('M d, Y', strtotime($date)); ?>.
                                            <?php else: ?>
                                                No <?php echo htmlspecialchars($statusFilter); ?> orders found for <?php echo date('M d, Y', strtotime($date)); ?>.
                                            <?php endif; ?>
                                        <?php elseif ($date == date('Y-m-d')): ?>
                                            <?php if ($statusFilter == 'all'): ?>
                                                No orders found for today.
                                            <?php else: ?>
                                                No <?php echo htmlspecialchars($statusFilter); ?> orders found for today.
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php if ($statusFilter == 'all'): ?>
                                                No orders found.
                                            <?php else: ?>
                                                No <?php echo htmlspecialchars($statusFilter); ?> orders found.
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($paginatedOrders as $order): ?>
                                    <?php renderTableRow($order); ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr id="noSearchResults" style="display: none;">
                                <td colspan="4" class="text-center">No orders found matching your search criteria.</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Mobile Card View -->
            <div class="d-md-none">
                <?php if (empty($paginatedOrders)): ?>
                    <div class="text-center py-4">
                        <?php if (!empty($date) && $date != date('Y-m-d')): ?>
                            <?php if ($statusFilter == 'all'): ?>
                                No orders found for <?php echo date('M d, Y', strtotime($date)); ?>.
                            <?php else: ?>
                                No <?php echo htmlspecialchars($statusFilter); ?> orders found for <?php echo date('M d, Y', strtotime($date)); ?>.
                            <?php endif; ?>
                        <?php elseif ($date == date('Y-m-d')): ?>
                            <?php if ($statusFilter == 'all'): ?>
                                No orders found for today.
                            <?php else: ?>
                                No <?php echo htmlspecialchars($statusFilter); ?> orders found for today.
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if ($statusFilter == 'all'): ?>
                                No orders found.
                            <?php else: ?>
                                No <?php echo htmlspecialchars($statusFilter); ?> orders found.
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="orders-mobile-list">
                        <?php foreach ($paginatedOrders as $order):
                            // Prepare item details for receipt
                            $itemDetails = '';
                            if ($order['tops'] > 0) $itemDetails .= "Tops: {$order['tops']}<br>";
                            if ($order['bottoms'] > 0) $itemDetails .= "Bottoms: {$order['bottoms']}<br>";
                            if ($order['undergarments'] > 0) $itemDetails .= "Undergarments: {$order['undergarments']}<br>";
                            if ($order['delicates'] > 0) $itemDetails .= "Delicates: {$order['delicates']}<br>";
                            if ($order['linens'] > 0) $itemDetails .= "Linens: {$order['linens']}<br>";
                            if ($order['curtains_drapes'] > 0) $itemDetails .= "Curtains & Drapes: {$order['curtains_drapes']}<br>";
                            if ($order['blankets_comforters'] > 0) $itemDetails .= "Blankets & Comforters: {$order['blankets_comforters']}<br>";
                            if ($order['others'] > 0) $itemDetails .= "Others: {$order['others']} (See remarks)<br>";

                            // Check if order is cancelled
                            $isCancelled = ($order['status'] === 'Cancelled');
                        ?>
                            <div class="order-card mb-3 p-3 border rounded" data-status="<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>" data-dropped-date="<?php echo htmlspecialchars($order['created_at']); ?>" data-queue-number="<?php echo htmlspecialchars($order['queue_number']); ?>">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <strong>Queue #<?php echo htmlspecialchars($order['queue_number']); ?></strong>
                                    </div>
                                    <div>
                                        <?php echo renderStatusBadge($order['status']); ?>
                                    </div>
                                </div>

                                <div class="order-dates mb-2">
                                    <small class="text-muted">
                                        Dropped: <?php echo date("M d, Y - h:i A", strtotime($order['created_at'])); ?><br>
                                        Modified: <?php echo date("M d, Y - h:i A", strtotime($order['updated_at'])); ?>
                                        <?php if ($isCancelled && $order['cancelled_at']): ?>
                                            <br><small class="text-danger">Cancelled: <?php echo date("M d, Y - h:i A", strtotime($order['cancelled_at'])); ?></small>
                                        <?php endif; ?>
                                    </small>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <button
                                        class="modern-action-btn modern-action-btn-success view-receipt-btn"
                                        style="font-size: 20px;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#viewReceiptModal"
                                        data-id="<?php echo htmlspecialchars($order['id']); ?>"
                                        data-customer-name="<?php echo htmlspecialchars($order['customer_name'] ?? ''); ?>"
                                        data-queue-number="<?php echo htmlspecialchars($order['queue_number'] ?? 'N/A'); ?>"
                                        data-receipt-number="<?php echo htmlspecialchars($order['receipt_number'] ?? 'N/A'); ?>"
                                        data-receipt-payment-status="<?php echo htmlspecialchars($order['payment_status'] ?? 'Unpaid'); ?>"
                                        data-receipt-amount-tendered="<?php echo htmlspecialchars($order['amount_tendered'] ?? '0.00'); ?>"
                                        data-receipt-total-price="<?php echo htmlspecialchars($order['total_price'] ?? '0.00'); ?>"
                                        data-receipt-amount-change="<?php echo htmlspecialchars($order['amount_change'] ?? '0.00'); ?>"
                                        data-receipt-order-details="<?php echo htmlspecialchars($order['order_details'] ?? 'No details'); ?>"
                                        data-receipt-created-at="<?php echo htmlspecialchars($order['receipt_created_at'] ?? 'N/A'); ?>"
                                        data-accommodated-by="<?php echo htmlspecialchars($order['accommodated_by'] ?? 'System'); ?>"
                                        data-item-details="<?php echo htmlspecialchars($itemDetails); ?>"
                                        title="View Receipt">
                                        <i class="fas fa-file-invoice"></i>
                                    </button>

                                    <!-- View Cancellation Details button for mobile - Show ONLY for Cancelled status -->
                                    <?php if ($isCancelled): ?>
                                        <button
                                            class="modern-action-btn modern-action-btn-secondary"
                                            style="font-size: 20px;"
                                            onclick="showCancellationDetails(<?php echo htmlspecialchars($order['queue_number']); ?>, '<?php echo htmlspecialchars(addslashes($order['cancellation_reason'] ?? 'N/A')); ?>', '<?php echo htmlspecialchars(addslashes($order['cancellation_notes'] ?? 'No additional notes')); ?>', '<?php echo htmlspecialchars(addslashes($order['cancelled_by'] ?? 'System')); ?>')"
                                            title="View Cancellation Details">
                                            <i class="fas fa-info-circle"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div id="noSearchResultsMobile" class="text-center py-4" style="display: none;">
                        No orders found matching your search criteria.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php renderPagination($page, $totalPages); ?>
        </div>
    </div>
</div>

<?php
$slot = ob_get_clean();
include '../layouts/app-customer.php';
?>

<!-- Include Modals -->
<?php include 'modals/view-receipt.php'; ?>

<script src="../assets/js/jquery.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>

<script>
    function filterTable() {
        const input = document.getElementById('searchOrders');
        const filter = input.value.toUpperCase();

        // Filter desktop table
        const table = document.getElementById('ordersTable');
        const rows = table.querySelectorAll('tbody tr');
        const noResultsRow = document.getElementById('noSearchResults');

        let visibleRows = 0;

        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            const searchMatch = Array.from(cells).some(cell =>
                cell.textContent.toUpperCase().includes(filter)
            );

            if (searchMatch) {
                row.style.display = "";
                visibleRows++;
            } else {
                row.style.display = "none";
            }
        });

        // Filter mobile cards
        const orderCards = document.querySelectorAll('.order-card');
        const noResultsMobile = document.getElementById('noSearchResultsMobile');
        let visibleCards = 0;

        orderCards.forEach(card => {
            const cardText = card.textContent.toUpperCase();
            const searchMatch = cardText.includes(filter);

            if (searchMatch) {
                card.style.display = "block";
                visibleCards++;
            } else {
                card.style.display = "none";
            }
        });

        // Show or hide the "no results" messages
        if (visibleRows === 0 && visibleCards === 0 && filter !== "") {
            noResultsRow.style.display = "table-row";
            noResultsMobile.style.display = "block";
        } else {
            noResultsRow.style.display = "none";
            noResultsMobile.style.display = "none";
        }
    }

    // Sorting functionality for desktop table
    document.querySelectorAll('#ordersTable .sortable').forEach(header => {
        header.addEventListener('click', () => {
            const table = document.getElementById('ordersTable');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr:not(#noSearchResults):not([style*="display: none"])')); // Exclude no results row and hidden search rows
            const colIndex = parseInt(header.dataset.sortCol);
            let dir = header.dataset.sortDir === 'asc' ? 1 : -1;

            // Toggle sort direction
            header.dataset.sortDir = header.dataset.sortDir === 'asc' ? 'desc' : 'asc';

            // Update sort icons
            document.querySelectorAll('#ordersTable .sortable i').forEach(icon => {
                icon.classList.remove('fa-sort-up', 'fa-sort-down');
                icon.classList.add('fa-sort');
            });
            const icon = header.querySelector('i');
            icon.classList.remove('fa-sort');
            icon.classList.add(header.dataset.sortDir === 'asc' ? 'fa-sort-up' : 'fa-sort-down');

            // Sort rows
            rows.sort((a, b) => {
                let aText = a.cells[colIndex].textContent.trim();
                let bText = b.cells[colIndex].textContent.trim();

                // Special handling for Dates column (index 0)
                if (colIndex === 0) {
                    const aMatch = aText.match(/Dropped: (.*?) /);
                    const bMatch = bText.match(/Dropped: (.*?) /);
                    if (aMatch && bMatch) {
                        const aDateStr = aMatch[1];
                        const bDateStr = bMatch[1];
                        const aDate = new Date(aDateStr);
                        const bDate = new Date(bDateStr);
                        if (!isNaN(aDate.getTime()) && !isNaN(bDate.getTime())) {
                            return dir * (bDate - aDate); // Reverse for descending by default
                        }
                    }
                    // Fallback to string comparison
                    return dir * bText.localeCompare(aText, undefined, {
                        numeric: true,
                        sensitivity: 'base'
                    });
                }

                // Special handling for Queue # (index 1) - numeric sort
                if (colIndex === 1) {
                    const aNum = parseInt(aText.replace('#', ''), 10);
                    const bNum = parseInt(bText.replace('#', ''), 10);
                    if (!isNaN(aNum) && !isNaN(bNum)) {
                        return dir * (aNum - bNum);
                    }
                    // Fallback to string comparison
                    return dir * aText.localeCompare(bText, undefined, {
                        numeric: true,
                        sensitivity: 'base'
                    });
                }

                // Default string comparison for other columns
                return dir * aText.localeCompare(bText, undefined, {
                    numeric: true,
                    sensitivity: 'base'
                });
            });

            // Re-append sorted rows
            rows.forEach(row => tbody.appendChild(row));
        });
    });

    // Sorting functionality for mobile cards
    document.querySelectorAll('#ordersTable .sortable').forEach(header => {
        header.addEventListener('click', () => {
            const orderCards = Array.from(document.querySelectorAll('.order-card:not([style*="display: none"])')); // Exclude hidden search/status filtered cards
            const colIndex = parseInt(header.dataset.sortCol);
            let dir = header.dataset.sortDir === 'asc' ? 1 : -1;

            // Sort cards
            orderCards.sort((a, b) => {
                let aText, bText;

                // Special handling for Dates column (index 0)
                if (colIndex === 0) {
                    aText = a.getAttribute('data-dropped-date');
                    bText = b.getAttribute('data-dropped-date');
                    const aDate = new Date(aText);
                    const bDate = new Date(bText);
                    if (!isNaN(aDate.getTime()) && !isNaN(bDate.getTime())) {
                        return dir * (bDate - aDate); // Reverse for descending by default
                    }
                    // Fallback to string comparison
                    return dir * bText.localeCompare(aText, undefined, {
                        numeric: true,
                        sensitivity: 'base'
                    });
                }

                // Special handling for Queue # (index 1)
                if (colIndex === 1) {
                    aText = a.getAttribute('data-queue-number');
                    bText = b.getAttribute('data-queue-number');
                    const aNum = parseInt(aText.replace('#', ''), 10);
                    const bNum = parseInt(bText.replace('#', ''), 10);
                    if (!isNaN(aNum) && !isNaN(bNum)) {
                        return dir * (aNum - bNum);
                    }
                    // Fallback to string comparison
                    return dir * aText.localeCompare(bText, undefined, {
                        numeric: true,
                        sensitivity: 'base'
                    });
                }

                // Default string comparison
                return dir * aText.localeCompare(bText, undefined, {
                    numeric: true,
                    sensitivity: 'base'
                });
            });

            // Re-append sorted cards
            const mobileList = document.querySelector('.orders-mobile-list');
            orderCards.forEach(card => mobileList.appendChild(card));
        });
    });
</script>