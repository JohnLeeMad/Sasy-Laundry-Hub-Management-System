<?php
session_start();
include 'req/staff-auth-check.php';
require_once __DIR__ . '/req/audit-logger.php'; // Add audit logger

$header = 'Financial Reports';
ob_start();
require_once '../config/db_conn.php';

// Set Philippine timezone
date_default_timezone_set('Asia/Manila');

// Set UTF-8 encoding for database connection
mysqli_set_charset($conn, "utf8mb4");

// Get date filters
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
$periodFilter = isset($_GET['periodFilter']) ? $_GET['periodFilter'] : '';

// Check if download was requested
if (isset($_GET['download']) && $_GET['download'] == 'true') {
    // AUDIT LOGGING - Log report download
    if (file_exists(__DIR__ . '/audit-logger.php')) {
        $description = 'Downloaded financial report for period: ' .
            date('F d, Y', strtotime($startDate)) . ' to ' .
            date('F d, Y', strtotime($endDate));
        logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'download_report', $description);
    }

    // Redirect to export PDF
    header('Location: req/export-pdf.php?start_date=' . $startDate . '&end_date=' . $endDate);
    exit;
}

// Get profits (paid laundry)
$profitQuery = "SELECT SUM(total_price) as total_profit 
                FROM laundry_lists 
                WHERE payment_status = 'Paid'
                AND DATE(created_at) BETWEEN ? AND ?";
$stmt = $conn->prepare($profitQuery);
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$profitResult = $stmt->get_result()->fetch_assoc();
$totalProfit = $profitResult['total_profit'] ?? 0;

// Get supply expenses
$supplyQuery = "SELECT sp.name, sp.price, st.quantity, (sp.price * st.quantity) as total_cost
                FROM supply_transactions st 
                JOIN supply_products sp ON st.product_id = sp.id
                WHERE st.type = 'IN'
                AND DATE(st.created_at) BETWEEN ? AND ?";
$stmt = $conn->prepare($supplyQuery);
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$supplyExpenses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get utility bills
$billsQuery = "SELECT * FROM utility_bills WHERE DATE(bill_date) BETWEEN ? AND ?";
$stmt = $conn->prepare($billsQuery);
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$utilityBills = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate total expenses
$totalExpenses = 0;
foreach ($supplyExpenses as $supply) {
    $totalExpenses += $supply['total_cost'];
}
foreach ($utilityBills as $bill) {
    $totalExpenses += $bill['amount'];
}

// Set proper content type header
header('Content-Type: text/html; charset=UTF-8');
?>

<div class="container-fluid px-4 py-4">

    <link href="assets/css/laundry-modals.css" rel="stylesheet">

    <?php if (isset($_SESSION['success'])): ?>
        <?php renderAlert('success', $_SESSION['success']); ?>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <?php renderAlert('error', $_SESSION['error']); ?>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Date Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3" id="filterForm">
                <div class="col-md-2">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control form-control-sm" id="startDate" name="start_date" value="<?= $startDate ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-control form-control-sm" id="endDate" name="end_date" value="<?= $endDate ?>">
                </div>
                <div class="col-md-2">
                    <label for="periodFilter" class="form-label">Quick Filter</label>
                    <select class="form-select form-select-sm" id="periodFilter" name="periodFilter">
                        <option value="">Select Period</option>
                        <option value="1" <?php echo ($periodFilter == '1') ? 'selected' : ''; ?>>Today</option>
                        <option value="7" <?php echo ($periodFilter == '7') ? 'selected' : ''; ?>>Last 7 Days</option>
                        <option value="30" <?php echo ($periodFilter == '30') ? 'selected' : ''; ?>>Last 30 Days</option>
                        <option value="365" <?php echo ($periodFilter == '365') ? 'selected' : ''; ?>>Last Year</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <?php if (isset($_GET['start_date']) || isset($_GET['end_date']) || isset($_GET['periodFilter'])): ?>
                            <a href="reports.php" class="btn btn-outline-secondary btn-sm">Clear Filters</a>
                        <?php endif; ?>
                        <a href="?start_date=<?= $startDate ?>&end_date=<?= $endDate ?>&download=true" class="btn btn-primary btn-sm">
                            <i class="fas fa-file-pdf me-2"></i>Export PDF
                        </a>
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addUtilityModal">
                            <i class="fas fa-plus me-2"></i>Add Utility Bill
                        </button>

                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Financial Report -->
    <div class="card">
        <div class="card-body">
            <h3 class="text-center mb-4">Financial Report</h3>
            <p class="text-center">Period: <?= date('F d, Y', strtotime($startDate)) ?> - <?= date('F d, Y', strtotime($endDate)) ?></p>

            <!-- Revenue Section -->
            <div class="mb-4">
                <h4>Revenue</h4>
                <div class="table-responsive">
                    <table class="table">
                        <tr>
                            <td>Total Laundry Revenue</td>
                            <td class="text-end">₱<?= number_format($totalProfit, 2) ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Expenses Section -->
            <div class="mb-4">
                <h4>Expenses</h4>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Description</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Supply Expenses
                            foreach ($supplyExpenses as $supply):
                            ?>
                                <tr>
                                    <td>Supply</td>
                                    <td><?= htmlspecialchars($supply['name'], ENT_QUOTES, 'UTF-8') ?> (<?= $supply['quantity'] ?> stocks)</td>
                                    <td class="text-end">₱<?= number_format($supply['total_cost'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>

                            <?php
                            // Utility Bills
                            foreach ($utilityBills as $bill):
                                $displayText = htmlspecialchars($bill['type'], ENT_QUOTES, 'UTF-8');

                                // Add description for maintenance bills
                                if ($bill['type'] === 'Maintenance' && !empty($bill['description'])) {
                                    $displayText .= ' - ' . htmlspecialchars($bill['description'], ENT_QUOTES, 'UTF-8');
                                }
                            ?>
                                <tr>
                                    <td>Utility</td>
                                    <td><?= $displayText ?></td>
                                    <td class="text-end">₱<?= number_format($bill['amount'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>

                            <tr class="fw-bold">
                                <td colspan="2">Total Expenses</td>
                                <td class="text-end">₱<?= number_format($totalExpenses, 2) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Net Profit -->
            <div class="mb-4">
                <h4>Summary</h4>
                <div class="table-responsive">
                    <table class="table">
                        <tr class="fw-bold">
                            <td>Net Profit/Loss</td>
                            <td class="text-end">₱<?= number_format($totalProfit - $totalExpenses, 2) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-submit form when date inputs change
    document.getElementById('startDate').addEventListener('change', function() {
        // Only submit if both dates are filled or if we have at least one date
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;

        if (startDate || endDate) {
            // Clear period filter when manually selecting dates
            document.getElementById('periodFilter').value = '';
            document.getElementById('filterForm').submit();
        }
    });

    document.getElementById('endDate').addEventListener('change', function() {
        // Only submit if both dates are filled or if we have at least one date
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;

        if (startDate || endDate) {
            // Clear period filter when manually selecting dates
            document.getElementById('periodFilter').value = '';
            document.getElementById('filterForm').submit();
        }
    });

    // Period filter functionality - auto-submit when changed
    document.getElementById('periodFilter').addEventListener('change', function() {
        const period = this.value;
        if (period) {
            const endDate = new Date();
            const startDate = new Date();

            switch (period) {
                case '1': // Today
                    startDate.setDate(endDate.getDate());
                    break;
                case '7': // Last 7 days
                    startDate.setDate(endDate.getDate() - 7);
                    break;
                case '30': // Last 30 days
                    startDate.setDate(endDate.getDate() - 30);
                    break;
                case '365': // Last year
                    startDate.setDate(endDate.getDate() - 365);
                    break;
            }

            document.getElementById('startDate').value = startDate.toISOString().split('T')[0];
            document.getElementById('endDate').value = endDate.toISOString().split('T')[0];

            // Auto-submit the form when period is selected
            document.getElementById('filterForm').submit();
        } else if (period === '') {
            // If "Select Period" is chosen, clear dates and submit
            document.getElementById('startDate').value = '';
            document.getElementById('endDate').value = '';
            document.getElementById('filterForm').submit();
        }
    });
</script>

<?php
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

$slot = ob_get_clean();
include '../layouts/app.php';

include 'modals/add-utility.php';
?>