<?php
$header = 'Home';
ob_start();
require_once 'req/customer-profile.php';
?>

<link href="assets/css/index.css" rel="stylesheet">
<link href="assets/css/index-mobile.css" rel="stylesheet">

<div class="container-fluid px-3 py-3">
    <?php if (isset($_SESSION['success'])): ?>
        <?php renderAlert('success', $_SESSION['success']); ?>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <?php renderAlert('error', $_SESSION['error']); ?>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php
    $sql_stats = "SELECT 
        SUM(CASE WHEN status = 'Claimed' THEN 1 ELSE 0 END) as claimed_orders,
        SUM(CASE WHEN status IN ('Pending', 'Ongoing', 'Ready for Pickup') AND payment_status = 'Unpaid' THEN adjusted_total_price ELSE 0 END) as amount_to_pay,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN status = 'Ongoing' THEN 1 ELSE 0 END) as ongoing_orders,
        SUM(CASE WHEN status = 'Ready for Pickup' THEN 1 ELSE 0 END) as ready_orders,
        SUM(CASE WHEN status = 'Claimed' THEN 1 ELSE 0 END) as claimed_orders_count,
        SUM(CASE WHEN status = 'Unclaimed' THEN 1 ELSE 0 END) as unclaimed_orders
        FROM laundry_lists WHERE customer_id = ?";
    $stmt_stats = $conn->prepare($sql_stats);
    $stmt_stats->bind_param("i", $customer_id);
    $stmt_stats->execute();
    $stats = $stmt_stats->get_result()->fetch_assoc();

    $sql_recent = "SELECT ll.id, ll.queue_number, ll.status, ll.adjusted_total_price, ll.created_at, ll.updated_at,
                   GROUP_CONCAT(DISTINCT CONCAT(ld.rounds_of_wash, ' wash rounds') SEPARATOR ', ') as wash_details,
                   GROUP_CONCAT(DISTINCT CONCAT(ld.scoops_of_detergent, ' scoops detergent') SEPARATOR ', ') as detergent_details,
                   GROUP_CONCAT(DISTINCT CASE WHEN ld.folding_service = 1 THEN 'Folding' ELSE NULL END SEPARATOR ', ') as folding_service,
                   GROUP_CONCAT(DISTINCT CASE WHEN ld.dryer_preference = 1 THEN 'Dryer' ELSE 'Air Dry' END SEPARATOR ', ') as dryer_preference,
                   SUM(ld.bleach_cups) as total_bleach_cups,
                   SUM(ld.fabcon_cups) as total_fabcon_cups
                   FROM laundry_lists ll 
                   LEFT JOIN laundry_details ld ON ll.id = ld.laundry_list_id 
                   WHERE ll.customer_id = ? 
                   GROUP BY ll.id
                   ORDER BY ll.created_at DESC LIMIT 5";
    $stmt_recent = $conn->prepare($sql_recent);
    $stmt_recent->bind_param("i", $customer_id);
    $stmt_recent->execute();
    $recent_orders = $stmt_recent->get_result();

    $sql_claimed_no_review = "SELECT ll.id, ll.queue_number, ll.created_at 
                            FROM laundry_lists ll 
                            WHERE ll.customer_id = ? 
                            AND ll.status = 'Claimed' 
                            AND ll.review_dismissed = 0
                            AND NOT EXISTS (
                                SELECT 1 FROM reviews r 
                                WHERE r.laundry_list_id = ll.id
                                AND r.customer_id = ?
                            )
                            ORDER BY ll.created_at DESC 
                            LIMIT 3";
    $stmt_claimed = $conn->prepare($sql_claimed_no_review);
    $stmt_claimed->bind_param("ii", $customer_id, $customer_id);
    $stmt_claimed->execute();
    $claimed_orders_no_review = $stmt_claimed->get_result();

    $review_orders = [];
    while ($row = $claimed_orders_no_review->fetch_assoc()) {
        $review_orders[] = $row;
    }

    $sql_receipts = "SELECT r.*, ll.queue_number 
                     FROM receipts r 
                     JOIN laundry_lists ll ON r.laundry_list_id = ll.id 
                     WHERE r.customer_id = ? 
                     ORDER BY r.created_at DESC LIMIT 3";
    $stmt_receipts = $conn->prepare($sql_receipts);
    $stmt_receipts->bind_param("i", $customer_id);
    $stmt_receipts->execute();
    $recent_receipts = $stmt_receipts->get_result();

    $sql_announcements = "SELECT pa.*, u.name as created_by_name 
                          FROM price_announcements pa 
                          LEFT JOIN users u ON pa.created_by = u.id 
                          WHERE pa.is_active = 1 AND pa.effective_date >= CURDATE() - INTERVAL 30 DAY
                          ORDER BY pa.created_at DESC LIMIT 3";
    $stmt_announcements = $conn->prepare($sql_announcements);
    $stmt_announcements->execute();
    $price_announcements = $stmt_announcements->get_result();
    $announcements_array = [];
    while ($announcement = $price_announcements->fetch_assoc()) {
        $announcements_array[] = $announcement;
    }
    ?>

    <?php if ($stats['ready_orders'] > 0): ?>
        <div class="alert alert-pickup alert-dismissible fade show" role="alert" data-auto-dismiss="8000">
            <i class="fas fa-bell me-2"></i>
            <strong>Attention! </strong> You have <?php echo $stats['ready_orders']; ?> order(s) ready for pickup!
            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($review_orders)): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-star me-3 fs-4" style="color: #ffc107;"></i>
                <div class="flex-grow-1">
                    <h6 class="alert-heading mb-2">Thanks for trusting us!</h6>
                    <p class="mb-2">Would you like to share your experience with us?</p>

                    <?php foreach ($review_orders as $order): ?>
                        <div class="review-form mb-3 p-3 rounded" style="background: rgba(255,255,255,0.3);">
                            <form method="POST" action="req/review-handler.php" id="reviewForm_<?php echo $order['id']; ?>">
                                <input type="hidden" name="laundry_list_id" value="<?php echo $order['id']; ?>">

                                <div class="mb-2">
                                    <label class="form-label d-block fw-bold">
                                        Rate your experience for your laundry (Queue #<?php echo $order['queue_number']; ?> – <?php echo date('M j, Y', strtotime($order['created_at'])); ?>):
                                    </label>
                                    <div class="star-rating">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <input type="radio"
                                                id="star<?php echo $i . '_' . $order['id']; ?>"
                                                name="rating"
                                                value="<?php echo $i; ?>"
                                                class="rating-input"
                                                data-form-id="reviewForm_<?php echo $order['id']; ?>">
                                            <label for="star<?php echo $i . '_' . $order['id']; ?>" class="star-label" title="<?php echo $i; ?> star<?php echo $i > 1 ? 's' : ''; ?>">
                                                <i class="fas fa-star"></i>
                                            </label>
                                        <?php endfor; ?>
                                    </div>
                                    <small class="text-danger rating-error" style="display: none;">Please select a rating</small>
                                </div>

                                <div class="mb-3">
                                    <label for="review_text_<?php echo $order['id']; ?>" class="form-label fw-bold">Your Review (optional):</label>
                                    <textarea class="form-control form-control-sm" id="review_text_<?php echo $order['id']; ?>"
                                        name="review_text" rows="2" placeholder="Share your experience..."></textarea>
                                </div>

                                <div class="d-flex gap-2 justify-content-between">
                                    <button type="submit" name="submit_review" class="btn btn-accent btn-sm submit-review-btn">
                                        <i class="fas fa-paper-plane me-1"></i>Submit Review
                                    </button>

                                    <form method="POST" action="req/review-handler.php" class="m-0">
                                        <input type="hidden" name="laundry_list_id" value="<?php echo $order['id']; ?>">
                                        <button type="submit" name="dont_show_review" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-times me-1"></i>Don't Show Again
                                        </button>
                                    </form>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn-close ms-2" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1" style="color: var(--primary-color);">Welcome back, <?php echo htmlspecialchars($_SESSION['customer_name'] ?? 'Customer'); ?>!</h2>
                    <p class="text-muted mb-0"><?php echo date('l, F j, Y'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-4 mb-2">
            <div class="card stats-card text-center">
                <div class="card-body py-2">
                    <h3>₱<?php echo number_format($stats['amount_to_pay'] ?? 0, 2); ?></h3>
                    <p class="mb-0">Amount to Pay</p>
                </div>
            </div>
        </div>
        <div class="col-4 mb-2">
            <div class="card stats-card text-center">
                <div class="card-body py-2">
                    <h3>₱<?php echo number_format($_SESSION['customer_balance'] ?? 0.00, 2); ?></h3>
                    <p class="mb-0">Wallet Balance</p>
                </div>
            </div>
        </div>
        <div class="col-4 mb-2">
            <div class="card stats-card text-center">
                <div class="card-body py-2">
                    <h3><?php echo $stats['claimed_orders'] ?? 0; ?></h3>
                    <p class="mb-0">Total Claimed Orders</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-5 mb-3 order-status-col">
            <div class="card card-equal-height" style="max-height: 200px;">
                <div class="card-body py-2">
                    <h5 class="card-title" style="color: var(--primary-color);">
                        <i class="fas fa-clipboard-list me-2"></i>Order Status Overview
                    </h5>
                    <div class="row">
                        <div class="col-6 mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="status-badge status-pending">Pending</span>
                                <strong><?php echo $stats['pending_orders'] ?? 0; ?></strong>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="status-badge status-ongoing">Ongoing</span>
                                <strong><?php echo $stats['ongoing_orders'] ?? 0; ?></strong>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="status-badge status-ready-for-pickup">Ready for Pickup</span>
                                <strong><?php echo $stats['ready_orders'] ?? 0; ?></strong>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="status-badge status-claimed">Claimed</span>
                                <strong><?php echo $stats['claimed_orders_count'] ?? 0; ?></strong>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="status-badge status-unclaimed">Unclaimed</span>
                                <strong><?php echo $stats['unclaimed_orders'] ?? 0; ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (count($announcements_array) > 0): ?>
                <div class="card mt-3 card-equal-height">
                    <div class="card-body py-3">
                        <h5 class="card-title" style="color: var(--primary-color);">
                            <i class="fas fa-bullhorn me-2"></i>Price Change Announcements
                        </h5>
                        <div class="announcement-container">
                            <?php foreach ($announcements_array as $index => $announcement): ?>
                                <div class="announcement-item <?php echo $index === 0 ? 'active' : ''; ?>" data-announcement-index="<?php echo $index; ?>">
                                    <div class="mb-3 p-3 rounded" style="background-color: #f8f9fa; border-left: 4px solid <?php
                                                                                                                            echo $announcement['announcement_type'] === 'price_increase' ? '#dc3545' : '#28a745'; ?>;">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-2 fw-bold">
                                                    <?php
                                                    $icon = $announcement['announcement_type'] === 'price_increase'
                                                        ? '<i class="fas fa-arrow-up text-danger me-1"></i>'
                                                        : '<i class="fas fa-arrow-down text-success me-1"></i>';
                                                    echo $icon . htmlspecialchars($announcement['title']);
                                                    ?>
                                                </h6>
                                                <p class="mb-2 text-dark"><?php echo nl2br(htmlspecialchars($announcement['message'])); ?></p>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar-alt me-1"></i>
                                                    Effective: <?php echo date('M j, Y', strtotime($announcement['effective_date'])); ?>
                                                    <?php if ($announcement['effective_date'] > date('Y-m-d')): ?>
                                                        <span class="badge bg-warning text-dark ms-2">Upcoming</span>
                                                    <?php elseif ($announcement['effective_date'] == date('Y-m-d')): ?>
                                                        <span class="badge bg-success ms-2">Effective Today</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary ms-2">Active</span>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-<?php
                                                                        echo $announcement['announcement_type'] === 'price_increase' ? 'danger' : 'success'; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $announcement['announcement_type'])); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if (count($announcements_array) > 1): ?>
                            <div class="announcement-pagination">
                                <button type="button" id="prevAnnouncement" onclick="changeAnnouncement(-1)">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <span class="page-indicator">
                                    <span id="currentAnnouncementPage">1</span> / <?php echo count($announcements_array); ?>
                                </span>
                                <button type="button" id="nextAnnouncement" onclick="changeAnnouncement(1)">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-md-7 mb-3 recent-orders-col">
            <div class="card card-equal-height">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title" style="color: var(--primary-color);">
                        <i class="fas fa-clock me-2"></i>Recent Orders
                    </h5>

                    <div class="recent-orders-container flex-grow-1">
                        <?php if ($recent_orders->num_rows > 0): ?>
                            <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                <div class="recent-order-item mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Queue #<?php echo $order['queue_number']; ?></h6>
                                            <small class="text-muted mb-1 d-block">Created: <?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></small>
                                            <?php if (!empty($order['updated_at']) && $order['updated_at'] != $order['created_at']): ?>
                                                <small class="text-muted mb-1 d-block">Last Modified: <?php echo date('M j, Y g:i A', strtotime($order['updated_at'])); ?></small>
                                            <?php endif; ?>
                                            <?php if (!empty($order['wash_details']) || !empty($order['folding_service'])): ?>
                                                <div class="mt-2">
                                                    <?php if (!empty($order['wash_details'])): ?>
                                                        <small class="badge bg-light text-dark me-1 mb-1"><?php echo $order['wash_details']; ?></small>
                                                    <?php endif; ?>
                                                    <?php if (!empty($order['folding_service'])): ?>
                                                        <small class="badge bg-light text-dark me-1 mb-1"><?php echo $order['folding_service']; ?></small>
                                                    <?php endif; ?>
                                                    <?php if ($order['total_bleach_cups'] > 0): ?>
                                                        <small class="badge bg-light text-dark me-1 mb-1"><?php echo $order['total_bleach_cups']; ?> bleach cup/s</small>
                                                    <?php endif; ?>
                                                    <?php if ($order['total_fabcon_cups'] > 0): ?>
                                                        <small class="badge bg-light text-dark me-1 mb-1"><?php echo $order['total_fabcon_cups']; ?> fabric softener cup/s</small>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-end ms-2">
                                            <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>">
                                                <?php echo $order['status']; ?>
                                            </span>
                                            <div class="mt-1">
                                                <strong>₱<?php echo number_format($order['adjusted_total_price'], 2); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <p class="text-muted">No orders yet. Place your first order!</p>
                                <a href="prelist-orders.php" class="btn btn-accent">Prelist an Order Now</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($recent_orders->num_rows > 0): ?>
                        <div class="text-center mt-3 pt-2 border-top">
                            <a href="orders.php" class="btn btn-primary btn-sm">View All Orders</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentAnnouncementIndex = 0;
    const totalAnnouncements = <?php echo count($announcements_array); ?>;

    function changeAnnouncement(direction) {
        const items = document.querySelectorAll('.announcement-item');

        items[currentAnnouncementIndex].classList.remove('active');
        currentAnnouncementIndex += direction;

        if (currentAnnouncementIndex < 0) {
            currentAnnouncementIndex = totalAnnouncements - 1;
        } else if (currentAnnouncementIndex >= totalAnnouncements) {
            currentAnnouncementIndex = 0;
        }

        items[currentAnnouncementIndex].classList.add('active');
        document.getElementById('currentAnnouncementPage').textContent = currentAnnouncementIndex + 1;
        updatePaginationButtons();
    }

    function updatePaginationButtons() {
        const prevBtn = document.getElementById('prevAnnouncement');
        const nextBtn = document.getElementById('nextAnnouncement');

        if (prevBtn && nextBtn) {
            prevBtn.disabled = false;
            nextBtn.disabled = false;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        updatePaginationButtons();
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.submit-review-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                const form = this.closest('form');
                const ratingInputs = form.querySelectorAll('.rating-input');
                const errorMsg = form.querySelector('.rating-error');
                let isRatingSelected = false;

                ratingInputs.forEach(function(input) {
                    if (input.checked) {
                        isRatingSelected = true;
                    }
                });

                if (!isRatingSelected) {
                    e.preventDefault();
                    errorMsg.style.display = 'block';
                    return false;
                } else {
                    errorMsg.style.display = 'none';
                }
            });
        });

        document.querySelectorAll('.rating-input').forEach(function(input) {
            input.addEventListener('change', function() {
                const form = this.closest('form');
                const errorMsg = form.querySelector('.rating-error');
                if (errorMsg) {
                    errorMsg.style.display = 'none';
                }
            });
        });
    });
</script>

<?php
$slot = ob_get_clean();
include '../layouts/app-customer.php';

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
?>