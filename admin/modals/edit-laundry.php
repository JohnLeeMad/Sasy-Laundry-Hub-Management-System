<?php
require_once '../config/db_conn.php';

// Fetch prices from database
$priceQuery = $conn->query("SELECT item_name, price FROM laundry_prices");
$prices = [];
while ($row = $priceQuery->fetch_assoc()) {
    $prices[$row['item_name']] = $row['price'];
}
// Add this near the top where prices are fetched
$productsQuery = $conn->query("
    SELECT sp.id, sp.name, sp.measurement, sp.unit_price, sp.category_id, sc.name as category_name,
           COALESCE(inv.available_units, 0) as available_units
    FROM supply_products sp
    JOIN supply_categories sc ON sp.category_id = sc.id
    LEFT JOIN inventory inv ON sp.id = inv.product_id
    WHERE sp.is_active = 1
    ORDER BY sc.name, sp.name
");

$productsByCategory = [];
while ($row = $productsQuery->fetch_assoc()) {
    $productsByCategory[$row['category_id']][] = $row;
}
?>
<style>
    .insufficient-stock {
        background-color: #e9ecef;
        opacity: 0.7;
        cursor: not-allowed;
    }
    
    /* Enhanced Whites Order Badge Styling */
    .whites-order-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border: 2px solid #4a90e2;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
        color: #2c5aa0;
        box-shadow: 0 2px 8px rgba(74, 144, 226, 0.2);
        animation: subtle-pulse 2s ease-in-out infinite;
    }
    
    .whites-order-badge i {
        font-size: 1rem;
        color: #4a90e2;
    }
    
    @keyframes subtle-pulse {
        0%, 100% {
            box-shadow: 0 2px 8px rgba(74, 144, 226, 0.2);
        }
        50% {
            box-shadow: 0 2px 12px rgba(74, 144, 226, 0.35);
        }
    }
</style>
<link href="assets/css/laundry-modals.css" rel="stylesheet">

<div class="modal fade" id="editLaundryModal" tabindex="-1" aria-labelledby="editLaundryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 rounded-lg">
            <!-- Modal Header -->
            <div class="modal-header" style="background: var(--primary-color); color: white;">
                <h5 class="modal-title" id="editLaundryModalLabel">
                    Manage Laundry Order
                    <span id="edit_queue_number_badge" class="badge bg-light text-dark ms-2">#00</span>
                    <span id="edit_whites_badge" class="whites-order-badge ms-2" style="display: none;">
                        <i class="fas fa-tshirt"></i>
                        <span>Whites Order</span>
                    </span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1);"></button>
            </div>
            <form method="POST" action="req/update-laundry.php" enctype="multipart/form-data">
                <input type="hidden" name="laundry_id" id="edit_laundry_id">
                <input type="hidden" name="customer_type" id="edit_customer_type">
                <input type="hidden" name="customer_id" id="edit_customer_id">
                <input type="hidden" name="change_stored_as_balance" id="edit_change_stored_as_balance" value="0">
                <input type="hidden" id="edit_adjusted_total_price" name="adjusted_total_price">
                <input type="hidden" id="edit_separate_whites" name="separate_whites" value="0">
                <input type="hidden" id="edit_is_whites_order" name="is_whites_order" value="0">
                <div class="modal-body">

                    <div class="mb-4">
                        <h6 class="fw-bold" style="color: var(--primary-color);">Customer Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="edit_customer_name" class="form-label">Customer Name</label>
                                <input type="text" id="edit_customer_name" class="form-control" readonly
                                    style="background-color: #e9ecef; opacity: 1; cursor: not-allowed;">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_customer_phone" class="form-label">Phone Number</label>
                                <input type="text" id="edit_customer_phone" class="form-control" readonly
                                    style="background-color: #e9ecef; opacity: 1; cursor: not-allowed;">
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Laundry Details Section -->
                    <div class="mb-4">
                        <h6 class="fw-bold" style="color: var(--primary-color);">Laundry Details</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="rounds_of_wash" class="form-label">Rounds of Wash</label>
                                <input type="number" name="rounds_of_wash" id="edit_rounds_of_wash" class="form-control" min="1" max="4" value="1"
                                    title="Min: 1 round - Max: 4 rounds">
                            </div>
                            <div class="col-md-6">
                                <label for="dryer_preference" class="form-label">Dryer Preference</label>
                                <select name="dryer_preference" id="edit_dryer_preference" class="form-select" title="0: No drying - 1: 1 round - 2: 2 rounds">
                                    <option value="0">No Drying</option>
                                    <option value="1">1 round</option>
                                    <option value="2">2 rounds</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <!-- Detergent Selection -->
                            <div class="col-md-6">
                                <label for="edit_detergent_product" class="form-label">Detergent Product</label>
                                <select name="detergent_product_id" id="edit_detergent_product" class="form-select" required>
                                    <option value="">Select Detergent</option>
                                    <?php if (isset($productsByCategory[1])): ?>
                                        <?php foreach ($productsByCategory[1] as $product): ?>
                                            <option value="<?php echo $product['id']; ?>"
                                                data-price="<?php echo $product['unit_price']; ?>"
                                                data-stock="<?php echo $product['available_units']; ?>">
                                                <?php echo htmlspecialchars($product['name']); ?>
                                                <?php echo $product['measurement'] ? ' - ' . htmlspecialchars($product['measurement']) : ''; ?>
                                                (₱<?php echo number_format($product['unit_price'], 2); ?>/scoop)
                                                <?php if ($product['available_units'] <= 0): ?>
                                                    - Out of Stock
                                                <?php elseif ($product['available_units'] <= 10): ?>
                                                    - Low Unit (<?php echo $product['available_units']; ?>)
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_scoops_of_detergent" class="form-label">Scoops of Detergent</label>
                                <input type="number" name="scoops_of_detergent" id="edit_scoops_of_detergent" class="form-control"
                                    min="1" max="10" value="0" title="Min: 1 scoops - Max: 3 scoops" disabled>
                                <div id="edit_detergent_stock_info" class="small mt-1"></div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <!-- Fabric Conditioner Selection -->
                            <div class="col-md-6">
                                <label for="edit_fabcon_product" class="form-label">Fabric Conditioner (Optional)</label>
                                <select name="fabcon_product_id" id="edit_fabcon_product" class="form-select">
                                    <option value="">Select Fabric Conditioner</option>
                                    <?php if (isset($productsByCategory[2])): ?>
                                        <?php foreach ($productsByCategory[2] as $product): ?>
                                            <option value="<?php echo $product['id']; ?>"
                                                data-price="<?php echo $product['unit_price']; ?>"
                                                data-stock="<?php echo $product['available_units']; ?>">
                                                <?php echo htmlspecialchars($product['name']); ?>
                                                <?php echo $product['measurement'] ? ' - ' . htmlspecialchars($product['measurement']) : ''; ?>
                                                (₱<?php echo number_format($product['unit_price'], 2); ?>/cup)
                                                <?php if ($product['available_units'] <= 0): ?>
                                                    - Out of Stock
                                                <?php elseif ($product['available_units'] <= 10): ?>
                                                    - Low Unit (<?php echo $product['available_units']; ?>)
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_fabcon_cups" class="form-label">Fabric Conditioner (Cups)</label>
                                <input type="number" name="fabcon_cups" id="edit_fabcon_cups" class="form-control"
                                    min="0" max="10" value="0" title="Min: 0 cups - Max: 10 cups" disabled>
                                <div id="edit_fabcon_stock_info" class="small mt-1"></div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <!-- Bleach Selection -->
                            <div class="col-md-6">
                                <label for="edit_bleach_product" class="form-label">Bleach (Optional)</label>
                                <select name="bleach_product_id" id="edit_bleach_product" class="form-select">
                                    <option value="">Select Bleach</option>
                                    <?php if (isset($productsByCategory[3])): ?>
                                        <?php foreach ($productsByCategory[3] as $product): ?>
                                            <option value="<?php echo $product['id']; ?>"
                                                data-price="<?php echo $product['unit_price']; ?>"
                                                data-stock="<?php echo $product['available_units']; ?>">
                                                <?php echo htmlspecialchars($product['name']); ?>
                                                <?php echo $product['measurement'] ? ' - ' . htmlspecialchars($product['measurement']) : ''; ?>
                                                (₱<?php echo number_format($product['unit_price'], 2); ?>/cup)
                                                <?php if ($product['available_units'] <= 0): ?>
                                                    - Out of Stock
                                                <?php elseif ($product['available_units'] <= 10): ?>
                                                    - Low Unit (<?php echo $product['available_units']; ?>)
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_bleach_cups" class="form-label">Bleach (Cups)</label>
                                <input type="number" name="bleach_cups" id="edit_bleach_cups" class="form-control"
                                    min="0" max="5" value="0" title="Min: 0 cups - Max: 5 cups" disabled>
                                <div id="edit_bleach_stock_info" class="small mt-1"></div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input type="hidden" name="folding_service" value="0">
                                    <input class="form-check-input" type="checkbox" name="folding_service" id="edit_folding_service" value="1"
                                        title="Check to include folding service">
                                    <label class="form-check-label" for="folding_service">Folding Service</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <!-- Status Section -->
                    <div class="mb-4">
                        <h6 class="fw-bold" style="color: var(--primary-color);">Status Detail</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="edit_status" class="form-label">Status</label>
                                <select name="status" id="edit_status" class="form-select">
                                    <option value="Pending">Pending</option>
                                    <option value="Ongoing">Ongoing</option>
                                    <option value="Ready for Pickup">Ready for Pickup</option>
                                    <option value="Claimed">Claimed</option>
                                    <option value="Unclaimed">Unclaimed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <!-- Payment Section -->
                    <div class="mb-4">
                        <h6 class="fw-bold" style="color: var(--primary-color);">Payment Details</h6>
                        <div id="editPaymentDetails" class="mt-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="edit_payment_status" class="form-label">Payment Status</label>
                                    <input type="text" id="edit_payment_status" class="form-control" readonly
                                        style="background-color: #e9ecef; opacity: 1; cursor: not-allowed;" value="Unpaid">
                                </div>
                                <div class="col-md-6">
                                    <label for="edit_amount_tendered" class="form-label">Amount Tendered</label>
                                    <input type="number" name="amount_tendered" id="edit_amount_tendered" class="form-control" value="0.00" min="1" step="0.01" required>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label for="edit_total_price" class="form-label">Total Price</label>
                                    <input type="hidden" id="edit_deducted_balance" name="deducted_balance" value="0.00">
                                    <input type="text" id="edit_total_price" name="total_price" class="form-control" readonly
                                        style="background-color: #e9ecef; opacity: 1; cursor: not-allowed;" value="0.00">
                                </div>
                                <div class="col-md-6">
                                    <label for="edit_change" class="form-label">Change</label>
                                    <input type="text" id="edit_change" class="form-control" readonly
                                        style="background-color: #e9ecef; opacity: 1; cursor: not-allowed;" value="0.00">
                                </div>
                            </div>
                            <div class="row mt-3" id="store_change_container" style="display: none;">
                                <div class="col-md-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="store_change_as_balance"
                                            <?php if (isset($change_stored_as_balance) && $change_stored_as_balance == 1) echo 'checked'; ?>>
                                        <label class="form-check-label" for="store_change_as_balance">Store Change as Balance</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <!-- Remarks -->
                    <div class="mb-4">
                        <label for="edit_remarks" class="form-label">Remarks</label>
                        <textarea name="remarks" id="edit_remarks" class="form-control" rows="3"><?php echo htmlspecialchars($remarks ?? ''); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        <i class="fas fa-user-circle me-1"></i> Accommodated by:
                        <span id="edit_accommodated_by" class="fw-bold">N/A</span>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            style="background: #6c757d; color: white; border-color: #6c757d;">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">Update Order</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const PRODUCTS = <?php echo json_encode($productsByCategory); ?>;
</script>

<script src="assets/js/edit-laundry-modal.js"></script>