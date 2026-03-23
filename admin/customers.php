<?php
session_start();
include 'req/admin-auth-check.php';

$header = 'Customer Accounts Management';  // Dynamic Header
ob_start();  // Start output buffering to capture the page's content

require_once '../config/db_conn.php'; // Include database connection

// Fetch all users
$query = "SELECT id, name, contact_num, email, type, created_at, updated_at FROM users ORDER BY id ASC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$registeredUsers = [];
$walkInUsers = [];
while ($row = mysqli_fetch_assoc($result)) {
    if ($row['type'] === 'registered') {
        $row['source_table'] = 'users';
        $registeredUsers[] = $row;
    } elseif ($row['type'] === 'walk-in') {
        $row['source_table'] = 'users';
        $walkInUsers[] = $row;
    }
}

// Helper functions
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

function renderStatusBadge($type)
{
    $typeColors = [
        'registered' => 'dark',
        'walk-in' => 'info'
    ];
    $color = $typeColors[$type] ?? 'secondary';
    return '<span class="badge bg-' . $color . '">' . htmlspecialchars(ucfirst($type)) . '</span>';
}

function renderTableRow($user)
{
    echo '
    <tr>
        <td class="text-center">
            Created: ' . date("M d, Y - h:i A", strtotime($user['created_at'])) . '<br>
            Modified: ' . date("M d, Y - h:i A", strtotime($user['updated_at'])) . '
        </td>
        <td class="text-center">' . htmlspecialchars($user['name']) . '</td>
        <td class="text-center">' . htmlspecialchars($user['email']) . '</td>
        <td class="text-center">' . htmlspecialchars($user['contact_num']) . '</td>
        <td class="text-center">
            <div class="d-flex gap-2 justify-content-center align-items-center">
                <button
                    class="modern-action-btn edit-user-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#editUserModal"
                    data-id="' . htmlspecialchars($user['id']) . '"
                    data-name="' . htmlspecialchars($user['name']) . '"
                    data-contact-num="' . htmlspecialchars($user['contact_num']) . '"
                    data-email="' . htmlspecialchars($user['email']) . '"
                    data-type="' . htmlspecialchars($user['type']) . '"
                    data-source-table="' . htmlspecialchars($user['source_table']) . '"
                    title="Edit Customer">
                    <i class="fas fa-edit"></i>
                </button>
                <button
                    class="modern-action-btn modern-action-btn-danger"
                    onclick="confirmDeleteCustomer(' . $user['id'] . ', \'' . htmlspecialchars(addslashes($user['name'])) . '\', \'' . htmlspecialchars(addslashes($user['email'])) . '\')"
                    title="Delete Customer">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </td>
    </tr>';
}
?>

<link href="../admin/assets/css/laundry-modals.css" rel="stylesheet">
<link href="../admin/assets/css/laundry-actions.css" rel="stylesheet">

<div class="container-fluid">
    <!-- Success and Error Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <?php renderAlert('success', $_SESSION['success']); ?>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <?php renderAlert('error', $_SESSION['error']); ?>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Registered Customers Table -->
    <div class="card mt-4 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center" style="background-color: var(--primary-color); color: white;">
            <h3 class="mb-0">Registered Customers</h3>
            <div class="d-flex align-items-center">
                <div class="input-group input-group-sm me-2" style="width: 200px;">
                    <span class="input-group-text" style="background-color: var(--accent-color); color: white;">
                        <i class="fas fa-search"></i>
                    </span>
                    <input
                        type="text"
                        id="searchRegisteredUsers"
                        placeholder="Search..."
                        class="form-control"
                        onkeyup="filterRegisteredUsersTable()">
                </div>
                <button
                    class="btn btn-light"
                    style="background-color: var(--accent-color); color: white; white-space: nowrap;"
                    data-bs-toggle="modal"
                    data-bs-target="#addUserModal">
                    <i class="fas fa-plus me-2"></i> Add Customer
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered" id="registeredUsersTable">
                    <thead style="background-color: var(--primary-color); color: white;">
                        <tr>
                            <th class="sortable text-center" data-sort-col="0" data-sort-dir="asc">Dates <i class="fas fa-sort"></i></th>
                            <th class="sortable text-center" data-sort-col="1" data-sort-dir="asc">Name <i class="fas fa-sort"></i></th>
                            <th class="sortable text-center" data-sort-col="2" data-sort-dir="asc">Email <i class="fas fa-sort"></i></th>
                            <th class="text-center">Phone</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($registeredUsers)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No registered customers found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($registeredUsers as $user): ?>
                                <?php renderTableRow($user); ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr id="noRegisteredSearchResults" style="display: none;">
                            <td colspan="6" class="text-center">No registered customers found matching your search criteria.</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Walk-In Customers Table -->
    <div class="card mt-4 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center bg-secondary text-white">
            <h3 class="mb-0">Walk-In Customers</h3>
            <div class="d-flex align-items-center">
                <div class="input-group input-group-sm" style="width: 200px;">
                    <span class="input-group-text" style="background-color: var(--accent-color); color: white;">
                        <i class="fas fa-search"></i>
                    </span>
                    <input
                        type="text"
                        id="searchWalkInUsers"
                        placeholder="Search..."
                        class="form-control"
                        onkeyup="filterWalkInUsersTable()">
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered" id="walkInUsersTable">
                    <thead style="background-color: var(--primary-color); color: white;">
                        <tr>
                            <th class="sortable text-center" data-sort-col="0" data-sort-dir="asc">Dates <i class="fas fa-sort"></i></th>
                            <th class="sortable text-center" data-sort-col="1" data-sort-dir="asc">Name <i class="fas fa-sort"></i></th>
                            <th class="sortable text-center" data-sort-col="2" data-sort-dir="asc">Email <i class="fas fa-sort"></i></th>
                            <th class="text-center">Phone</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($walkInUsers)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No walk-in customers found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($walkInUsers as $user): ?>
                                <?php renderTableRow($user); ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr id="noWalkInSearchResults" style="display: none;">
                            <td colspan="6" class="text-center">No walk-in customers found matching your search criteria.</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$slot = ob_get_clean();  // Store the output content in $slot
include '../layouts/app.php';  // Include the layout
?>
<?php include 'modals/edit-user.php'; ?>
<?php include 'modals/add-user.php'; ?>

<script src="../assets/js/jquery.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>

<script>
    // Search functionality for registered users table
    function filterRegisteredUsersTable() {
        const input = document.getElementById("searchRegisteredUsers");
        const filter = input.value.toUpperCase();
        const table = document.getElementById("registeredUsersTable");
        const rows = table.querySelectorAll('tbody tr');
        const noResultsRow = document.getElementById("noRegisteredSearchResults");

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

        // Show or hide the "no results" message
        if (visibleRows === 0 && filter !== "") {
            noResultsRow.style.display = "table-row";
        } else {
            noResultsRow.style.display = "none";
        }
    }

    // Search functionality for walk-in users table
    function filterWalkInUsersTable() {
        const input = document.getElementById("searchWalkInUsers");
        const filter = input.value.toUpperCase();
        const table = document.getElementById("walkInUsersTable");
        const rows = table.querySelectorAll('tbody tr');
        const noResultsRow = document.getElementById("noWalkInSearchResults");

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

        // Show or hide the "no results" message
        if (visibleRows === 0 && filter !== "") {
            noResultsRow.style.display = "table-row";
        } else {
            noResultsRow.style.display = "none";
        }
    }

    // Sorting functionality for registered users table
    document.querySelectorAll('#registeredUsersTable .sortable').forEach(header => {
        header.addEventListener('click', () => {
            const table = document.getElementById('registeredUsersTable');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr:not(#noRegisteredSearchResults):not([style*="display: none"])'));
            const colIndex = parseInt(header.dataset.sortCol);
            let dir = header.dataset.sortDir === 'asc' ? 1 : -1;

            // Toggle sort direction
            header.dataset.sortDir = header.dataset.sortDir === 'asc' ? 'desc' : 'asc';

            // Update sort icons
            document.querySelectorAll('#registeredUsersTable .sortable i').forEach(icon => {
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
                    const aMatch = aText.match(/Created: (.*?) /);
                    const bMatch = bText.match(/Created: (.*?) /);
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

    // Sorting functionality for walk-in users table
    document.querySelectorAll('#walkInUsersTable .sortable').forEach(header => {
        header.addEventListener('click', () => {
            const table = document.getElementById('walkInUsersTable');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr:not(#noWalkInSearchResults):not([style*="display: none"])'));
            const colIndex = parseInt(header.dataset.sortCol);
            let dir = header.dataset.sortDir === 'asc' ? 1 : -1;

            // Toggle sort direction
            header.dataset.sortDir = header.dataset.sortDir === 'asc' ? 'desc' : 'asc';

            // Update sort icons
            document.querySelectorAll('#walkInUsersTable .sortable i').forEach(icon => {
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
                    const aMatch = aText.match(/Created: (.*?) /);
                    const bMatch = bText.match(/Created: (.*?) /);
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
</script>