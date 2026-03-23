<?php
session_start();
include 'req/admin-auth-check.php';

$header = 'Customer Reviews';  // Dynamic Header
ob_start();  // Start output buffering to capture the page's content
require_once '../config/db_conn.php';

// Get ALL reviews including archived ones for the admin view
$sql = "SELECT r.*, 
               ll.queue_number, 
               ll.created_at as order_date,
               c.name as customer_name,
               c.contact_num as customer_phone
        FROM reviews r
        LEFT JOIN laundry_lists ll ON r.laundry_list_id = ll.id
        LEFT JOIN users c ON r.customer_id = c.id
        WHERE r.rating > 0
        ORDER BY r.created_at DESC";

$result = $conn->query($sql);
$reviews = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }
}

// Pagination variables
$total = count($reviews); // Total records
$perPage = 10; // Records per page
$totalPages = ceil($total / $perPage); // Total pages
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page
$start = ($page - 1) * $perPage; // Start record index

// Get filter values
$ratingFilter = isset($_GET['rating_filter']) ? $_GET['rating_filter'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';

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

    $ratingFilter = isset($_GET['rating_filter']) ? $_GET['rating_filter'] : 'all';
    $search = isset($_GET['search']) ? $_GET['search'] : '';

    echo '<nav aria-label="Page navigation">';
    echo '<ul class="pagination justify-content-center mt-3 custom-pagination">';

    // Previous button
    $prevDisabled = $currentPage <= 1 ? 'disabled' : '';
    echo '<li class="page-item ' . $prevDisabled . '">';
    echo '<a class="page-link" href="?page=' . ($currentPage - 1) . '&rating_filter=' . urlencode($ratingFilter) .
        '&search=' . urlencode($search) . '" tabindex="-1">Previous</a>';
    echo '</li>';

    // Page numbers
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = $i == $currentPage ? 'active' : '';
        echo '<li class="page-item ' . $active . '">';
        echo '<a class="page-link" href="?page=' . $i . '&rating_filter=' . urlencode($ratingFilter) .
            '&search=' . urlencode($search) . '">' . $i . '</a>';
        echo '</li>';
    }

    // Next button
    $nextDisabled = $currentPage >= $totalPages ? 'disabled' : '';
    echo '<li class="page-item ' . $nextDisabled . '">';
    echo '<a class="page-link" href="?page=' . ($currentPage + 1) . '&rating_filter=' . urlencode($ratingFilter) .
        '&search=' . urlencode($search) . '">Next</a>';
    echo '</li>';

    echo '</ul>';
    echo '</nav>';
}

function renderStarRating($rating)
{
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $stars .= '<i class="fas fa-star text-warning"></i>';
        } else {
            $stars .= '<i class="far fa-star text-warning"></i>';
        }
    }
    return '<div class="star-rating-display">' . $stars . '</div>';
}

function renderTableRow($review)
{
    $isArchived = $review['is_archived'] ?? false;
    $rowClass = $isArchived ? 'table-secondary' : '';
    $archivedText = $isArchived ? '<span class="badge bg-warning text-dark ms-1">Archived</span>' : '';

    echo '
    <tr class="' . $rowClass . '">
        <td class="text-center">
            <form method="POST" action="req/archive-review.php" class="d-inline-block">
                <input type="hidden" name="review_id" value="' . htmlspecialchars($review['id']) . '">
                <input type="hidden" name="is_archived" value="' . ($isArchived ? '0' : '1') . '">
                <button
                    type="submit"
                    class="modern-action-btn ' . ($isArchived ? 'modern-action-btn-secondary' : 'modern-action-btn-success') . '"
                    title="' . ($isArchived ? 'Show on Homepage' : 'Hide from Homepage') . '"
                    onclick="return confirmToggleArchive(this)">
                    <i class="fas ' . ($isArchived ? 'fa-eye' : 'fa-eye-slash') . '"></i>
                </button>
            </form>
        </td>
        <td class="text-center">' . date("M d, Y - h:i A", strtotime($review['created_at'])) . '</td>
        <td class="text-center">' . htmlspecialchars($review['customer_name'] ?? 'N/A') . '</td>
        <td class="text-center">' . htmlspecialchars($review['customer_phone'] ?? 'N/A') . '</td>
        <td class="text-center" data-rating="' . $review['rating'] . '">' . renderStarRating($review['rating']) . '</td>
        <td class="text-center">' . nl2br(htmlspecialchars($review['review_text'] ?? 'No review text')) . '</td>
        <td class="text-center">
            <div class="d-flex gap-2 justify-content-center align-items-center">
                <button
                    type="button"
                    class="modern-action-btn modern-action-btn-danger"
                    onclick="confirmDeleteReview(' . $review['id'] . ', \'' . htmlspecialchars(addslashes($review['customer_name'])) . '\', ' . $review['rating'] . ', \'' . htmlspecialchars(addslashes($review['review_text'] ?? 'No review text')) . '\')"
                    title="Delete Review">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </td>
    </tr>';
}

// Filter the reviews based on rating_filter and search
function filterReviews($reviews, $ratingFilter, $search = '')
{
    // First filter by rating
    $filtered = array_filter($reviews, function ($review) use ($ratingFilter) {
        if ($ratingFilter == 'all') {
            return true; // Show all reviews
        } else {
            return $review['rating'] == $ratingFilter;
        }
    });

    // Then filter by search term if provided
    if (!empty($search)) {
        $filtered = array_filter($filtered, function ($review) use ($search) {
            $searchLower = strtolower($search);
            return (
                strpos(strtolower($review['customer_name'] ?? ''), $searchLower) !== false ||
                strpos(strtolower($review['queue_number']), $searchLower) !== false ||
                strpos(strtolower($review['review_text'] ?? ''), $searchLower) !== false
            );
        });
    }

    return $filtered;
}

// Filter and paginate the data
$filteredReviews = filterReviews($reviews, $ratingFilter, $search);
$total = count($filteredReviews);
$totalPages = ceil($total / $perPage);
$page = min(max(1, $page), max(1, $totalPages));
$paginatedReviews = array_slice($filteredReviews, ($page - 1) * $perPage, $perPage);

// Get counts for each rating for tabs (including archived)
$ratingCounts = [
    'all' => count($reviews),
    '5' => count(array_filter($reviews, function ($review) {
        return $review['rating'] == 5;
    })),
    '4' => count(array_filter($reviews, function ($review) {
        return $review['rating'] == 4;
    })),
    '3' => count(array_filter($reviews, function ($review) {
        return $review['rating'] == 3;
    })),
    '2' => count(array_filter($reviews, function ($review) {
        return $review['rating'] == 2;
    })),
    '1' => count(array_filter($reviews, function ($review) {
        return $review['rating'] == 1;
    }))
];

?>
<link href="assets/css/laundry-actions.css" rel="stylesheet">
<link href="assets/css/reviews.css" rel="stylesheet">

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
            <h3 class="mb-0">Customer Reviews</h3>
            <div class="d-flex align-items-center">
                <div class="me-3 text-light">
                    <small><i class="fas fa-info-circle me-1"></i>
                        <i class="fas fa-eye text-success"></i> = Show on Homepage |
                        <i class="fas fa-eye-slash text-light"></i> = Hidden
                    </small>
                </div>
                <form method="GET" class="d-flex align-items-center me-2">
                    <input
                        type="hidden"
                        name="rating_filter"
                        value="<?php echo htmlspecialchars($ratingFilter); ?>">
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <span class="input-group-text" style="background-color: var(--accent-color); color: white;">
                            <i class="fas fa-search"></i>
                        </span>
                        <input
                            type="text"
                            id="searchReviews"
                            placeholder="Search by customer, queue, or review..."
                            value="<?php echo htmlspecialchars($search); ?>"
                            class="form-control"
                            onkeyup="filterReviewsTable()">
                    </div>
                </form>
            </div>
        </div>

        <!-- Rating Filter Tabs -->
        <ul class="nav nav-tabs mt-2 px-3 pt-2">
            <li class="nav-item">
                <a class="nav-link rating-tab <?php echo $ratingFilter == 'all' ? 'active fw-bold' : ''; ?>"
                    style="color: <?php echo $ratingFilter == 'all' ? '#644499' : 'black'; ?>;"
                    href="?rating_filter=all&search=<?php echo urlencode($search); ?>">
                    All <span class="badge"><?php echo $ratingCounts['all']; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rating-tab <?php echo $ratingFilter == '5' ? 'active fw-bold' : ''; ?>"
                    style="color: <?php echo $ratingFilter == '5' ? '#644499' : 'black'; ?>;"
                    href="?rating_filter=5&search=<?php echo urlencode($search); ?>">
                    <span class="star-icon">⭐</span>5 <span class="badge"><?php echo $ratingCounts['5']; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rating-tab <?php echo $ratingFilter == '4' ? 'active fw-bold' : ''; ?>"
                    style="color: <?php echo $ratingFilter == '4' ? '#644499' : 'black'; ?>;"
                    href="?rating_filter=4&search=<?php echo urlencode($search); ?>">
                    <span class="star-icon">⭐</span>4 <span class="badge"><?php echo $ratingCounts['4']; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rating-tab <?php echo $ratingFilter == '3' ? 'active fw-bold' : ''; ?>"
                    style="color: <?php echo $ratingFilter == '3' ? '#644499' : 'black'; ?>;"
                    href="?rating_filter=3&search=<?php echo urlencode($search); ?>">
                    <span class="star-icon">⭐</span>3 <span class="badge"><?php echo $ratingCounts['3']; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rating-tab <?php echo $ratingFilter == '2' ? 'active fw-bold' : ''; ?>"
                    style="color: <?php echo $ratingFilter == '2' ? '#644499' : 'black'; ?>;"
                    href="?rating_filter=2&search=<?php echo urlencode($search); ?>">
                    <span class="star-icon">⭐</span>2 <span class="badge"><?php echo $ratingCounts['2']; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rating-tab <?php echo $ratingFilter == '1' ? 'active fw-bold' : ''; ?>"
                    style="color: <?php echo $ratingFilter == '1' ? '#644499' : 'black'; ?>;"
                    href="?rating_filter=1&search=<?php echo urlencode($search); ?>">
                    <span class="star-icon">⭐</span>1 <span class="badge"><?php echo $ratingCounts['1']; ?></span>
                </a>
            </li>
        </ul>

        <div class="card-body">
            <?php if (empty($reviews)): ?>
                <div class="empty-reviews">
                    <i class="fas fa-comment-slash"></i>
                    <h4>No Reviews Yet</h4>
                    <p>Customer reviews will appear here once they start submitting feedback.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered" id="reviewsTable">
                        <thead style="background-color: var(--primary-color); color: white;">
                            <tr>
                                <th class="text-center" style="width: 80px;">Homepage</th>
                                <th class="sortable text-center" data-sort-col="1" data-sort-dir="desc">Review Date <i class="fas fa-sort"></i></th>
                                <th class="sortable text-center" data-sort-col="3" data-sort-dir="asc">Customer Name <i class="fas fa-sort"></i></th>
                                <th class="text-center">Phone Number</th>
                                <th class="sortable text-center" data-sort-col="5" data-sort-dir="desc">Rating <i class="fas fa-sort"></i></th>
                                <th class="text-center">Review</th>
                                <th class="text-center">Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($paginatedReviews)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <?php if ($ratingFilter == 'all'): ?>
                                            No reviews found matching your search criteria.
                                        <?php else: ?>
                                            No <?php echo htmlspecialchars($ratingFilter); ?>-star reviews found.
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($paginatedReviews as $review): ?>
                                    <?php renderTableRow($review); ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr id="noSearchResults" style="display: none;">
                                <td colspan="8" class="text-center">No reviews found matching your search criteria.</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <!-- Pagination -->
                <?php renderPagination($page, $totalPages); ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$slot = ob_get_clean();  // Store the output content in $slot
include '../layouts/app.php';  // Include the layout
?>

<script src="../assets/js/jquery.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>

<script>
    // Confirmation for toggling archive status with SweetAlert2
    function confirmToggleArchive(button) {
        const form = button.closest('form');
        const isArchiving = form.querySelector('input[name="is_archived"]').value === '1';
        const reviewId = form.querySelector('input[name="review_id"]').value;

        const actionText = isArchiving ? 'hide' : 'show';
        const actionTextCapitalized = isArchiving ? 'Hide' : 'Show';
        const icon = isArchiving ? 'warning' : 'info';
        const confirmButtonColor = isArchiving ? 'btn-warning' : 'btn-success';

        Swal.fire({
            title: `${actionTextCapitalized} Review Confirmation`,
            html: `Are you sure you want to ${actionText} this review from the homepage?<br><br>
                <div class="text-start">
                    <strong>Action:</strong> ${isArchiving ? 'Archive and hide from homepage' : 'Unarchive and show on homepage'}
                </div>`,
            icon: icon,
            showCancelButton: true,
            confirmButtonText: `Yes, ${actionTextCapitalized}`,
            cancelButtonText: 'Cancel',
            background: "#fff",
            customClass: {
                popup: "rounded-3",
                confirmButton: `btn ${confirmButtonColor} custom-swal-button`,
                cancelButton: "btn btn-secondary custom-swal-button",
            },
            buttonsStyling: false,
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: `${actionTextCapitalized}ing Review...`,
                    text: "Please wait",
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                // Submit the form
                form.submit();
            }
        });

        // Prevent the default form submission
        return false;
    }

    // Search functionality for reviews table
    function filterReviewsTable() {
        const input = document.getElementById("searchReviews");
        const filter = input.value.toUpperCase();
        const table = document.getElementById("reviewsTable");
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

        // Show or hide the "no results" message
        if (visibleRows === 0 && filter !== "") {
            noResultsRow.style.display = "table-row";
        } else {
            noResultsRow.style.display = "none";
        }
    }

    // Sorting functionality for reviews table
    document.querySelectorAll('#reviewsTable .sortable').forEach(header => {
        header.addEventListener('click', () => {
            const table = document.getElementById('reviewsTable');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr:not(#noSearchResults):not([style*="display: none"])'));
            const colIndex = parseInt(header.dataset.sortCol);
            let dir = header.dataset.sortDir === 'asc' ? 1 : -1;

            // Toggle sort direction
            header.dataset.sortDir = header.dataset.sortDir === 'asc' ? 'desc' : 'asc';

            // Update sort icons
            document.querySelectorAll('#reviewsTable .sortable i').forEach(icon => {
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

                // Special handling for Review Date column (index 1)
                if (colIndex === 1) {
                    const aDate = new Date(aText);
                    const bDate = new Date(bText);
                    if (!isNaN(aDate.getTime()) && !isNaN(bDate.getTime())) {
                        return dir * (aDate - bDate);
                    }
                }

                // Special handling for Queue # (index 2) - numeric sort
                if (colIndex === 2) {
                    const aNum = parseInt(aText.replace('#', ''), 10);
                    const bNum = parseInt(bText.replace('#', ''), 10);
                    if (!isNaN(aNum) && !isNaN(bNum)) {
                        return dir * (aNum - bNum);
                    }
                }

                // Special handling for Rating column (index 5) - numeric sort using data attribute
                if (colIndex === 5) {
                    const aRating = parseInt(a.cells[colIndex].getAttribute('data-rating') || '0');
                    const bRating = parseInt(b.cells[colIndex].getAttribute('data-rating') || '0');
                    return dir * (aRating - bRating);
                }

                // Special handling for Customer Name (index 3)
                if (colIndex === 3) {
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