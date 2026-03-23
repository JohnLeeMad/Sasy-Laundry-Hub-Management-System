<?php
session_start();
include 'req/admin-auth-check.php';

$header = 'Inventory Management';  // Dynamic Header
ob_start();  // Start output buffering to capture the page's content
require_once 'req/get-inventory.php';

// Enhanced function to get inventory levels organized by category with usage ranking
function getInventoryByCategory($conn)
{
    $lowStockThreshold = 3;
    $query = "SELECT 
                sp.id as product_id,
                sp.name, 
                sc.name as category_name,
                i.available_units, 
                i.stock_quantity 
              FROM inventory i
              JOIN supply_products sp ON i.product_id = sp.id
              JOIN supply_categories sc ON sp.category_id = sc.id
              WHERE sp.is_active = 1
              ORDER BY sc.name, sp.name";

    $result = $conn->query($query);
    $categories = [];
    $warnings = [];
    $outOfStock = [];

    while ($row = $result->fetch_assoc()) {
        $categoryName = $row['category_name'];

        if (!isset($categories[$categoryName])) {
            $categories[$categoryName] = [];
        }

        $categories[$categoryName][] = [
            'id' => $row['product_id'],
            'name' => $row['name'],
            'available_units' => $row['available_units'],
            'stock_quantity' => $row['stock_quantity']
        ];

        if ($row['stock_quantity'] < $lowStockThreshold && $row['stock_quantity'] > 0) {
            $warnings[] = [
                'name' => $row['name'],
                'category' => $row['category_name'],
                'stock_quantity' => $row['stock_quantity']
            ];
        }

        if ($row['stock_quantity'] == 0) {
            $outOfStock[] = $row['name'] . ' (' . $row['category_name'] . ')';
        }
    }

    return [
        'categories' => $categories,
        'warnings' => $warnings,
        'outOfStock' => $outOfStock
    ];
}

// New function to get product usage ranking by category
function getProductUsageRanking($conn)
{
    $query = "SELECT 
                sp.id as product_id,
                sp.name as product_name,
                sc.name as category_name,
                COUNT(ld.id) as usage_count,
                DATE(MAX(ll.created_at)) as last_used
              FROM laundry_details ld
              JOIN laundry_lists ll ON ld.laundry_list_id = ll.id
              JOIN supply_products sp ON (
                  sp.id = ld.detergent_product_id OR 
                  sp.id = ld.fabcon_product_id OR 
                  sp.id = ld.bleach_product_id
              )
              JOIN supply_categories sc ON sp.category_id = sc.id
              WHERE sp.is_active = 1
              AND ll.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
              GROUP BY sp.id, sp.name, sc.name
              ORDER BY sc.name, usage_count DESC";

    $result = $conn->query($query);
    $rankings = [
        'Detergent' => [],
        'Fabric Conditioner' => [],
        'Bleach' => []
    ];

    while ($row = $result->fetch_assoc()) {
        $category = $row['category_name'];
        if (isset($rankings[$category])) {
            $rankings[$category][] = [
                'product_id' => $row['product_id'],
                'product_name' => $row['product_name'],
                'usage_count' => $row['usage_count'],
                'last_used' => $row['last_used']
            ];
        }
    }

    return $rankings;
}

// Get specific category usage details
function getCategoryUsageDetails($conn, $categoryName)
{
    $categoryMap = [
        'Detergent' => 'detergent_product_id',
        'Fabric Conditioner' => 'fabcon_product_id',
        'Bleach' => 'bleach_product_id'
    ];

    if (!isset($categoryMap[$categoryName])) {
        return [];
    }

    $field = $categoryMap[$categoryName];

    $query = "SELECT 
                sp.id as product_id,
                sp.name as product_name,
                COUNT(ld.id) as usage_count,
                DATE(MAX(ll.created_at)) as last_used,
                ROUND(COUNT(ld.id) * 100.0 / (
                    SELECT COUNT(*) 
                    FROM laundry_details ld2 
                    JOIN laundry_lists ll2 ON ld2.laundry_list_id = ll2.id
                    WHERE ld2.$field IS NOT NULL 
                    AND ld2.$field != '' 
                    AND ll2.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ), 1) as usage_percentage
              FROM laundry_details ld
              JOIN laundry_lists ll ON ld.laundry_list_id = ll.id
              JOIN supply_products sp ON sp.id = ld.$field
              JOIN supply_categories sc ON sp.category_id = sc.id
              WHERE sp.is_active = 1
              AND sc.name = '$categoryName'
              AND ll.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
              GROUP BY sp.id, sp.name
              ORDER BY usage_count DESC";

    $result = $conn->query($query);
    $details = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $details[] = $row;
        }
    }

    return $details;
}

$inventoryData = getInventoryByCategory($conn);
$usageRanking = getProductUsageRanking($conn);
$detergentDetails = getCategoryUsageDetails($conn, 'Detergent');
$fabconDetails = getCategoryUsageDetails($conn, 'Fabric Conditioner');
$bleachDetails = getCategoryUsageDetails($conn, 'Bleach');
?>

<link href="assets/css/laundry-modals.css" rel="stylesheet">
<link href="assets/css/laundry-actions.css" rel="stylesheet">
<link href="assets/css/inventory.css" rel="stylesheet">

<div class="container-fluid">
    <?php if (isset($_SESSION['success'])): ?>
        <?php renderAlert('success', $_SESSION['success']); ?>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <?php renderAlert('error', $_SESSION['error']); ?>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>



    <div class="row">
        <!-- Inventory by Category -->
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header" style="background-color: var(--primary-color); color: white;">
                    <h5 class="mb-0" style="font-size: 1.5rem;">Stock by Product Category</h5>
                </div>
                <div class="card-body">
                    <!-- Category Chart -->
                    <div class="chart-container mb-4">
                        <canvas id="categoryChart"></canvas>
                    </div>

                    <!-- Category Details -->
                    <div style="max-height: 300px; overflow-y: auto;">
                        <?php foreach ($inventoryData['categories'] as $categoryName => $products): ?>
                            <div class="category-section">
                                <div class="category-title">
                                    <i class="fas fa-tag me-2"></i><?php echo htmlspecialchars($categoryName); ?>
                                    <span class="badge bg-secondary ms-2"><?php echo count($products); ?> product(s)</span>
                                </div>

                                <?php foreach ($products as $product): ?>
                                    <?php
                                    $stockClass = '';
                                    $isMostUsed = false;
                                    $usageInfo = '';

                                    // Check if this product is the most used in its category and get usage stats
                                    $categoryDetails = [];
                                    if ($categoryName === 'Detergent') $categoryDetails = $detergentDetails;
                                    elseif ($categoryName === 'Fabric Conditioner') $categoryDetails = $fabconDetails;
                                    elseif ($categoryName === 'Bleach') $categoryDetails = $bleachDetails;

                                    // Find usage stats for this product
                                    foreach ($categoryDetails as $index => $detail) {
                                        if ($detail['product_id'] == $product['id']) {
                                            $usageInfo = "Rank #" . ($index + 1) . " | " . $detail['usage_count'] . " uses (" . $detail['usage_percentage'] . "%) | Last used: " . ($detail['last_used'] ? date('M d, Y', strtotime($detail['last_used'])) : 'Never');
                                            if ($index === 0) {
                                                $isMostUsed = true;
                                            }
                                            break;
                                        }
                                    }

                                    if (empty($usageInfo) && in_array($categoryName, ['Detergent', 'Fabric Conditioner', 'Bleach'])) {
                                        $usageInfo = "No usage data available in the last 30 days";
                                    }

                                    if ($product['stock_quantity'] == 0) {
                                        $stockClass = 'out-of-stock';
                                    } elseif ($product['stock_quantity'] < 3) {
                                        $stockClass = 'low-stock';
                                    } elseif ($isMostUsed) {
                                        $stockClass = 'most-used';
                                    }
                                    ?>
                                    <div class="product-row <?php echo $stockClass; ?>"
                                        <?php if ($usageInfo): ?>
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-html="true"
                                        title="<strong>Usage Stats (Last 30 Days)</strong><br/><?php echo htmlspecialchars($usageInfo); ?>"
                                        <?php endif; ?>>
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <strong>
                                                    <?php if ($isMostUsed): ?>
                                                        <i class="fas fa-crown crown-icon" title="Most Used"></i>
                                                    <?php endif; ?>
                                                    <?php echo htmlspecialchars($product['name']); ?>
                                                </strong>
                                                <div class="small text-muted">
                                                    Available: <?php echo $product['available_units']; ?> unit(s)
                                                    <?php if ($isMostUsed): ?>
                                                        <span class="badge bg-success ms-2">Most Popular</span>
                                                    <?php endif; ?>
                                                    <?php if ($usageInfo && in_array($categoryName, ['Detergent', 'Fabric Conditioner', 'Bleach'])): ?>
                                                        <i class="fas fa-info-circle ms-1" style="color: #6c757d; cursor: help;"></i>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <span class="badge <?php
                                                                    if ($product['stock_quantity'] == 0) echo 'bg-danger';
                                                                    elseif ($product['stock_quantity'] < 3) echo 'bg-warning';
                                                                    else echo 'bg-success';
                                                                    ?>">
                                                    <?php echo $product['stock_quantity']; ?> stock(s)
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Alerts -->
                    <?php if (!empty($inventoryData['warnings'])): ?>
                        <div class="alert alert-warning mt-3" role="alert">
                            <strong><i class="fas fa-exclamation-triangle me-2"></i>Low Stock Warning:</strong>
                            <ul class="mb-0 mt-2">
                                <?php foreach ($inventoryData['warnings'] as $warning): ?>
                                    <li><?php echo htmlspecialchars($warning['name']); ?>: <?php echo $warning['stock_quantity']; ?> stock(s) left</li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($inventoryData['outOfStock'])): ?>
                        <div class="alert alert-danger mt-3" role="alert">
                            <strong><i class="fas fa-times-circle me-2"></i>Out of Stock:</strong>
                            <ul class="mb-0 mt-2">
                                <?php foreach ($inventoryData['outOfStock'] as $item): ?>
                                    <li><?php echo htmlspecialchars($item); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Supply In/Out List -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center" style="background-color: var(--primary-color); color: white;">
                    <h5 class="mb-0" style="font-size: 1.5rem;">Supply In/Out List</h5>
                    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#manageSupplyModal" style="background-color: var(--accent-color); color: white;">
                        <i class="fas fa-plus me-2"></i> Manage Supply
                    </button>
                </div>
                <div class="card-body">
                    <!-- Date Filter Form -->
                    <form method="get" class="mb-3" id="filterForm">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label for="startDate" class="form-label small">From Date</label>
                                <input type="date" class="form-control form-control-sm" id="startDate" name="startDate"
                                    value="<?php echo isset($_GET['startDate']) ? htmlspecialchars($_GET['startDate']) : ''; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="endDate" class="form-label small">To Date</label>
                                <input type="date" class="form-control form-control-sm" id="endDate" name="endDate"
                                    value="<?php echo isset($_GET['endDate']) ? htmlspecialchars($_GET['endDate']) : ''; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="periodFilter" class="form-label small">Quick Filter</label>
                                <select class="form-select form-select-sm" id="periodFilter" name="periodFilter">
                                    <option value="">Select Period</option>
                                    <option value="1" <?php echo (isset($_GET['periodFilter']) && $_GET['periodFilter'] == '1') ? 'selected' : ''; ?>>Today</option>
                                    <option value="7" <?php echo (isset($_GET['periodFilter']) && $_GET['periodFilter'] == '7') ? 'selected' : ''; ?>>Last 7 Days</option>
                                    <option value="30" <?php echo (isset($_GET['periodFilter']) && $_GET['periodFilter'] == '30') ? 'selected' : ''; ?>>Last 30 Days</option>
                                    <option value="365" <?php echo (isset($_GET['periodFilter']) && $_GET['periodFilter'] == '365') ? 'selected' : ''; ?>>Last Year</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <?php if (isset($_GET['startDate']) || isset($_GET['endDate']) || isset($_GET['periodFilter'])): ?>
                                    <a href="inventory.php" class="btn btn-sm btn-outline-secondary w-100">Clear Filters</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover table-striped table-bordered table-sm" id="supplyInOutTable">
                            <thead style="background-color: var(--primary-color); color: white;">
                                <tr>
                                    <th class="sortable text-center" data-sort-col="0" data-sort-dir="asc" style="font-size: 0.875rem;">Date <i class="fas fa-sort"></i></th>
                                    <th class="sortable text-center" data-sort-col="1" data-sort-dir="asc" style="font-size: 0.875rem;">Supply Name <i class="fas fa-sort"></i></th>
                                    <th class="sortable text-center" data-sort-col="2" data-sort-dir="asc" style="font-size: 0.875rem; width: 60px;">Qty <i class="fas fa-sort"></i></th>
                                    <th style="font-size: 0.875rem; width: 70px;" class="text-center">Type</th>
                                    <th style="font-size: 0.875rem; width: 80px;" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php generateSupplyInOutTableRows($supplyInOutItems); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Simple Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');

    // Prepare data for the chart
    const categories = <?php echo json_encode(array_keys($inventoryData['categories'])); ?>;
    const categoryTotals = <?php
                            $totals = [];
                            foreach ($inventoryData['categories'] as $products) {
                                $total = array_sum(array_column($products, 'stock_quantity'));
                                $totals[] = $total;
                            }
                            echo json_encode($totals);
                            ?>;

    const categoryChart = new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: categories,
            datasets: [{
                label: 'Stock Quantity',
                data: categoryTotals,
                backgroundColor: [
                    'rgba(177, 0, 124, 0.8)',
                    'rgba(43, 88, 118, 0.8)',
                    'rgba(46, 204, 113, 0.8)',
                    'rgba(243, 156, 18, 0.8)',
                    'rgba(231, 76, 60, 0.8)',
                    'rgba(155, 89, 182, 0.8)',
                    'rgba(52, 152, 219, 0.8)',
                    'rgba(241, 196, 15, 0.8)'
                ],
                borderColor: [
                    'rgba(177, 0, 124, 1)',
                    'rgba(43, 88, 118, 1)',
                    'rgba(46, 204, 113, 1)',
                    'rgba(243, 156, 18, 1)',
                    'rgba(231, 76, 60, 1)',
                    'rgba(155, 89, 182, 1)',
                    'rgba(52, 152, 219, 1)',
                    'rgba(241, 196, 15, 1)'
                ],
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            indexAxis: 'y', // This makes it horizontal
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.parsed.x} items in stock`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        stepSize: 1
                    }
                },
                y: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

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

    // Sorting functionality for supply in/out table
    document.querySelectorAll('#supplyInOutTable .sortable').forEach(header => {
        header.addEventListener('click', () => {
            const table = document.getElementById('supplyInOutTable');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr:not(:empty)')); // Exclude empty rows
            const colIndex = parseInt(header.dataset.sortCol);
            let dir = header.dataset.sortDir === 'asc' ? 1 : -1;

            // Toggle sort direction
            header.dataset.sortDir = header.dataset.sortDir === 'asc' ? 'desc' : 'asc';

            // Update sort icons
            document.querySelectorAll('#supplyInOutTable .sortable i').forEach(icon => {
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

                // Special handling for Date column (index 0)
                if (colIndex === 0) {
                    const aMatch = aText.match(/(.+?)(?=\s*(?:[0-1]?[0-9]:[0-5][0-9]\s*[AP]M|$))/);
                    const bMatch = bText.match(/(.+?)(?=\s*(?:[0-1]?[0-9]:[0-5][0-9]\s*[AP]M|$))/);
                    if (aMatch && bMatch) {
                        const aDateStr = a.cells[colIndex].querySelector('span').textContent.trim();
                        const bDateStr = b.cells[colIndex].querySelector('span').textContent.trim();
                        const aDate = new Date(aMatch[1] + ' ' + aDateStr);
                        const bDate = new Date(bMatch[1] + ' ' + bDateStr);
                        if (!isNaN(aDate.getTime()) && !isNaN(bDate.getTime())) {
                            return dir * (aDate - bDate);
                        }
                    }
                    // Fallback to string comparison
                    return dir * aText.localeCompare(bText, undefined, {
                        numeric: true,
                        sensitivity: 'base'
                    });
                }

                // Special handling for Quantity (index 2)
                if (colIndex === 2) {
                    const aNum = parseInt(aText, 10);
                    const bNum = parseInt(bText, 10);
                    if (!isNaN(aNum) && !isNaN(bNum)) {
                        return dir * (aNum - bNum);
                    }
                    // Fallback to string comparison
                    return dir * aText.localeCompare(bText, undefined, {
                        numeric: true,
                        sensitivity: 'base'
                    });
                }

                // Default string comparison for Supply Name (index 1)
                return dir * aText.localeCompare(bText, undefined, {
                    numeric: true,
                    sensitivity: 'base'
                });
            });

            // Re-append sorted rows
            rows.forEach(row => tbody.appendChild(row));
        });
    });

    // Initialize Bootstrap tooltips for usage stats
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                trigger: 'hover focus'
            });
        });
    });
</script>

<?php
$slot = ob_get_clean();
include '../layouts/app.php';
?>

<?php include 'modals/create-transaction.php'; ?>
<?php include 'modals/edit-transaction.php'; ?>

<script src="../assets/js/jquery.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>

<?php
/**
 * Display success or error alerts.
 */
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

/**
 * Generate supply in/out table rows with icon-only actions.
 */
function generateSupplyInOutTableRows($supplyInOutItems)
{
    foreach ($supplyInOutItems as $item) {
        echo '<tr>';
        $datePart = date("M d, Y", strtotime($item['date']));
        $timePart = date("h:i A", strtotime($item['date']));
        echo '<td class="date-cell" style="text-align: center;">' . $datePart . '<br/><span style="font-size: 1em; color: #888;">' . $timePart . '</span></td>';
        echo '<td style="text-align: center;"><span class="supply-name" title="' . htmlspecialchars($item['supply_name']) . '">' . htmlspecialchars($item['supply_name']) . '</span></td>';
        echo '<td class="text-center">' . htmlspecialchars($item['quantity']) . '</td>';
        echo '<td class="text-center"><span class="badge bg-' . ($item['type'] === 'IN' ? 'success' : 'secondary') . '">' . htmlspecialchars($item['type']) . '</span></td>';
        echo '<td>';
        echo '<div class="d-flex gap-2 justify-content-center">';
        // Edit button - modern icon only
        echo '<button class="modern-action-btn" ';
        echo 'data-bs-toggle="modal" data-bs-target="#editTransactionModal" ';
        echo 'onclick="populateEditTransactionModal(' . $item['id'] . ', \'' . htmlspecialchars($item['supply_name']) . '\', ' . $item['quantity'] . ', \'' . $item['type'] . '\')" ';
        echo 'title="Edit Transaction">';
        echo '<i class="fas fa-edit"></i>';
        echo '</button>';
        // Delete button - modern icon only with SweetAlert2
        echo '<button type="button" class="modern-action-btn modern-action-btn-danger" ';
        echo 'onclick="confirmDeleteTransaction(' . $item['id'] . ', \'' . htmlspecialchars(addslashes($item['supply_name'])) . '\', ' . $item['quantity'] . ', \'' . $item['type'] . '\')" ';
        echo 'title="Delete Transaction">';
        echo '<i class="fas fa-trash-alt"></i>';
        echo '</button>';
        echo '</div>';
        echo '</td>';
        echo '</tr>';
    }
    if (empty($supplyInOutItems)) {
        echo '<tr><td colspan="5" class="text-center text-muted" style="padding: 2rem;">No supply transactions found.</td></tr>';
    }
}
