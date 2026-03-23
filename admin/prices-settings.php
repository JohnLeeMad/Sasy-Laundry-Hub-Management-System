<?php
session_start();
include 'req/admin-auth-check.php';
require_once __DIR__ . '/req/audit-logger.php'; // Add audit logger

require_once '../config/db_conn.php';

// Fetch laundry service prices
function fetchServicePrices($conn)
{
    $stmt = $conn->query("SELECT * FROM laundry_prices ORDER BY item_name");
    if ($stmt) {
        return $stmt->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

// Fetch supply products grouped by category
function fetchSupplyProductsByCategory($conn)
{
    $stmt = $conn->query("
        SELECT sp.id, sp.name, sp.price, sp.unit_price, sc.name as category_name, sp.max_unit_per_container,
               CASE 
                   WHEN LOWER(sc.name) LIKE '%detergent%' THEN 'scoop'
                   WHEN LOWER(sc.name) LIKE '%fabric%' THEN 'cup'
                   WHEN LOWER(sc.name) LIKE '%bleach%' OR LOWER(sc.name) LIKE '%zonrox%' THEN 'cup'
                   ELSE 'unit'
               END as unit_type
        FROM supply_products sp 
        LEFT JOIN supply_categories sc ON sp.category_id = sc.id 
        WHERE sp.is_active = 1 AND sp.name != 'Plastic Bag'
        ORDER BY sc.name, sp.name
    ");

    if ($stmt) {
        $products = $stmt->fetch_all(MYSQLI_ASSOC);
        $categorized = [];
        foreach ($products as $product) {
            $category = $product['category_name'] ?? 'Uncategorized';
            $categorized[$category][] = $product;
        }
        return $categorized;
    }
    return [];
}

// Fetch categories
function fetchCategories($conn)
{
    $stmt = $conn->query("SELECT * FROM supply_categories ORDER BY name");
    if ($stmt) {
        return $stmt->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

// Display success or error messages
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

// Get product name for audit logging
function getProductName($conn, $productId)
{
    $stmt = $conn->prepare("SELECT name FROM supply_products WHERE id = ?");
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result ? $result['name'] : 'Unknown Product';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->autocommit(false);

    try {
        $changes = [];
        $effectiveDate = $_POST['effective_date'] ?? date('Y-m-d');
        $isPriceChangeToday = ($effectiveDate === date('Y-m-d'));

        // Only update prices if effective date is today
        if ($isPriceChangeToday) {
            // Update laundry service prices
            if (isset($_POST['service_prices'])) {
                foreach ($_POST['service_prices'] as $itemName => $price) {
                    $price = floatval($price);

                    // Get current price for comparison
                    $stmt = $conn->prepare("SELECT price FROM laundry_prices WHERE item_name = ?");
                    $stmt->bind_param('s', $itemName);
                    $stmt->execute();
                    $currentPrice = $stmt->get_result()->fetch_assoc()['price'];

                    if ($currentPrice != $price) {
                        $stmt = $conn->prepare("UPDATE laundry_prices SET price = ? WHERE item_name = ?");
                        $stmt->bind_param('ds', $price, $itemName);
                        $stmt->execute();

                        $changes[] = [
                            'type' => 'service',
                            'name' => $itemName,
                            'old_price' => $currentPrice,
                            'new_price' => $price
                        ];
                    }
                }
            }

            // Update supply product unit prices
            if (isset($_POST['supply_prices'])) {
                foreach ($_POST['supply_prices'] as $productId => $unitPrice) {
                    if (!empty($unitPrice)) {
                        $unitPrice = floatval($unitPrice);

                        // Get current price for comparison
                        $stmt = $conn->prepare("SELECT unit_price, name FROM supply_products WHERE id = ?");
                        $stmt->bind_param('i', $productId);
                        $stmt->execute();
                        $result = $stmt->get_result()->fetch_assoc();
                        $currentPrice = $result['unit_price'];
                        $productName = $result['name'];

                        if ($currentPrice != $unitPrice) {
                            $stmt = $conn->prepare("UPDATE supply_products SET unit_price = ?, updated_at = NOW() WHERE id = ?");
                            $stmt->bind_param('di', $unitPrice, $productId);
                            $stmt->execute();

                            $changes[] = [
                                'type' => 'supply',
                                'name' => $productName,
                                'old_price' => $currentPrice,
                                'new_price' => $unitPrice
                            ];
                        }
                    }
                }
            }
        } else {
            // Store scheduled price changes for future effective date
            if (isset($_POST['service_prices'])) {
                foreach ($_POST['service_prices'] as $itemName => $price) {
                    $price = floatval($price);

                    // Get current price for comparison
                    $stmt = $conn->prepare("SELECT price FROM laundry_prices WHERE item_name = ?");
                    $stmt->bind_param('s', $itemName);
                    $stmt->execute();
                    $currentPrice = $stmt->get_result()->fetch_assoc()['price'];

                    if ($currentPrice != $price) {
                        // Store in scheduled_price_changes table
                        $stmt = $conn->prepare("INSERT INTO scheduled_price_changes (item_type, item_identifier, old_price, new_price, effective_date, created_by) VALUES ('service', ?, ?, ?, ?, ?)");
                        $stmt->bind_param('sddsi', $itemName, $currentPrice, $price, $effectiveDate, $_SESSION['user_id']);
                        $stmt->execute();

                        $changes[] = [
                            'type' => 'service',
                            'name' => $itemName,
                            'old_price' => $currentPrice,
                            'new_price' => $price
                        ];
                    }
                }
            }

            // Store scheduled supply product price changes
            if (isset($_POST['supply_prices'])) {
                foreach ($_POST['supply_prices'] as $productId => $unitPrice) {
                    if (!empty($unitPrice)) {
                        $unitPrice = floatval($unitPrice);

                        // Get current price for comparison
                        $stmt = $conn->prepare("SELECT unit_price, name FROM supply_products WHERE id = ?");
                        $stmt->bind_param('i', $productId);
                        $stmt->execute();
                        $result = $stmt->get_result()->fetch_assoc();
                        $currentPrice = $result['unit_price'];
                        $productName = $result['name'];

                        if ($currentPrice != $unitPrice) {
                            // Store in scheduled_price_changes table
                            $stmt = $conn->prepare("INSERT INTO scheduled_price_changes (item_type, item_identifier, old_price, new_price, effective_date, created_by) VALUES ('supply', ?, ?, ?, ?, ?)");
                            $stmt->bind_param('iddsi', $productId, $currentPrice, $unitPrice, $effectiveDate, $_SESSION['user_id']);
                            $stmt->execute();

                            $changes[] = [
                                'type' => 'supply',
                                'name' => $productName,
                                'old_price' => $currentPrice,
                                'new_price' => $unitPrice
                            ];
                        }
                    }
                }
            }
        }

        // Handle price change announcement if there are price changes
        if (!empty($changes) && isset($_POST['announcement_title']) && !empty(trim($_POST['announcement_title']))) {
            $announcementTitle = trim($_POST['announcement_title']);
            $announcementMessage = trim($_POST['announcement_message']);
            $announcementType = $_POST['announcement_type'];

            // Create announcement
            $stmt = $conn->prepare("INSERT INTO price_announcements (title, message, effective_date, announcement_type, created_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('ssssi', $announcementTitle, $announcementMessage, $effectiveDate, $announcementType, $_SESSION['user_id']);
            $stmt->execute();
        }

        // COMMIT TRANSACTION FIRST
        $conn->commit();

        // AUDIT LOGGING - Log price changes AFTER commit
        if (!empty($changes)) {
            $changeDescriptions = [];
            foreach ($changes as $change) {
                $type = $change['type'] === 'service' ? 'Service' : 'Supply';
                $changeDescriptions[] = $type . ': ' . str_replace('_', ' ', $change['name']) .
                    ' (₱' . number_format($change['old_price'], 2) . ' → ₱' .
                    number_format($change['new_price'], 2) . ')';
            }

            $statusText = $isPriceChangeToday ? 'Updated prices' : 'Scheduled price changes for ' . date('M j, Y', strtotime($effectiveDate));
            $description = $statusText . ': ' . implode(', ', $changeDescriptions);
            $auditResult = logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'update_prices', $description);

            error_log("Audit logging result: " . ($auditResult ? 'success' : 'failed'));
        }

        $successMessage = $isPriceChangeToday
            ? 'Prices updated successfully!'
            : 'Price changes scheduled for ' . date('M j, Y', strtotime($effectiveDate)) . '!';

        if (!empty($changes) && isset($_POST['announcement_title']) && !empty(trim($_POST['announcement_title']))) {
            $successMessage .= ' Customer announcement has been posted.';
        }

        $_SESSION['success'] = $successMessage;
        header('Location: prices-settings.php');
        exit;
    } catch (Exception $e) {
        $conn->rollback();

        if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
            $errorDescription = 'Failed to update prices: Your account may have been deactivated. Please contact administrator.';
            $_SESSION['error'] = 'Your account may have been deactivated. Please contact administrator.';
        } else {
            $errorDescription = 'Failed to update prices: ' . $e->getMessage();
            $_SESSION['error'] = 'Error updating prices: ' . $e->getMessage();
        }

        logActivity($_SESSION['user_id'] ?? 0, $_SESSION['user_role'] ?? 'unknown', $_SESSION['user_name'] ?? 'Unknown', 'price_error', $errorDescription);

        header('Location: prices-settings.php');
        exit;
    }

    // Re-enable autocommit
    $conn->autocommit(true);
}

// Generate laundry service price form rows (only for non-supply items)
function generateServicePriceFormRows($prices)
{
    // List of items that should be replaced by supply product dropdowns
    $supplyBasedItems = ['detergent_per_scoop', 'fabcon_per_cup', 'zonrox_per_cup'];

    foreach ($prices as $item) {
        // Skip items that will be handled by supply product dropdowns
        if (in_array($item['item_name'], $supplyBasedItems)) {
            continue;
        }

        echo '<div class="col-md-6">';
        echo '<div class="card price-card mb-4">';
        echo '<div class="card-body">';
        echo '<h5 class="card-title text-capitalize">' . str_replace('_', ' ', $item['item_name']) . '</h5>';
        echo '<p class="card-text text-muted">' . htmlspecialchars($item['description']) . '</p>';
        echo '<div class="input-group">';
        echo '<span class="input-group-text">₱</span>';
        echo '<input type="number" name="service_prices[' . htmlspecialchars($item['item_name']) . ']" class="form-control" value="' . number_format($item['price'], 2) . '" step="1" min="0" required>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
}

// Generate supply product category cards within the same card structure
function generateSupplyProductCards($categorizedProducts)
{
    foreach ($categorizedProducts as $category => $products) {
        echo '<div class="col-md-6">';
        echo '<div class="card price-card mb-4">';
        echo '<div class="card-body">';
        echo '<h5 class="card-title"><i class="fas fa-tags me-2"></i>' . htmlspecialchars($category) . '</h5>';

        echo '<div class="mb-3">';
        echo '<label class="form-label text-muted">Select Product:</label>';
        echo '<select class="form-select product-selector" data-category="' . htmlspecialchars(str_replace(' ', '_', $category)) . '">';
        echo '<option value="">Choose a product...</option>';

        foreach ($products as $product) {
            $unitText = $product['unit_type'] ?? 'unit';
            echo '<option value="' . $product['id'] . '" data-current-price="' . $product['unit_price'] . '" data-container-price="' . $product['price'] . '" data-unit-type="' . $unitText . '" data-max-units="' . $product['max_unit_per_container'] . '">';
            echo htmlspecialchars($product['name']) . ' (₱' . number_format($product['unit_price'], 2) . ' per ' . $unitText . ')';
            echo '</option>';
        }

        echo '</select>';
        echo '</div>';

        echo '<div class="price-input-section" style="display: none;">';
        echo '<div class="mb-2">';
        echo '<label class="form-label text-muted">Current Price:</label>';
        echo '<div class="input-group mb-2">';
        echo '<span class="input-group-text">₱</span>';
        echo '<input type="number" class="form-control current-price-display" readonly>';
        echo '<span class="input-group-text unit-type-display">per unit</span>';
        echo '</div>';
        echo '</div>';

        echo '<div class="mb-2">';
        echo '<label class="form-label text-muted">New Price:</label>';
        echo '<div class="input-group">';
        echo '<span class="input-group-text">₱</span>';
        echo '<input type="number" class="form-control new-price-input" step="1" min="0" placeholder="Enter new price">';
        echo '<span class="input-group-text unit-type-display">per unit</span>';
        echo '</div>';
        echo '<small class="form-text text-muted">Leave empty to keep current price</small>';
        echo '</div>';

        echo '<div class="product-info text-muted small mt-2">';
        echo '<i class="fas fa-info-circle me-1"></i>';
        echo '<span class="max-units-display">Container: ₱</span><span class="container-price-display">0.00</span> ';
        echo '(<span class="max-units-number">0</span> <span class="unit-type-text">units</span>)';
        echo '</div>';

        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
}

$servicePrices = fetchServicePrices($conn);
$categorizedProducts = fetchSupplyProductsByCategory($conn);

// Fetch scheduled price changes
$sql_scheduled = "SELECT spc.*, 
                  CASE 
                    WHEN spc.item_type = 'service' THEN spc.item_identifier
                    WHEN spc.item_type = 'supply' THEN sp.name
                  END as item_display_name,
                  u.name as created_by_name
                  FROM scheduled_price_changes spc
                  LEFT JOIN supply_products sp ON spc.item_type = 'supply' AND spc.item_identifier = sp.id
                  LEFT JOIN users u ON spc.created_by = u.id
                  WHERE spc.is_applied = 0 AND spc.effective_date >= CURDATE()
                  ORDER BY spc.effective_date ASC, spc.created_at DESC";
$scheduled_changes = $conn->query($sql_scheduled);

$header = 'Manage Prices';
ob_start();
?>
<link href="assets/css/laundry-modals.css" rel="stylesheet">

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

    <div class="row mt-4">
        <div class="col-md-10 offset-md-1">
            <form method="POST" id="priceForm">

                <!-- Price Change Announcement Section -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #17a2b8; color: white;">
                        <h5 class="mb-0" style="font-size: 1.3rem;">
                            <i class="fas fa-bullhorn me-2"></i>Price Change Announcement (Optional)
                        </h5>
                        <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#manageAnnouncementsModal"
                            style="background-color: white; color: #17a2b8; border: 1px solid white;">
                            <i class="fas fa-list me-2"></i>View Past Announcements
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="announcement_title" class="form-label">Announcement Title</label>
                                <input type="text" class="form-control" id="announcement_title" name="announcement_title"
                                    placeholder="e.g., Price Adjustment Notice" maxlength="255">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="effective_date" class="form-label">Effective Date</label>
                                <input type="date" class="form-control" id="effective_date" name="effective_date"
                                    value="<?php echo date('Y-m-d'); ?>" min="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="announcement_type" class="form-label">Type</label>
                                <select class="form-select" id="announcement_type" name="announcement_type">
                                    <option value="price_increase">Price Increase</option>
                                    <option value="price_decrease">Price Decrease</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="announcement_message" class="form-label">Message to Customers</label>
                            <textarea class="form-control" id="announcement_message" name="announcement_message" rows="3"
                                placeholder="Explain the reason for price changes and any additional information customers should know..."></textarea>
                            <div class="form-text"><strong>Note: </strong>This announcement will only be created if you make actual price changes below. Fill this out to notify customers about the price changes.</div>
                        </div>
                    </div>
                </div>

                <!-- Combined Laundry Service and Supply Product Prices Section -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header" style="background-color: var(--primary-color); color: white;">
                        <h5 class="mb-0" style="font-size: 1.5rem;">
                            Laundry Service Prices
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Laundry Service Prices (non-supply based) -->
                            <?php generateServicePriceFormRows($servicePrices); ?>

                            <!-- Supply Product Category Dropdowns (replacing detergent, fabcon, zonrox inputs) -->
                            <?php if (!empty($categorizedProducts)): ?>
                                <?php generateSupplyProductCards($categorizedProducts); ?>
                            <?php endif; ?>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="reset" class="btn btn-secondary" style="background: #6c757d; color: white; border-color: #6c757d;" onclick="resetForm()">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary px-4">
                                Submit
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$slot = ob_get_clean();
include '../layouts/app.php';
?>
<?php include 'modals/manage-announcements.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/price-settings.js"></script>