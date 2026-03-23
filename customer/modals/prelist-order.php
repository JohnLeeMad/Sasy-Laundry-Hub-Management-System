<?php
require_once '../config/db_conn.php';

// Fetch prices from database
$priceQuery = $conn->query("SELECT item_name, price FROM laundry_prices");
$prices = [];
while ($row = $priceQuery->fetch_assoc()) {
    $prices[$row['item_name']] = $row['price'];
}

// Fetch products by category for dropdowns
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

<link href="assets/css/laundry-modals.css" rel="stylesheet">

<style>
    /* Order List Styles */
    .order-list-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        padding: 10px;
        background-color: #f8f9fa;
        border-radius: 8px;
    }

    .order-list-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        background-color: white;
        border: 2px solid #dee2e6;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .order-list-item:hover {
        border-color: var(--primary-color);
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .order-list-item.active {
        border-color: var(--primary-color);
        background-color: var(--primary-color);
        color: white;
    }

    .order-list-item.whites-order {
        border-color: #e3f2fd;
        background-color: #e3f2fd;
    }

    .order-list-item.whites-order.active {
        border-color: #2196f3;
        background-color: #2196f3;
        color: white;
    }

    .order-list-item-content {
        display: flex;
        flex-direction: column;
        gap: 2px;
        flex: 1;
    }

    .order-list-number {
        font-weight: 600;
        font-size: 14px;
    }

    .whites-badge {
        display: inline-block;
        font-size: 11px;
        font-weight: 500;
        padding: 2px 6px;
        margin-left: 6px;
        background-color: rgba(255, 255, 255, 0.3);
        border-radius: 4px;
    }

    .order-list-item.active .whites-badge {
        background-color: rgba(255, 255, 255, 0.3);
    }

    .order-list-total {
        font-size: 12px;
        opacity: 0.8;
    }

    .order-list-delete {
        padding: 4px 8px;
        font-size: 12px;
    }

    .order-list-item.active .order-list-delete {
        color: white;
        border-color: white;
    }

    .order-list-item.active .order-list-delete:hover {
        background-color: rgba(255, 255, 255, 0.2);
    }

    /* Notification Animations */
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
</style>

<div class="modal fade" id="prelistOrderModal" tabindex="-1" aria-labelledby="prelistOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 rounded-lg">
            <!-- Modal Header -->
            <div class="modal-header" style="background: var(--primary-color); color: white;">
                <h5 class="modal-title" id="prelistOrderModalLabel">Pre-list Laundry Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1);"></button>
            </div>
            <form method="POST" action="req/store-prelist.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <!-- Multiple Orders Section -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0" style="color: var(--primary-color);">Laundry Details</h6>
                            <button type="button" id="addOrderButton" class="btn btn-sm" style="background-color: var(--primary-color); color: white;">
                                <i class="fas fa-plus me-1"></i> Add Order
                            </button>
                        </div>

                        <!-- Order List Navigation -->
                        <div id="orderListContainer" class="order-list-container mb-3"></div>

                        <!-- Orders Container -->
                        <div id="ordersContainer">
                            <!-- Order will be displayed here by JavaScript -->
                        </div>
                    </div>
                    <hr>

                    <!-- Payment Section -->
                    <div class="mb-3">
                        <h6 class="fw-bold" style="color: var(--primary-color);">Order Summary</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="grand_total" class="form-label">Grand Total</label>
                                <input type="text" id="grand_total" class="form-control fw-bold" readonly
                                    style="background-color: #e9ecef; opacity: 1; cursor: not-allowed; font-size: 1.1rem;" value="₱0.00">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mt-4">
                                    <input
                                        type="checkbox"
                                        class="form-check-input"
                                        id="use_balance"
                                        name="use_balance"
                                        <?php echo (empty($_SESSION['customer_balance']) || $_SESSION['customer_balance'] <= 0) ? 'disabled' : ''; ?>
                                        <?php echo (empty($_SESSION['customer_balance']) || $_SESSION['customer_balance'] <= 0) ? 'style="opacity: 0.5; filter: grayscale(100%);"' : ''; ?>>
                                    <label class="form-check-label" for="use_balance">
                                        Use Available Balance (₱<span id="customer_balance"><?php echo htmlspecialchars($_SESSION['customer_balance'] ?? '0.00'); ?></span>)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Remarks -->
                    <div class="mb-3">
                        <label for="global_remarks" class="form-label">Global Remarks</label>
                        <textarea name="global_remarks" id="global_remarks" class="form-control" rows="2" placeholder="Optional remarks for all orders"></textarea>
                    </div>

                    <!-- Hidden field to store order data -->
                    <input type="hidden" name="orders_data" id="orders_data">
                    <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($_SESSION['user_id'] ?? ''); ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" style="background: #6c757d; color: white; border-color: #6c757d;" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" id="prelistAllOrdersButton" class="btn btn-primary">
                        Pre-list Orders
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Order Template (Hidden) -->
<template id="orderTemplate">
    <div class="order-item mb-4 p-3 border rounded">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0" style="color: var(--secondary-color);">Order #<span class="order-number">1</span></h6>
            <div>
                <button type="button" class="btn btn-sm btn-outline-danger remove-order-btn" title="Remove this order">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <label class="form-label">Rounds of Wash</label>
                <input type="number" class="form-control rounds-of-wash" min="1" max="4" value="1">
            </div>
            <div class="col-md-6">
                <label class="form-label">Dryer Preference</label>
                <select class="form-select dryer-preference">
                    <option value="0">No Drying</option>
                    <option value="1">1 round</option>
                    <option value="2">2 rounds</option>
                </select>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-6">
                <label class="form-label">Detergent Product</label>
                <select class="form-select detergent-product" required>
                    <option value="">Select Detergent</option>
                    <?php if (isset($productsByCategory[1])): ?>
                        <?php foreach ($productsByCategory[1] as $product): ?>
                            <option value="<?php echo $product['id']; ?>"
                                data-price="<?php echo $product['unit_price']; ?>"
                                data-stock="<?php echo $product['available_units']; ?>"
                                data-measurement="<?php echo htmlspecialchars($product['measurement'] ?? ''); ?>"
                                <?php echo $product['available_units'] <= 0 ? 'disabled' : ''; ?>>
                                <?php echo htmlspecialchars($product['name']); ?>
                                <?php echo $product['measurement'] ? ' - ' . htmlspecialchars($product['measurement'] ?? '') : ''; ?>
                                (₱<?php echo number_format($product['unit_price'], 2); ?> per <?php echo htmlspecialchars($product['measurement'] ?? ''); ?>)
                                <?php echo $product['available_units'] <= 0 ? ' - OUT OF STOCK' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <div class="detergent-stock-info small mt-1"></div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Scoops of Detergent</label>
                <input type="number" class="form-control scoops-of-detergent" min="1" max="10" value="0" disabled>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-6">
                <label class="form-label">Fabric Conditioner Product</label>
                <select class="form-select fabcon-product">
                    <option value="">Select Fabric Conditioner (Optional)</option>
                    <?php if (isset($productsByCategory[2])): ?>
                        <?php foreach ($productsByCategory[2] as $product): ?>
                            <option value="<?php echo $product['id']; ?>"
                                data-price="<?php echo $product['unit_price']; ?>"
                                data-stock="<?php echo $product['available_units']; ?>"
                                data-measurement="<?php echo htmlspecialchars($product['measurement'] ?? ''); ?>"
                                <?php echo $product['available_units'] <= 0 ? 'disabled' : ''; ?>>
                                <?php echo htmlspecialchars($product['name']); ?>
                                <?php echo $product['measurement'] ? ' - ' . htmlspecialchars($product['measurement'] ?? '') : ''; ?>
                                (₱<?php echo number_format($product['unit_price'], 2); ?> per <?php echo htmlspecialchars($product['measurement'] ?? ''); ?>)
                                <?php echo $product['available_units'] <= 0 ? ' - OUT OF STOCK' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <div class="fabcon-stock-info small mt-1"></div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Cups of Fabric Conditioner</label>
                <input type="number" class="form-control fabcon-cups" min="0" max="10" value="0" disabled>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-6">
                <label class="form-label">Bleach Product</label>
                <select class="form-select bleach-product">
                    <option value="">Select Bleach (Optional)</option>
                    <?php if (isset($productsByCategory[3])): ?>
                        <?php foreach ($productsByCategory[3] as $product): ?>
                            <option value="<?php echo $product['id']; ?>"
                                data-price="<?php echo $product['unit_price']; ?>"
                                data-stock="<?php echo $product['available_units']; ?>"
                                data-measurement="<?php echo htmlspecialchars($product['measurement'] ?? ''); ?>"
                                <?php echo $product['available_units'] <= 0 ? 'disabled' : ''; ?>>
                                <?php echo htmlspecialchars($product['name']); ?>
                                <?php echo $product['measurement'] ? ' - ' . htmlspecialchars($product['measurement'] ?? '') : ''; ?>
                                (₱<?php echo number_format($product['unit_price'], 2); ?> per <?php echo htmlspecialchars($product['measurement'] ?? ''); ?>)
                                <?php echo $product['available_units'] <= 0 ? ' - OUT OF STOCK' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <div class="bleach-stock-info small mt-1"></div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Bleach (Cups)</label>
                <input type="number" class="form-control bleach-cups" min="0" max="5" value="0" disabled>
                <div class="bleach-stock-info small mt-1"></div>
            </div>
        </div>

        <!-- Clothing and Household Items -->
        <div class="row mt-3">
            <div class="col-md-12">
                <button type="button" class="btn btn-sm btn-outline-secondary toggle-clothing-items mb-2">
                    Show Clothing & Household Items
                </button>
                <div class="clothing-items-section d-none">
                    <label class="form-label">Clothing & Household Items (Optional)</label>
                    <div class="row g-3">
                        <!-- First Row -->
                        <div class="col-md-3 col-6">
                            <div class="d-flex flex-column h-100">
                                <label class="form-label small mb-1">Tops <i class="fas fa-tshirt text-muted"></i></label>
                                <small class="text-muted mb-2">(Shirts, Polos, Sandos, Sweatshirts)</small>
                                <input type="number" class="form-control form-control-sm clothing-tops" min="0" max="40" value="0">
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="d-flex flex-column h-100">
                                <label class="form-label small mb-1">Bottoms <i class="fas fa-socks text-muted"></i></label>
                                <small class="text-muted mb-2">(Pants, Shorts, Socks, Skirts)</small>
                                <input type="number" class="form-control form-control-sm clothing-bottoms" min="0" max="40" value="0">
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="d-flex flex-column h-100">
                                <label class="form-label small mb-1">Undergarments <i class="fas fa-heart text-muted"></i></label>
                                <small class="text-muted mb-2">(Briefs, Panties, Bras, Boxers)</small>
                                <input type="number" class="form-control form-control-sm clothing-undergarments" min="0" max="50" value="0">
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="d-flex flex-column h-100">
                                <label class="form-label small mb-1">Delicates <i class="fas fa-spray-can text-muted"></i></label>
                                <small class="text-muted mb-2">(Silk, Lingerie)</small>
                                <input type="number" class="form-control form-control-sm clothing-delicates" min="0" max="40" value="0">
                            </div>
                        </div>
                        <!-- Second Row -->
                        <div class="col-md-3 col-6">
                            <div class="d-flex flex-column h-100">
                                <label class="form-label small mb-1">Linens <i class="fas fa-bed text-muted"></i></label>
                                <small class="text-muted mb-2">(Bed Sheets, Pillow Cases, Towels)</small>
                                <input type="number" class="form-control form-control-sm clothing-linens" min="0" max="15" value="0">
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="d-flex flex-column h-100">
                                <label class="form-label small mb-1">Curtains <i class="fas fa-window-maximize text-muted"></i></label>
                                <small class="text-muted mb-2">(Curtains & Drapes)</small>
                                <input type="number" class="form-control form-control-sm clothing-curtains-drapes" min="0" max="15" value="0">
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="d-flex flex-column h-100">
                                <label class="form-label small mb-1">Blankets <i class="fas fa-layer-group text-muted"></i></label>
                                <small class="text-muted mb-2">(Blankets & Comforters)</small>
                                <input type="number" class="form-control form-control-sm clothing-blankets-comforters" min="0" max="8" value="0">
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="d-flex flex-column h-100">
                                <label class="form-label small mb-1">Others <i class="fas fa-box text-muted"></i></label>
                                <small class="text-muted mb-2">(Specify in remarks)</small>
                                <input type="number" class="form-control form-control-sm clothing-others" min="0" max="20" value="0">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-6">
                <div class="form-check mt-2">
                    <input class="form-check-input folding-service" type="checkbox" value="1">
                    <label class="form-check-label">Folding Service</label>
                </div>
                <div class="form-check mt-2">
                    <input class="form-check-input separate-whites" type="checkbox" value="1" name="separate_whites">
                    <label class="form-check-label">Separate Whites from Colored</label>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Order Remarks</label>
                <textarea class="form-control order-remarks" rows="2" placeholder="Optional remarks for this order"></textarea>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-12">
                <div class="d-flex justify-content-end">
                    <strong>Order Total: ₱<span class="order-total">0.00</span></strong>
                </div>
            </div>
        </div>
    </div>
</template>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Pass PHP prices to JavaScript
    const PRICES = {
        washFeePerRound: <?php echo $prices['wash_per_round'] ?? 70; ?>,
        dryerFeePerRound: <?php echo $prices['dryer_per_round'] ?? 70; ?>,
        foldingFee: <?php echo $prices['folding_service'] ?? 0; ?>
    };

    // Pass product data to JavaScript
    const PRODUCTS = <?php echo json_encode($productsByCategory); ?>;

    // Check plastic bag stock
    const plasticBagStock = <?php
                            $plasticBagStock = 0;
                            if (isset($productsByCategory[4])) {
                                foreach ($productsByCategory[4] as $product) {
                                    $plasticBagStock += $product['available_units'];
                                }
                            }
                            echo $plasticBagStock;
                            ?>;
</script>

<script src="assets/js/prelist-order-modal.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Disable prelist button if no plastic bags
        if (plasticBagStock <= 0) {
            document.getElementById('prelistAllOrdersButton').disabled = true;
        }
    });
</script>