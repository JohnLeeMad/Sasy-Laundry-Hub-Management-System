<?php
$header = 'Pre-listed Orders';
ob_start();
require_once 'req/customer-profile.php';
require_once 'req/customer-prelist-orders.php';

$perPage = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $perPage;

function filterPrelistOrders($orders)
{
    return $orders;
}

$filteredOrders = filterPrelistOrders($allPrelistOrders);
$total = count($filteredOrders);
$totalPages = ceil($total / $perPage);
$page = min(max(1, $page), max(1, $totalPages));
$paginatedOrders = array_slice($filteredOrders, $start, $perPage);

function renderStatusBadge($status)
{
    $statusColors = [
        'Pre-listed' => 'secondary'
    ];
    $color = $statusColors[$status] ?? 'secondary';
    return '<span class="badge bg-' . $color . '">' . htmlspecialchars($status) . '</span>';
}

function renderAlert($type, $message)
{
    if (!empty($message)) {
        $icon = $type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
        $alertClass = $type === 'success' ? 'alert-success' : 'alert-error';
        $title = $type === 'success' ? 'Success' : 'Error';

        echo '
            <div class="alert ' . $alertClass . '" role="alert" data-auto-dismiss="6000">
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

    echo '<nav aria-label="Page navigation">';
    echo '<ul class="pagination justify-content-center mt-3">';

    $prevDisabled = $currentPage <= 1 ? 'disabled' : '';
    echo '<li class="page-item ' . $prevDisabled . '">';
    echo '<a class="page-link" href="?page=' . ($currentPage - 1) . '" tabindex="-1">Previous</a>';
    echo '</li>';

    for ($i = 1; $i <= $totalPages; $i++) {
        $active = $i == $currentPage ? 'active' : '';
        echo '<li class="page-item ' . $active . '">';
        echo '<a class="page-link" href="?page=' . $i . '">' . $i . '</a>';
        echo '</li>';
    }

    $nextDisabled = $currentPage >= $totalPages ? 'disabled' : '';
    echo '<li class="page-item ' . $nextDisabled . '">';
    echo '<a class="page-link" href="?page=' . ($currentPage + 1) . '">Next</a>';
    echo '</li>';

    echo '</ul>';
    echo '</nav>';
}

function renderTableRow($order)
{
    $itemDetails = '';
    if ($order['tops'] > 0) $itemDetails .= "Tops: {$order['tops']}<br>";
    if ($order['bottoms'] > 0) $itemDetails .= "Bottoms: {$order['bottoms']}<br>";
    if ($order['undergarments'] > 0) $itemDetails .= "Undergarments: {$order['undergarments']}<br>";
    if ($order['delicates'] > 0) $itemDetails .= "Delicates: {$order['delicates']}<br>";
    if ($order['linens'] > 0) $itemDetails .= "Linens: {$order['linens']}<br>";
    if ($order['curtains_drapes'] > 0) $itemDetails .= "Curtains & Drapes: {$order['curtains_drapes']}<br>";
    if ($order['blankets_comforters'] > 0) $itemDetails .= "Blankets & Comforters: {$order['blankets_comforters']}<br>";
    if ($order['others'] > 0) $itemDetails .= "Others: {$order['others']} (See remarks)<br>";

    echo '
    <tr>
        <td class="text-center">
            Created: ' . date("M d, Y - h:i A", strtotime($order['created_at'])) . '<br>
        </td>
        <td class="text-center">' . htmlspecialchars($order['receipt_number']) . '</td>
        <td class="text-center">
            <div class="d-flex gap-2 justify-content-center">
                <button
                    class="modern-action-btn modern-action-btn-success view-prelist-receipt-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#viewPrelistReceiptModal"
                    data-id="' . htmlspecialchars($order['id']) . '"
                    data-customer-name="' . htmlspecialchars($order['customer_name'] ?? '') . '"
                    data-receipt-number="' . htmlspecialchars($order['receipt_number'] ?? 'N/A') . '"
                    data-payment-status="' . htmlspecialchars($order['payment_status'] ?? 'Unpaid') . '"
                    data-amount-tendered="' . htmlspecialchars($order['amount_tendered'] ?? '0.00') . '"
                    data-total-price="' . htmlspecialchars($order['total_price'] ?? '0.00') . '"
                    data-amount-change="' . htmlspecialchars($order['amount_change'] ?? '0.00') . '"
                    data-order-details="' . htmlspecialchars($order['order_details'] ?? 'No details') . '"
                    data-created-at="' . htmlspecialchars($order['receipt_created_at'] ?? $order['created_at']) . '"
                    data-accommodated-by="' . htmlspecialchars($order['accommodated_by'] ?? 'System') . '"
                    data-separate-whites="' . htmlspecialchars($order['separate_whites'] ?? '0') . '"
                    data-is-whites-order="' . htmlspecialchars($order['is_whites_order'] ?? '0') . '"
                    data-item-details="' . htmlspecialchars($itemDetails) . '"
                    title="View Receipt">
                    <i class="fas fa-file-invoice"></i>
                </button>

                <button
                    type="button"
                    class="modern-action-btn modern-action-btn-danger"
                    onclick="confirmCancelPrelistOrder(' . htmlspecialchars($order['id']) . ', \'' . htmlspecialchars(addslashes($order['receipt_number'])) . '\')"
                    title="Cancel Order">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </td>
    </tr>';
}
?>

<link href="assets/css/laundry-actions.css" rel="stylesheet">
<link href="assets/css/laundry-modals.css" rel="stylesheet">
<link href="assets/css/prelist-mobile.css" rel="stylesheet">

<div class="container-fluid">
    <style>
        .alert-progress-bar {
            animation: progressBar 6s linear forwards;
        }
    </style>
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
            <h3 class="mb-0">Pre-listed Orders</h3>
            <div class="d-flex align-items-center">
                <div class="input-group input-group-sm" style="max-width: 160px;">
                    <span class="input-group-text" style="background-color: var(--accent-color); color: white;">
                        <i class="fas fa-search"></i>
                    </span>
                    <input
                        type="text"
                        id="searchOrders"
                        placeholder="Search..."
                        class="form-control"
                        onkeyup="filterTable()">
                </div>
                <button
                    class="btn btn-light ms-2"
                    style="background-color: var(--accent-color); color: white; white-space: nowrap;"
                    data-bs-toggle="modal"
                    data-bs-target="#prelistOrderModal">
                    <i class="fas fa-plus me-2"></i> Pre-list an Order
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered" id="prelistOrdersTable">
                    <thead style="background-color: var(--primary-color); color: white;">
                        <tr>
                            <th class="text-center">Dates</th>
                            <th class="text-center">Receipt Number</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($paginatedOrders)): ?>
                            <tr>
                                <td colspan="3" class="text-center">
                                    No pre-listed orders found.
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
                            <td colspan="3" class="text-center">No pre-listed orders found matching your search criteria.</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <?php renderPagination($page, $totalPages); ?>
        </div>
    </div>
</div>

<?php
$slot = ob_get_clean();
include '../layouts/app-customer.php';
?>

<?php include 'modals/view-prelist-receipt.php'; ?>
<?php include 'modals/prelist-order.php'; ?>

<script src="../assets/js/jquery.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>

<script>
    function filterTable() {
        const input = document.getElementById('searchOrders');
        const filter = input.value.toUpperCase();
        const table = document.getElementById('prelistOrdersTable');
        const rows = table.querySelectorAll('tbody tr');
        const noResultsRow = document.getElementById('noSearchResults');

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
</script>