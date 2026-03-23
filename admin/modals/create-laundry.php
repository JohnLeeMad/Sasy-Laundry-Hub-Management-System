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

// Fetch existing phone numbers for validation
$phoneQuery = $conn->query("SELECT contact_num FROM users WHERE contact_num IS NOT NULL AND contact_num != ''");
$existingPhones = [];
while ($row = $phoneQuery->fetch_assoc()) {
    $existingPhones[] = $row['contact_num'];
}
?>

<link href="assets/css/laundry-modals.css" rel="stylesheet">

<style>
    /* Phone number validation styling */
    .was-validated .form-control:invalid,
    .form-control.is-invalid {
        border-color: #dc3545;
        padding-right: calc(1.5em + 0.75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 3.6.4.4.4-.4'/%3e%3cpath d='M6 7v1'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }

    .form-control.is-valid {
        border-color: #198754;
        /* Remove the green checkmark background image */
        background-image: none;
        padding-right: 0.75rem;
        /* Reset to normal padding */
    }

    .invalid-feedback {
        display: none;
    }

    .is-invalid~.invalid-feedback {
        display: block;
    }

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
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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

<div class="modal fade" id="createLaundryModal" tabindex="-1" aria-labelledby="createLaundryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 rounded-lg">
            <!-- Modal Header -->
            <div class="modal-header" style="background: var(--primary-color); color: white;">
                <h5 class="modal-title" id="createLaundryModalLabel">Create Laundry Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1);"></button>
            </div>
            <form method="POST" action="req/store-laundry.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <!-- Customer Information Section -->
                    <div class="mb-4">
                        <h6 class="fw-bold" style="color: var(--primary-color);">Customer Information</h6>
                        <div class="mb-3">
                            <label class="form-label">Customer Type: </label>
                            <br />
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="customer_type" id="registeredCustomer" value="registered" checked>
                                <label class="form-check-label" for="registeredCustomer">Customer List</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="customer_type" id="walkInCustomer" value="walk_in">
                                <label class="form-check-label" for="walkInCustomer">Walk-in Customer</label>
                            </div>
                        </div>
                        <!-- Updated Customer Selection Section -->
                        <div id="registeredCustomerFields" class="mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="customer_id" class="form-label">Select Customer</label>
                                    <select name="customer_id" id="customer_id" class="form-select">
                                        <option value="">Search and select customer...</option>
                                        <?php foreach ($customers as $customer): ?>
                                            <option value="<?php echo htmlspecialchars($customer['id']); ?>"
                                                data-contact_num="<?php echo htmlspecialchars($customer['contact_num']); ?>"
                                                data-balance="<?php echo htmlspecialchars($customer['balance']); ?>">
                                                <?php
                                                $typeLabel = $customer['type'] === 'walk-in' ? '[Walk-in]' : '[Registered]';
                                                echo "{$typeLabel} " . htmlspecialchars($customer['name']) . " - " . htmlspecialchars($customer['contact_num']);
                                                ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="registered_customer_phone" class="form-label">Phone Number</label>
                                    <input type="text" id="registered_customer_phone" class="form-control" readonly
                                        style="background-color: #e9ecef; opacity: 1; cursor: not-allowed;" value="N/A" />
                                </div>
                            </div>
                        </div>
                        <div id="walkInCustomerFields" class="mb-3 d-none">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="customer_name" class="form-label">Customer Name</label>
                                    <input type="text" name="customer_name" id="customer_name" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label for="customer_phone" class="form-label">Phone Number</label>
                                    <input type="tel" name="customer_phone" id="customer_phone" class="form-control"
                                        pattern="[0-9]{11}" maxlength="11"
                                        placeholder="09XXXXXXXXX"
                                        title="Please enter a valid 11-digit Philippine phone number (09XXXXXXXXX)"
                                        oninput="validatePhoneNumber(this)">
                                    <div class="invalid-feedback" id="phone-feedback">
                                        Please enter a valid 11-digit Philippine phone number starting with 09.
                                    </div>
                                    <div class="valid-feedback">
                                        Phone number looks good!
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>

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
                    <div class="mb-4">
                        <h6 class="fw-bold" style="color: var(--primary-color);">Payment Summary</h6>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label for="grand_total" class="form-label">Grand Total</label>
                                                <input type="text" id="grand_total" class="form-control fw-bold" readonly
                                                    style="background-color: #e9ecef; opacity: 1; cursor: not-allowed; font-size: 1.2rem;" value="₱0.00">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="amount_tendered" class="form-label">Amount Tendered</label>
                                                <input type="number" name="amount_tendered" id="amount_tendered" class="form-control"
                                                    value="0.00" min="1" step="0.01" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="change" class="form-label">Change</label>
                                                <input type="text" id="change" class="form-control" readonly
                                                    style="background-color: #e9ecef; opacity: 1; cursor: not-allowed;" value="₱0.00">
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-12">
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
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Global Remarks -->
                    <div class="mb-4">
                        <label for="global_remarks" class="form-label">Global Remarks</label>
                        <textarea name="global_remarks" id="global_remarks" class="form-control" rows="2" placeholder="Optional remarks for all orders"></textarea>
                    </div>

                    <!-- Hidden field to store order data -->
                    <input type="hidden" name="orders_data" id="orders_data">
                </div>
                <div class="modal-footer">
                    <button type="button" style="background: #6c757d; color: white; border-color: #6c757d;" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" id="createAllOrdersButton" class="btn btn-primary">
                        Create All Orders
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
                                <?php echo $product['available_units'] <= 0 ? 'disabled' : ''; ?>>
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
                <label class="form-label">Scoops of Detergent</label>
                <input type="number" class="form-control scoops-of-detergent" min="1" max="10" value="0" disabled>
                <div class="detergent-stock-info small mt-1"></div>
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
                                <?php echo $product['available_units'] <= 0 ? 'disabled' : ''; ?>>
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
                <label class="form-label">Fabcon (Cups)</label>
                <input type="number" class="form-control fabcon-cups" min="0" max="10" value="0" disabled>
                <div class="fabcon-stock-info small mt-1"></div>
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
                                <?php echo $product['available_units'] <= 0 ? 'disabled' : ''; ?>>
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
                <label class="form-label">Bleach (Cups)</label>
                <input type="number" class="form-control bleach-cups" min="0" max="5" value="0" disabled>
                <div class="bleach-stock-info small mt-1"></div>
            </div>
        </div>

        <!-- Add this section after the bleach cups section -->
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
                    <input class="form-check-input folding-service" type="checkbox" value="1" name="folding_service">
                    <label class="form-check-label">Folding Service</label>
                </div>
                <!-- ADD THIS CHECKBOX -->
                <div class="form-check mt-2">
                    <input class="form-check-input separate-whites" type="checkbox" value="1" name="separate_whites">
                    <label class="form-check-label">Separate Whites from Colored</label>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Order Remarks</label>
                <textarea class="form-control order-remarks" name="remarks" rows="2" placeholder="Optional remarks for this order"></textarea>
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

    // Pass existing phone numbers for validation
    const EXISTING_PHONES = <?php echo json_encode($existingPhones); ?>;

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

<script src="assets/js/create-laundry-modal.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const registeredCustomerRadio = document.getElementById('registeredCustomer');
        const walkInCustomerRadio = document.getElementById('walkInCustomer');
        const registeredCustomerFields = document.getElementById('registeredCustomerFields');
        const walkInCustomerFields = document.getElementById('walkInCustomerFields');
        const customerIdSelect = document.getElementById('customer_id');
        const registeredCustomerPhone = document.getElementById('registered_customer_phone');
        const customerBalance = document.getElementById('customer_balance');
        const useBalanceCheckbox = document.getElementById('use_balance');

        registeredCustomerRadio.addEventListener('change', () => {
            registeredCustomerFields.classList.remove('d-none');
            walkInCustomerFields.classList.add('d-none');
        });

        walkInCustomerRadio.addEventListener('change', () => {
            registeredCustomerFields.classList.add('d-none');
            walkInCustomerFields.classList.remove('d-none');
            customerBalance.textContent = '0.00';
            useBalanceCheckbox.disabled = true;
            useBalanceCheckbox.checked = false;
            useBalanceCheckbox.style.opacity = '0.5';
            useBalanceCheckbox.style.filter = 'grayscale(100%)';
        });

        // Update phone number and balance when customer is selected
        customerIdSelect.addEventListener('change', () => {
            const selectedOption = customerIdSelect.options[customerIdSelect.selectedIndex];
            if (selectedOption.value) {
                registeredCustomerPhone.value = selectedOption.dataset.contact_num || 'N/A';
                const balance = parseFloat(selectedOption.dataset.balance || 0);
                customerBalance.textContent = balance.toFixed(2);

                if (balance > 0) {
                    useBalanceCheckbox.disabled = false;
                    useBalanceCheckbox.style.opacity = '1';
                    useBalanceCheckbox.style.filter = '';
                } else {
                    useBalanceCheckbox.disabled = true;
                    useBalanceCheckbox.checked = false;
                    useBalanceCheckbox.style.opacity = '0.5';
                    useBalanceCheckbox.style.filter = 'grayscale(100%)';
                }
            } else {
                registeredCustomerPhone.value = 'N/A';
                customerBalance.textContent = '0.00';
                useBalanceCheckbox.disabled = true;
                useBalanceCheckbox.checked = false;
                useBalanceCheckbox.style.opacity = '0.5';
                useBalanceCheckbox.style.filter = 'grayscale(100%)';
            }
        });

        // Disable create button if no plastic bags
        if (plasticBagStock <= 0) {
            document.getElementById('createAllOrdersButton').disabled = true;
        }
    });
</script>