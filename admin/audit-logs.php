<?php
session_start();
include 'req/admin-auth-check.php';

$header = 'Audit Logs';
ob_start();
require_once '../config/db_conn.php';

// Set Philippine timezone
date_default_timezone_set('Asia/Manila');

// Set UTF-8 encoding for database connection
mysqli_set_charset($conn, "utf8mb4");

// Set default dates to today
$defaultStartDate = date('Y-m-d');
$defaultEndDate = date('Y-m-d');

// Get filters with today as default
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : $defaultStartDate;
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : $defaultEndDate;
$periodFilter = isset($_GET['periodFilter']) ? $_GET['periodFilter'] : '1'; // Default to Today
$userType = isset($_GET['user_type']) ? $_GET['user_type'] : '';
$actionFilter = isset($_GET['action']) ? $_GET['action'] : '';
$userId = isset($_GET['user_id']) ? $_GET['user_id'] : '';

// Check if PDF export is requested
if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
    exportAuditLogsToPDF();
    exit;
}

// Build query with filters
$query = "SELECT al.* 
          FROM audit_logs al 
          WHERE DATE(al.created_at) BETWEEN ? AND ?";
$params = array($startDate, $endDate);
$paramTypes = "ss";

if (!empty($userType)) {
    $query .= " AND al.user_type = ?";
    $params[] = $userType;
    $paramTypes .= "s";
}

if (!empty($actionFilter)) {
    $query .= " AND al.action = ?";
    $params[] = $actionFilter;
    $paramTypes .= "s";
}

if (!empty($userId)) {
    $query .= " AND al.user_id = ?";
    $params[] = $userId;
    $paramTypes .= "i";
}

$query .= " ORDER BY al.created_at DESC";

// Prepare and execute query
$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($paramTypes, ...$params);
}
$stmt->execute();
$auditLogs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get distinct actions for filter dropdown
$actionsQuery = "SELECT DISTINCT action FROM audit_logs ORDER BY action";
$actionsResult = $conn->query($actionsQuery);
$actions = $actionsResult->fetch_all(MYSQLI_ASSOC);

// Get staff and admin lists for filter
$usersQuery = "SELECT id, name, 'staff' as type FROM staffs 
               UNION 
               SELECT id, name, 'admin' as type FROM admins 
               ORDER BY name";
$usersResult = $conn->query($usersQuery);
$users = $usersResult->fetch_all(MYSQLI_ASSOC);

// Set proper content type header
header('Content-Type: text/html; charset=UTF-8');

// PDF Export Function
function exportAuditLogsToPDF()
{
    global $conn, $startDate, $endDate, $userType, $actionFilter, $userId;

    // Rebuild the query for PDF export to ensure data consistency
    $query = "SELECT al.* 
              FROM audit_logs al 
              WHERE DATE(al.created_at) BETWEEN ? AND ?";
    $params = array($startDate, $endDate);
    $paramTypes = "ss";

    if (!empty($userType)) {
        $query .= " AND al.user_type = ?";
        $params[] = $userType;
        $paramTypes .= "s";
    }

    if (!empty($actionFilter)) {
        $query .= " AND al.action = ?";
        $params[] = $actionFilter;
        $paramTypes .= "s";
    }

    if (!empty($userId)) {
        $query .= " AND al.user_id = ?";
        $params[] = $userId;
        $paramTypes .= "i";
    }

    $query .= " ORDER BY al.created_at DESC";

    $stmt = $conn->prepare($query);
    if ($params) {
        $stmt->bind_param($paramTypes, ...$params);
    }
    $stmt->execute();
    $auditLogs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Include TCPDF library
    require_once('../vendor/TCPDF-main/tcpdf.php');

    // Create new PDF document
    $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('Audit System');
    $pdf->SetAuthor('Sasy Laundry Hub');
    $pdf->SetTitle('Audit Logs Report');
    $pdf->SetSubject('Audit Logs Export');

    // Set default header data
    $logoPath = '../logo.jpg'; // Ensure this path is correct
    $pdf->SetHeaderData($logoPath, 15, 'Audit Logs Report', 'Period: ' . date('F d, Y', strtotime($startDate)) . ' - ' . date('F d, Y', strtotime($endDate)) . "\nSasy Laundry Hub");

    // Set header and footer fonts
    $pdf->setHeaderFont(array('dejavusans', 'B', 12));
    $pdf->setFooterFont(array('dejavusans', '', PDF_FONT_SIZE_DATA));

    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, 30, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(10);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // Add a page
    $pdf->AddPage();

    // Set Unicode-compatible font
    $pdf->SetFont('dejavusans', '', 9);

    // Define colors
    $primaryColor = '#644499';
    $accentColor = '#c7345c';
    $lightGray = '#f0f0f0';

    // Report details
    $html = '<h2 style="color: ' . $primaryColor . '; text-align: center; border-bottom: 2px solid ' . $accentColor . '; padding-bottom: 5px;">AUDIT LOGS REPORT</h2>';

    // Report period and filters
    $html .= '<table border="0" cellpadding="3" cellspacing="0" style="margin-bottom: 10px;">';
    $html .= '<tr><td width="20%"><strong>Generated:</strong></td><td width="30%">' . date('F j, Y g:i A') . '</td>';
    $html .= '<td width="20%"><strong>Total Records:</strong></td><td width="30%">' . count($auditLogs) . '</td></tr>';
    $html .= '<tr><td><strong>Period:</strong></td><td>' . date('F d, Y', strtotime($startDate)) . ' to ' . date('F d, Y', strtotime($endDate)) . '</td>';

    // Filters applied
    $filters = '';
    if (!empty($userType)) $filters .= 'User Type: ' . ucfirst($userType) . ', ';
    if (!empty($actionFilter)) $filters .= 'Action: ' . ucfirst(str_replace('_', ' ', $actionFilter)) . ', ';
    if (!empty($userId)) $filters .= 'Specific User, ';
    if (!empty($filters)) {
        $filters = rtrim($filters, ', ');
    } else {
        $filters = 'None';
    }

    $html .= '<td><strong>Filters:</strong></td><td>' . $filters . '</td></tr>';
    $html .= '</table>';

    // Create table
    $html .= '<table border="1" cellpadding="4" cellspacing="0" style="border-collapse: collapse;">';

    // Table header
    $html .= '<tr style="background-color: ' . $lightGray . '; color: ' . $primaryColor . ';">';
    $html .= '<th width="20%" align="center"><strong>Date & Time</strong></th>';
    $html .= '<th width="20%" align="center"><strong>User</strong></th>';
    $html .= '<th width="10%" align="center"><strong>Type</strong></th>';
    $html .= '<th width="15%" align="center"><strong>Action</strong></th>';
    $html .= '<th width="35%" align="center"><strong>Description</strong></th>';
    $html .= '</tr>';

    // Table data
    if (count($auditLogs) > 0) {
        foreach ($auditLogs as $log) {
            $html .= '<tr>';
            $html .= '<td>' . date('M d, Y h:i A', strtotime($log['created_at'])) . '</td>';
            $html .= '<td>' . htmlspecialchars($log['user_name'], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td align="center">' . ucfirst($log['user_type']) . '</td>';
            $html .= '<td>' . ucfirst(str_replace('_', ' ', $log['action'])) . '</td>';
            $html .= '<td>' . htmlspecialchars($log['description'], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '</tr>';
        }
    } else {
        $html .= '<tr><td colspan="5" align="center" style="padding: 20px;">No audit logs found for the selected filters.</td></tr>';
    }

    $html .= '</table>';

    // Add footer note
    $html .= '<div style="text-align: center; margin-top: 20px; color: ' . $primaryColor . '; font-size: 8px;">';
    $html .= '<hr style="border-top: 1px solid ' . $accentColor . ';">';
    $html .= 'This is an official audit report. Modifying this document is prohibited.<br>';
    $html .= 'Generated by Audit System - Sasy Laundry Hub on ' . date('F j, Y g:i A');
    $html .= '</div>';

    // Print HTML content
    $pdf->writeHTML($html, true, false, true, false, '');

    // Generate filename
    $filename = 'Audit_Logs_' . date('Y-m-d_His') . '.pdf';

    // Output PDF
    $pdf->Output($filename, 'D');
    exit;
}
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

    <!-- Filter Form -->
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
                <div class="col-md-2">
                    <label class="form-label">User Type</label>
                    <select class="form-select form-select-sm" name="user_type">
                        <option value="">All Types</option>
                        <option value="admin" <?= $userType === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="staff" <?= $userType === 'staff' ? 'selected' : '' ?>>Staff</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Action</label>
                    <select class="form-select form-select-sm" name="action">
                        <option value="">All Actions</option>
                        <?php foreach ($actions as $action): ?>
                            <option value="<?= $action['action'] ?>" <?= $actionFilter === $action['action'] ? 'selected' : '' ?>>
                                <?= ucfirst(str_replace('_', ' ', $action['action'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">User</label>
                    <select class="form-select form-select-sm" name="user_id">
                        <option value="">All Users</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>" <?= $userId == $user['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['name']) ?> (<?= $user['type'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <a href="?export=pdf&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>&user_type=<?= $userType ?>&action=<?= $actionFilter ?>&user_id=<?= $userId ?>&periodFilter=<?= $periodFilter ?>"
                        class="btn btn-primary btn-sm">
                        <i class="fas fa-file-pdf me-2"></i>Export PDF
                    </a>
                    <?php if (isset($_GET['start_date']) || isset($_GET['end_date']) || isset($_GET['user_type']) || isset($_GET['action']) || isset($_GET['user_id']) || isset($_GET['periodFilter'])): ?>
                        <a href="audit-logs.php" class="btn btn-outline-secondary btn-sm">Clear Filters</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Activity Logs</h5>
            <span class="badge" style="background-color: #644499;"><?= count($auditLogs) ?> records found</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <div class="container my-4">
                    <div class="table-responsive shadow-sm rounded">
                        <table class="table table-striped table-hover align-middle mb-0" id="auditTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Date & Time</th>
                                    <th>User</th>
                                    <th>Type</th>
                                    <th>Action</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($auditLogs) > 0): ?>
                                    <?php foreach ($auditLogs as $log): ?>
                                        <tr>
                                            <td><?= date('M d, Y h:i A', strtotime($log['created_at'])) ?></td>
                                            <td><?= htmlspecialchars($log['user_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <span class="badge bg-<?= $log['user_type'] === 'admin' ? 'warning' : 'info' ?>">
                                                    <?= ucfirst($log['user_type']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?= ucfirst(str_replace('_', ' ', $log['action'])) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($log['description'], ENT_QUOTES, 'UTF-8') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4">No audit logs found for the selected filters.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
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

    // Auto-submit when other filter dropdowns change
    document.querySelectorAll('select[name="user_type"], select[name="action"], select[name="user_id"]').forEach(select => {
        select.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
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
?>