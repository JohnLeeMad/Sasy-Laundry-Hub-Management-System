<?php
session_start();
include 'req/staff-auth-check.php';

$header = 'Pre-listed Orders';
ob_start();
require_once 'req/admin-prelist-orders.php';

$perPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $perPage;

function filterPrelistOrders($orders, $search = '')
{
    $filtered = $orders;

    if (!empty($search)) {
        $filtered = array_filter($filtered, function ($order) use ($search) {
            $searchLower = strtolower($search);
            return (
                strpos(strtolower($order['id']), $searchLower) !== false ||
                strpos(strtolower($order['customer_name'] ?? ''), $searchLower) !== false
            );
        });
    }

    usort($filtered, function ($a, $b) {
        return strtotime($a['created_at']) <=> strtotime($b['created_at']);
    });

    return $filtered;
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$filteredOrders = filterPrelistOrders($allPrelistOrders, $search);
$total = count($filteredOrders);
$totalPages = ceil($total / $perPage);
$page = min(max(1, $page), max(1, $totalPages));
$paginatedOrders = array_slice($filteredOrders, $start, $perPage);

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

    $search = isset($_GET['search']) ? $_GET['search'] : '';

    echo '<nav aria-label="Page navigation">';
    echo '<ul class="pagination justify-content-center mt-3">';

    $prevDisabled = $currentPage <= 1 ? 'disabled' : '';
    echo '<li class="page-item ' . $prevDisabled . '">';
    echo '<a class="page-link" href="?page=' . ($currentPage - 1) . '&search=' . urlencode($search) . '" tabindex="-1">Previous</a>';
    echo '</li>';

    for ($i = 1; $i <= $totalPages; $i++) {
        $active = $i == $currentPage ? 'active' : '';
        echo '<li class="page-item ' . $active . '">';
        echo '<a class="page-link" href="?page=' . $i . '&search=' . urlencode($search) . '">' . $i . '</a>';
        echo '</li>';
    }

    $nextDisabled = $currentPage >= $totalPages ? 'disabled' : '';
    echo '<li class="page-item ' . $nextDisabled . '">';
    echo '<a class="page-link" href="?page=' . ($currentPage + 1) . '&search=' . urlencode($search) . '">Next</a>';
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
            Created: ' . date("M d, Y - h:i A", strtotime($order['created_at'])) . '
        </td>
        <td class="text-center">' . htmlspecialchars($order['receipt_number']) . '</td>
        <td class="text-center">' . htmlspecialchars($order['customer_name'] ?? 'N/A') . '</td>
        <td class="text-center">' . htmlspecialchars($order['customer_phone'] ?? 'N/A') . '</td>
        <td class="text-center">
            <div class="d-flex gap-2 justify-content-center">
                <button
                    class="modern-action-btn accept-prelist-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#acceptPrelistModal"
                    data-id="' . htmlspecialchars($order['id']) . '"
                    data-customer-id="' . htmlspecialchars($order['customer_id'] ?? '') . '"
                    data-customer-name="' . htmlspecialchars($order['customer_name'] ?? '') . '"
                    data-customer-phone="' . htmlspecialchars($order['customer_phone'] ?? 'N/A') . '"
                    data-rounds-of-wash="' . htmlspecialchars($order['rounds_of_wash'] ?? '1') . '"
                    data-scoops-of-detergent="' . htmlspecialchars($order['scoops_of_detergent'] ?? '0') . '"
                    data-dryer-preference="' . htmlspecialchars($order['dryer_preference'] ?? '0') . '"
                    data-folding-service="' . htmlspecialchars($order['folding_service'] ?? '0') . '"
                    data-separate-whites="' . htmlspecialchars($order['separate_whites'] ?? '0') . '"
                    data-is-whites-order="' . htmlspecialchars($order['is_whites_order'] ?? '0') . '"                    
                    data-fabcon-cups="' . htmlspecialchars($order['fabcon_cups'] ?? '0') . '"
                    data-bleach-cups="' . htmlspecialchars($order['bleach_cups'] ?? '0') . '"
                    data-detergent-product-id="' . htmlspecialchars($order['detergent_product_id'] ?? '') . '"
                    data-fabcon-product-id="' . htmlspecialchars($order['fabcon_product_id'] ?? '') . '"
                    data-bleach-product-id="' . htmlspecialchars($order['bleach_product_id'] ?? '') . '"
                    data-total-price="' . htmlspecialchars($order['total_price'] ?? '0.00') . '"
                    data-adjusted-total-price="' . htmlspecialchars($order['adjusted_total_price'] ?? '0.00') . '"
                    data-deducted-balance="' . htmlspecialchars($order['deducted_balance'] ?? '0.00') . '"
                    data-remarks="' . htmlspecialchars($order['remarks'] ?? '') . '"
                    data-accommodated-by="' . htmlspecialchars($order['accommodated_by'] ?? 'System') . '"
                    data-queue-number="' . htmlspecialchars($order['queue_number'] ?? 'N/A') . '"
                    title="Accept as Laundry">
                    <i class="fas fa-check"></i>
                </button>

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
                    data-is-whites-order="' . htmlspecialchars($order['is_whites_order'] ?? '0') . '"
                    data-item-details="' . htmlspecialchars($itemDetails) . '"
                    title="View Receipt">
                    <i class="fas fa-receipt"></i>
                </button>
                
                <button 
                    type="button" 
                    class="modern-action-btn modern-action-btn-danger" 
                    onclick="confirmDeclineOrder(\'' . htmlspecialchars($order['id']) . '\', \'' . htmlspecialchars($order['receipt_number']) . '\')"
                    title="Decline Order">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </td>
    </tr>';
}
?>

<link href="assets/css/laundry-modals.css" rel="stylesheet">
<link href="assets/css/laundry-actions.css" rel="stylesheet">

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
            <h3 class="mb-0">Pre-listed Records</h3>
            <div class="d-flex align-items-center">
                <div class="input-group input-group-sm me-2" style="width: 200px;">
                    <span class="input-group-text" style="background-color: var(--accent-color); color: white;">
                        <i class="fas fa-search"></i>
                    </span>
                    <input
                        type="text"
                        id="searchPrelistOrders"
                        placeholder="Search..."
                        value="<?php echo htmlspecialchars($search); ?>"
                        class="form-control"
                        onkeyup="filterPrelistOrdersTable()">
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered" id="prelistOrdersTable">
                    <thead style="background-color: var(--primary-color); color: white;">
                        <tr>
                            <th class="sortable text-center" data-sort-col="0" data-sort-dir="asc">Dates <i class="fas fa-sort"></i></th>
                            <th class="sortable text-center" data-sort-col="1" data-sort-dir="asc">Receipt Number <i class="fas fa-sort"></i></th>
                            <th class="sortable text-center" data-sort-col="2" data-sort-dir="asc">Customer Name <i class="fas fa-sort"></i></th>
                            <th class="text-center">Contact Number</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($paginatedOrders)): ?>
                            <tr>
                                <td colspan="5" class="text-center">
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
                            <td colspan="5" class="text-center">No pre-listed orders found matching your search criteria.</td>
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
include '../layouts/app.php';
?>

<?php include 'modals/accept-prelist.php'; ?>
<?php include '../customer/modals/view-prelist-receipt.php'; ?>

<script src="../assets/js/jquery.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>

<script>
    function filterPrelistOrdersTable() {
        const input = document.getElementById("searchPrelistOrders");
        const filter = input.value.toUpperCase();
        const table = document.getElementById("prelistOrdersTable");
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

    document.querySelectorAll('#prelistOrdersTable .sortable').forEach(header => {
        header.addEventListener('click', () => {
            const table = document.getElementById('prelistOrdersTable');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr:not(#noSearchResults):not([style*="display: none"])')); 
            const colIndex = parseInt(header.dataset.sortCol);
            let dir = header.dataset.sortDir === 'asc' ? 1 : -1;

            header.dataset.sortDir = header.dataset.sortDir === 'asc' ? 'desc' : 'asc';

            document.querySelectorAll('#prelistOrdersTable .sortable i').forEach(icon => {
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
                    const aMatch = aText.match(/Created: (.*)/);
                    const bMatch = bText.match(/Created: (.*)/);
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
</script>