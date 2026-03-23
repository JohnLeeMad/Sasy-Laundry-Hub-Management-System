<?php
session_start();
include 'req/admin-auth-check.php';

$header = 'Dashboard Overview';
ob_start();
require_once 'req/dashboard.php';
require_once 'req/admin-profile.php';

$period = isset($_GET['period']) ? $_GET['period'] : 'week';

$chartData = getChartData($conn, $period);

$lowStockAlerts = getLowStockAlerts($conn);

$totalRegisteredCustomers = getTotalRegisteredCustomers($conn);
?>

<div class="container-fluid px-4 py-4">
    <link href="assets/css/dashboard.css" rel="stylesheet">

    <?php if (isset($_SESSION['success'])): ?>
        <?php renderAlert('success', $_SESSION['success']); ?>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <?php renderAlert('error', $_SESSION['error']); ?>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1" style="color: var(--primary-color);">Welcome back, Admin <?php echo htmlspecialchars($_SESSION['admin_details']['name'] ?? 'Admin'); ?>!</h2>
                    <p class="text-muted mb-0"><?php echo date('l, F j, Y'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card card-stat card-profit shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">TOTAL SALES</h6>
                            <h2 class="text-white mb-0">₱<?php echo number_format($totalProfitToday, 2); ?></h2>
                            <span class="text-white-50 small">Today</span>
                        </div>
                        <div class="icon-wrapper bg-white bg-opacity-20">
                            <i class="fas fa-money-bill-wave text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-stat card-customers shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">TOTAL CUSTOMERS</h6>
                            <h2 class="text-white mb-0"><?php echo number_format($totalCustomersToday); ?></h2>
                            <span class="text-white-50 small">Today</span>
                        </div>
                        <div class="icon-wrapper bg-white bg-opacity-20">
                            <i class="fas fa-users text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-stat card-claimed shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">CLAIMED LAUNDRY</h6>
                            <h2 class="text-white mb-0"><?php echo number_format($totalClaimedToday); ?></h2>
                            <span class="text-white-50 small">Today</span>
                        </div>
                        <div class="icon-wrapper bg-white bg-opacity-20">
                            <i class="fas fa-tshirt text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <a href="customers.php" class="text-decoration-none">
                <div class="card card-stat card-registered shadow-sm h-100 card-clickable">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-2">CUSTOMER ACCOUNTS</h6>
                                <h2 class="text-white mb-0"><?php echo number_format($totalRegisteredCustomers); ?></h2>
                                <span class="text-white-50 small">View All <i class="fas fa-arrow-right ms-1"></i></span>
                            </div>
                            <div class="icon-wrapper bg-white bg-opacity-20">
                                <i class="fas fa-user-check text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0" style="color: var(--primary-color);">
                            Sales - Last <?php echo $period === 'week' ? '7 Days' : '30 Days'; ?>
                        </h5>
                        <div class="period-filter">
                            <a href="?period=week" class="btn <?php echo $period === 'week' ? 'active' : ''; ?>">Week</a>
                            <a href="?period=month" class="btn <?php echo $period === 'month' ? 'active' : ''; ?>">Month</a>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start justify-content-between mb-4">
                        <div>
                            <h5 class="mb-1 fw-bold" style="color: var(--primary-color);">Stock Alerts</h5>
                            <p class="text-muted small mb-0">Inventory monitoring</p>
                        </div>
                    </div>

                    <?php if (!empty($lowStockAlerts)): ?>
                        <div class="stock-alerts-list">
                            <?php foreach ($lowStockAlerts as $alert): ?>
                                <div class="stock-alert-item mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="stock-icon <?php echo $alert['stock_quantity'] == 0 ? 'critical' : 'warning'; ?>">
                                            <i class="fas fa-box"></i>
                                        </div>

                                        <div class="flex-grow-1">
                                            <h6 class="mb-0 fw-semibold text-dark"><?php echo htmlspecialchars($alert['product_name']); ?></h6>
                                            <small class="text-muted"><?php echo htmlspecialchars($alert['category_name']); ?></small>
                                        </div>

                                        <div class="text-end">
                                            <span class="stock-quantity <?php echo $alert['stock_quantity'] == 0 ? 'critical' : 'warning'; ?>">
                                                <?php echo $alert['stock_quantity']; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <a href="inventory.php" class="btn btn-primary w-100 mt-3">
                            <i class="fas fa-warehouse me-2"></i>Manage Inventory
                        </a>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <div class="empty-icon mb-3">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h6 class="fw-semibold text-dark mb-2">All Stocked Up</h6>
                            <p class="text-muted small mb-3">Your inventory levels look great</p>
                            <a href="inventory.php" class="btn btn-outline-primary">
                                View Inventory
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');

    const dataValues = <?php echo json_encode($chartData['values']); ?>;
    const maxValue = Math.max(...dataValues);
    const suggestedMax = maxValue === 0 ? 100 : Math.ceil(maxValue / 100) * 100;

    const revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chartData['labels']); ?>,
            datasets: [{
                label: 'Revenue',
                data: dataValues,
                backgroundColor: 'rgba(177, 0, 124, 0.1)',
                borderColor: '#c7345c',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '₱' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: suggestedMax,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toFixed(0);
                        },
                        stepSize: suggestedMax <= 100 ? 50 : Math.ceil(suggestedMax / 5 / 100) * 100
                    },
                    grid: {
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        maxTicksLimit: <?php echo $period === 'month' ? 15 : 7; ?>,
                        callback: function(value, index, values) {
                            if (<?php echo $period === 'month' ? 'true' : 'false'; ?>) {
                                return index % 3 === 0 ? this.getLabelForValue(value) : '';
                            }
                            return this.getLabelForValue(value);
                        }
                    }
                }
            }
        }
    });
</script>

<?php
$slot = ob_get_clean();
include '../layouts/app.php';

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

function getChartData($conn, $period = 'week')
{
    if ($period === 'month') {
        return getMonthlyProfitData($conn);
    } else {
        return getWeeklyProfitData($conn);
    }
}

function getWeeklyProfitData($conn)
{
    $query = "SELECT 
                DATE_FORMAT(created_at, '%a') as day, 
                COALESCE(SUM(total_price), 0) as total 
              FROM laundry_lists 
              WHERE payment_status = 'Paid' 
              AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
              GROUP BY DATE(created_at), DATE_FORMAT(created_at, '%a')
              ORDER BY DATE(created_at)";

    $result = $conn->query($query);
    $labels = [];
    $values = [];

    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['day'];
        $values[] = (float)$row['total'];
    }

    $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    $completeLabels = [];
    $completeValues = [];

    $existingData = [];
    foreach ($labels as $index => $label) {
        $existingData[$label] = $values[$index];
    }

    foreach ($days as $day) {
        $completeLabels[] = $day;
        $completeValues[] = isset($existingData[$day]) ? $existingData[$day] : 0;
    }

    return [
        'labels' => $completeLabels,
        'values' => $completeValues
    ];
}

function getMonthlyProfitData($conn)
{
    $dateRange = [];
    for ($i = 29; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $dateRange[$date] = [
            'label' => date('M d', strtotime($date)),
            'value' => 0
        ];
    }

    $startDate = date('Y-m-d', strtotime('-29 days'));

    $query = "SELECT 
                DATE(created_at) as date,
                COALESCE(SUM(total_price), 0) as total 
              FROM laundry_lists 
              WHERE payment_status = 'Paid' 
              AND DATE(created_at) >= ?
              GROUP BY DATE(created_at)
              ORDER BY DATE(created_at)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $startDate);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $dateKey = $row['date'];
        if (isset($dateRange[$dateKey])) {
            $dateRange[$dateKey]['value'] = (float)$row['total'];
        }
    }

    $labels = [];
    $values = [];
    foreach ($dateRange as $data) {
        $labels[] = $data['label'];
        $values[] = $data['value'];
    }

    return [
        'labels' => $labels,
        'values' => $values
    ];
}
?>