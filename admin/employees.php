<?php
session_start();
include 'req/admin-auth-check.php';

$header = 'Team Accounts Management';
ob_start();

require_once '../config/db_conn.php';

$showArchived = isset($_GET['show_archived']) && $_GET['show_archived'] === '1';

$currentUserId = $_SESSION['user_id'] ?? null;
$currentUserType = strtolower($_SESSION['user_role'] ?? '') . 's';

$adminQuery = "SELECT id, name, contact_num, email, email_verified_at, password, remember_token, created_at, updated_at, archived FROM admins ORDER BY id ASC";
$adminResult = mysqli_query($conn, $adminQuery);

if (!$adminResult) {
    die("Admin query failed: " . mysqli_error($conn));
}

$staffQuery = "SELECT id, name, contact_num, email, email_verified_at, password, remember_token, created_at, updated_at, archived FROM staffs ORDER BY id ASC";
$staffResult = mysqli_query($conn, $staffQuery);

if (!$staffResult) {
    die("Staff query failed: " . mysqli_error($conn));
}

$employees = [];

while ($row = mysqli_fetch_assoc($adminResult)) {
    $row['role'] = 'Admin';
    $row['source_table'] = 'admins';
    $row['archived'] = isset($row['archived']) ? $row['archived'] : 0;
    $employees[] = $row;
}

while ($row = mysqli_fetch_assoc($staffResult)) {
    $row['role'] = 'Staff';
    $row['source_table'] = 'staffs';
    $row['archived'] = isset($row['archived']) ? $row['archived'] : 0;
    $employees[] = $row;
}

$filteredEmployees = array_filter($employees, function ($employee) use ($showArchived) {
    return $showArchived ? ($employee['archived'] == 1) : ($employee['archived'] == 0);
});

$activeCount = count(array_filter($employees, function ($e) {
    return $e['archived'] == 0;
}));
$archivedCount = count(array_filter($employees, function ($e) {
    return $e['archived'] == 1;
}));

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

function renderStatusBadge($role)
{
    $roleColors = [
        'Admin' => 'dark',
        'Staff' => 'info'
    ];
    $color = $roleColors[$role] ?? 'secondary';
    return '<span class="badge bg-' . $color . '">' . htmlspecialchars($role) . '</span>';
}

function renderTableRow($employee, $showArchived, $currentUserId, $currentUserType)
{
    $isArchived = $employee['archived'] == 1;

    $isOwnAccount = ($employee['id'] == $currentUserId && $employee['source_table'] == $currentUserType);

    echo '
    <tr>
        <td class="text-center">
            Created: ' . date("M d, Y - h:i A", strtotime($employee['created_at'])) . '<br>
            Modified: ' . date("M d, Y - h:i A", strtotime($employee['updated_at'])) . '
        </td>
        <td class="text-center">' . htmlspecialchars($employee['name']) . '</td>
        <td class="text-center">' . htmlspecialchars($employee['email']) . '</td>
        <td class="text-center">' . htmlspecialchars($employee['contact_num']) . '</td>
        <td class="text-center">' . renderStatusBadge($employee['role']) . '</td>
        <td class="text-center">
            <div class="d-flex gap-2 justify-content-center align-items-center">';

    if (!$isArchived) {
        echo '
                <button
                    class="modern-action-btn edit-employee-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#editEmployeeModal"
                    data-id="' . htmlspecialchars($employee['id']) . '"
                    data-name="' . htmlspecialchars($employee['name']) . '"
                    data-contact-num="' . htmlspecialchars($employee['contact_num']) . '"
                    data-email="' . htmlspecialchars($employee['email']) . '"
                    data-role="' . htmlspecialchars($employee['role']) . '"
                    data-type="' . strtolower(htmlspecialchars($employee['role'])) . '"
                    data-source-table="' . htmlspecialchars($employee['source_table']) . '"
                    data-is-own-account="' . ($isOwnAccount ? '1' : '0') . '"
                    title="Edit Employee">
                    <i class="fas fa-edit"></i>
                </button>';

        if (!$isOwnAccount) {
            echo '
                <button
                    class="modern-action-btn modern-action-btn-warning"
                    onclick="confirmArchiveEmployee(' . $employee['id'] . ', \'' . htmlspecialchars(addslashes($employee['name'])) . '\', \'' . htmlspecialchars($employee['role']) . '\', \'archive\')"
                    title="Archive Employee">
                    <i class="fas fa-archive"></i>
                </button>';
        } else {
            echo '
                <button
                    class="modern-action-btn modern-action-btn-warning"
                    disabled
                    title="Cannot archive your own account"
                    style="opacity: 0.5; cursor: not-allowed;">
                    <i class="fas fa-archive"></i>
                </button>';
        }
    } else {
        echo '
                <button
                    class="modern-action-btn modern-action-btn-success"
                    onclick="confirmArchiveEmployee(' . $employee['id'] . ', \'' . htmlspecialchars(addslashes($employee['name'])) . '\', \'' . htmlspecialchars($employee['role']) . '\', \'restore\')"
                    title="Restore Employee">
                    <i class="fas fa-undo"></i>
                </button>';
    }

    if (!$isOwnAccount) {
        echo '
                <button
                    class="modern-action-btn modern-action-btn-danger"
                    onclick="confirmDeleteEmployee(' . $employee['id'] . ', \'' . htmlspecialchars(addslashes($employee['name'])) . '\', \'' . htmlspecialchars($employee['role']) . '\')"
                    title="Delete Employee">
                    <i class="fas fa-trash"></i>
                </button>';
    } else {
        echo '
                <button
                    class="modern-action-btn modern-action-btn-danger"
                    disabled
                    title="Cannot delete your own account"
                    style="opacity: 0.5; cursor: not-allowed;">
                    <i class="fas fa-trash"></i>
                </button>';
    }

    echo '
            </div>
        </td>
    </tr>';
}
?>
<link href="../admin/assets/css/laundry-modals.css" rel="stylesheet">
<link href="../admin/assets/css/laundry-actions.css" rel="stylesheet">

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

    .archive-toggle {
        background-color: var(--accent-color);
        color: white;
        border: none;
        padding: 0.375rem 0.75rem;
        border-radius: 0.25rem;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .archive-toggle:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }

    .archive-toggle.active {
        background-color: #28a745;
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
            <h3 class="mb-0">Team Accounts</h3>
            <div class="d-flex align-items-center gap-2">
                <button
                    class="archive-toggle <?php echo $showArchived ? 'active' : ''; ?>"
                    onclick="toggleArchived()">
                    <i class="fas <?php echo $showArchived ? 'fa-eye-slash' : 'fa-archive'; ?> me-1"></i>
                    <?php echo $showArchived ? 'Show Active' : 'Show Archived'; ?>
                    <span class="badge bg-light text-dark ms-1">
                        <?php echo $showArchived ? $activeCount : $archivedCount; ?>
                    </span>
                </button>

                <div class="input-group input-group-sm" style="width: 200px;">
                    <span class="input-group-text" style="background-color: var(--accent-color); color: white;">
                        <i class="fas fa-search"></i>
                    </span>
                    <input
                        type="text"
                        id="searchEmployees"
                        placeholder="Search..."
                        class="form-control"
                        onkeyup="filterEmployeesTable()">
                </div>

                <button
                    class="btn btn-light"
                    style="background-color: var(--accent-color); color: white; white-space: nowrap;"
                    data-bs-toggle="modal"
                    data-bs-target="#addEmployeeModal">
                    <i class="fas fa-plus me-2"></i> Add Team Member
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered" id="employeesTable">
                    <thead style="background-color: var(--primary-color); color: white;">
                        <tr>
                            <th class="sortable text-center" data-sort-col="0" data-sort-dir="asc">Dates <i class="fas fa-sort"></i></th>
                            <th class="sortable text-center" data-sort-col="1" data-sort-dir="asc">Name <i class="fas fa-sort"></i></th>
                            <th class="sortable text-center" data-sort-col="2" data-sort-dir="asc">Email <i class="fas fa-sort"></i></th>
                            <th class="text-center">Phone</th>
                            <th class="text-center">Role</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($filteredEmployees)): ?>
                            <tr>
                                <td colspan="6" class="text-center">
                                    <?php echo $showArchived ? 'No archived account found.' : 'No active account found.'; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($filteredEmployees as $employee): ?>
                                <?php renderTableRow($employee, $showArchived, $currentUserId, $currentUserType); ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr id="noSearchResults" style="display: none;">
                            <td colspan="6" class="text-center">No account found matching your search criteria.</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$slot = ob_get_clean();
include '../layouts/app.php';
?>
<?php include 'modals/edit-employee.php'; ?>
<?php include 'modals/add-employee.php'; ?>

<script src="../assets/js/jquery.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>

<script>
    function toggleArchived() {
        const currentUrl = new URL(window.location.href);
        const currentState = currentUrl.searchParams.get('show_archived');

        if (currentState === '1') {
            currentUrl.searchParams.delete('show_archived');
        } else {
            currentUrl.searchParams.set('show_archived', '1');
        }

        window.location.href = currentUrl.toString();
    }

    function filterEmployeesTable() {
        const input = document.getElementById("searchEmployees");
        const filter = input.value.toUpperCase();
        const table = document.getElementById("employeesTable");
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

    document.querySelectorAll('#employeesTable .sortable').forEach(header => {
        header.addEventListener('click', () => {
            const table = document.getElementById('employeesTable');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr:not(#noSearchResults):not([style*="display: none"])'));
            const colIndex = parseInt(header.dataset.sortCol);
            let dir = header.dataset.sortDir === 'asc' ? 1 : -1;

            header.dataset.sortDir = header.dataset.sortDir === 'asc' ? 'desc' : 'asc';

            document.querySelectorAll('#employeesTable .sortable i').forEach(icon => {
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

                return dir * aText.localeCompare(bText, undefined, {
                    numeric: true,
                    sensitivity: 'base'
                });
            });

            rows.forEach(row => tbody.appendChild(row));
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const editButtons = document.querySelectorAll('.edit-employee-btn');
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const email = this.getAttribute('data-email');
                const contactNum = this.getAttribute('data-contact-num');
                const type = this.getAttribute('data-type');
                const sourceTable = this.getAttribute('data-source-table');
                const role = this.getAttribute('data-role');
                const isOwnAccount = this.getAttribute('data-is-own-account') === '1';

                document.getElementById('edit_id').value = id;
                document.getElementById('edit_name').value = name;
                document.getElementById('edit_email').value = email;
                document.getElementById('edit_contact_num').value = contactNum;
                document.getElementById('edit_type').value = type;
                document.getElementById('edit_source_table').value = sourceTable;
                document.getElementById('edit_role').value = role;
                document.getElementById('edit_is_own_account').value = isOwnAccount ? '1' : '0';

                document.getElementById('editEmployeeModalLabel').textContent = 'Edit ' + role;

                const warningDiv = document.getElementById('edit_own_account_warning');
                const roleSelect = document.getElementById('edit_role');
                const roleHelp = document.getElementById('edit_role_help');

                if (isOwnAccount) {
                    warningDiv.style.display = 'block';
                    roleSelect.disabled = true;
                    roleHelp.style.display = 'block';
                } else {
                    warningDiv.style.display = 'none';
                    roleSelect.disabled = false;
                    roleHelp.style.display = 'none';
                }
            });
        });
    });
</script>