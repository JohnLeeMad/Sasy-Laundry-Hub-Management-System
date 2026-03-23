<?php
require_once '../config/db_conn.php';

// Fetch pre-listed order data based on the order ID passed via data attribute
$prelistOrderId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$prelistData = [];
if ($prelistOrderId) {
    $stmt = $conn->prepare("
        SELECT 
            po.id,
            po.customer_id,
            po.total_price,
            po.deducted_balance,
            po.adjusted_total_price,
            po.remarks,
            po.created_at,
            po.is_whites_order,
            u.name AS customer_name,
            u.contact_num AS customer_phone,
            pd.rounds_of_wash,
            pd.scoops_of_detergent,
            pd.dryer_preference,
            pd.folding_service,
            pd.bleach_cups,
            pd.fabcon_cups,
            pd.detergent_product_id,
            pd.fabcon_product_id,
            pd.bleach_product_id,
            pd.separate_whites,
            pi.tops,
            pi.bottoms,
            pi.undergarments,
            pi.delicates,
            pi.linens,
            pi.curtains_drapes,
            pi.blankets_comforters,
            pi.others,
            pr.receipt_number,
            pr.payment_status,
            pr.total_price AS receipt_total_price,
            pr.order_details,
            pr.created_at AS receipt_created_at,
            pr.accommodated_by
        FROM prelist_orders po
        LEFT JOIN users u ON po.customer_id = u.id
        LEFT JOIN prelist_details pd ON po.id = pd.prelist_order_id
        LEFT JOIN prelist_items pi ON po.id = pi.prelist_order_id
        LEFT JOIN prelist_receipts pr ON po.id = pr.prelist_order_id
        WHERE po.id = ?
    ");
    $stmt->bind_param('i', $prelistOrderId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $prelistData = $result->fetch_assoc();
    }
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

<div class="modal fade" id="acceptPrelistModal" tabindex="-1" aria-labelledby="acceptPrelistModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 rounded-lg">
            <!-- Modal Header -->
            <div class="modal-header" style="background: var(--primary-color); color: white;">
                <h5 class="modal-title" id="acceptPrelistModalLabel">
                    Accept Pre-listed Order
                    <span id="accept_queue_number_badge" class="badge bg-light text-dark ms-2">#00</span>
                    <span id="accept_whites_badge" class="whites-order-badge ms-2" style="display: none;">
                        <i class="fas fa-tshirt"></i>
                        <span>Whites Order</span>
                    </span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1);"></button>
            </div>
            <form method="POST" action="req/accept-prelist.php" enctype="multipart/form-data">
                <input type="hidden" name="prelist_id" id="accept_prelist_id" value="<?php echo htmlspecialchars($prelistOrderId ?? ''); ?>">
                <input type="hidden" name="customer_type" id="accept_customer_type" value="Registered">
                <input type="hidden" name="customer_id" id="accept_customer_id" value="<?php echo htmlspecialchars($prelistData['customer_id'] ?? ''); ?>">
                <input type="hidden" name="change_stored_as_balance" id="accept_change_stored_as_balance" value="0">
                <input type="hidden" id="accept_adjusted_total_price" name="adjusted_total_price" value="<?php echo htmlspecialchars($prelistData['adjusted_total_price'] ?? '0.00'); ?>">
                <input type="hidden" name="separate_whites" id="accept_separate_whites_hidden" value="<?php echo htmlspecialchars($prelistData['separate_whites'] ?? '0'); ?>">
                <input type="hidden" name="is_whites_order" id="accept_is_whites_order_hidden" value="<?php echo htmlspecialchars($prelistData['is_whites_order'] ?? '0'); ?>">
                <div class="modal-body">

                    <div class="mb-4">
                        <h6 class="fw-bold" style="color: var(--primary-color);">Customer Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="accept_customer_name" class="form-label">Customer Name</label>
                                <input type="text" id="accept_customer_name" class="form-control" readonly
                                    style="background-color: #e9ecef; opacity: 1; cursor: not-allowed;"
                                    value="<?php echo htmlspecialchars($prelistData['customer_name'] ?? 'N/A'); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="accept_customer_phone" class="form-label">Phone Number</label>
                                <input type="text" id="accept_customer_phone" class="form-control" readonly
                                    style="background-color: #e9ecef; opacity: 1; cursor: not-allowed;"
                                    value="<?php echo htmlspecialchars($prelistData['customer_phone'] ?? 'N/A'); ?>">
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Laundry Details Section -->
                    <div class="mb-4">
                        <h6 class="fw-bold" style="color: var(--primary-color);">Laundry Details</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="accept_rounds_of_wash" class="form-label">Rounds of Wash</label>
                                <input type="number" name="rounds_of_wash" id="accept_rounds_of_wash" class="form-control" min="1" max="4" value="<?php echo htmlspecialchars($prelistData['rounds_of_wash'] ?? 1); ?>"
                                    title="Min: 1 round - Max: 4 rounds" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="accept_dryer_preference" class="form-label">Dryer Preference</label>
                                <select name="dryer_preference" id="accept_dryer_preference" class="form-select" title="0: No drying - 1: 1 round - 2: 2 rounds" disabled>
                                    <option value="0" <?php echo ($prelistData['dryer_preference'] ?? 0) == 0 ? 'selected' : ''; ?>>No Drying</option>
                                    <option value="1" <?php echo ($prelistData['dryer_preference'] ?? 0) == 1 ? 'selected' : ''; ?>>1 round</option>
                                    <option value="2" <?php echo ($prelistData['dryer_preference'] ?? 0) == 2 ? 'selected' : ''; ?>>2 rounds</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label for="accept_detergent_product" class="form-label">Detergent Product</label>
                                <select name="detergent_product_id" id="accept_detergent_product" class="form-select" disabled>
                                    <option value="">Select Detergent</option>
                                    <?php
                                    $stmt = $conn->prepare("
                                        SELECT sp.id, sp.name, sp.measurement, sp.unit_price, COALESCE(inv.available_units, 0) as available_units
                                        FROM supply_products sp
                                        LEFT JOIN inventory inv ON sp.id = inv.product_id
                                        WHERE sp.category_id = 1 AND sp.is_active = 1
                                        ORDER BY sp.name
                                    ");
                                    $stmt->execute();
                                    $detergentResult = $stmt->get_result();
                                    while ($product = $detergentResult->fetch_assoc()) {
                                        $selected = $product['id'] == $prelistData['detergent_product_id'] ? 'selected' : '';
                                        echo "<option value='{$product['id']}' $selected data-price='{$product['unit_price']}' data-stock='{$product['available_units']}'>"
                                            . htmlspecialchars($product['name']) . ($product['measurement'] ? ' - ' . htmlspecialchars($product['measurement']) : '')
                                            . " (₱" . number_format($product['unit_price'], 2) . "/scoop)"
                                            . ($product['available_units'] <= 0 ? ' - Out of Stock' : ($product['available_units'] <= 10 ? ' - Low Unit (' . $product['available_units'] . ')' : ''))
                                            . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="accept_scoops_of_detergent" class="form-label">Scoops of Detergent</label>
                                <input type="number" name="scoops_of_detergent" id="accept_scoops_of_detergent" class="form-control"
                                    min="1" max="10" value="<?php echo htmlspecialchars($prelistData['scoops_of_detergent'] ?? 0); ?>" title="Min: 1 scoops - Max: 10 scoops" readonly>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label for="accept_fabcon_product" class="form-label">Fabric Conditioner</label>
                                <select name="fabcon_product_id" id="accept_fabcon_product" class="form-select" disabled>
                                    <option value="">Select Fabric Conditioner (Optional)</option>
                                    <?php
                                    $stmt = $conn->prepare("
                                        SELECT sp.id, sp.name, sp.measurement, sp.unit_price, COALESCE(inv.available_units, 0) as available_units
                                        FROM supply_products sp
                                        LEFT JOIN inventory inv ON sp.id = inv.product_id
                                        WHERE sp.category_id = 2 AND sp.is_active = 1
                                        ORDER BY sp.name
                                    ");
                                    $stmt->execute();
                                    $fabconResult = $stmt->get_result();
                                    while ($product = $fabconResult->fetch_assoc()) {
                                        $selected = $product['id'] == $prelistData['fabcon_product_id'] ? 'selected' : '';
                                        echo "<option value='{$product['id']}' $selected data-price='{$product['unit_price']}' data-stock='{$product['available_units']}'>"
                                            . htmlspecialchars($product['name']) . ($product['measurement'] ? ' - ' . htmlspecialchars($product['measurement']) : '')
                                            . " (₱" . number_format($product['unit_price'], 2) . "/cup)"
                                            . ($product['available_units'] <= 0 ? ' - Out of Stock' : ($product['available_units'] <= 10 ? ' - Low Unit (' . $product['available_units'] . ')' : ''))
                                            . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="accept_fabcon_cups" class="form-label">Fabcon (Cups)</label>
                                <input type="number" name="fabcon_cups" id="accept_fabcon_cups" class="form-control"
                                    min="0" max="10" value="<?php echo htmlspecialchars($prelistData['fabcon_cups'] ?? 0); ?>" title="Min: 0 cups - Max: 10 cups" readonly>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label for="accept_bleach_product" class="form-label">Bleach Product</label>
                                <select name="bleach_product_id" id="accept_bleach_product" class="form-select" disabled>
                                    <option value="">Select Bleach (Optional)</option>
                                    <?php
                                    $stmt = $conn->prepare("
                                        SELECT sp.id, sp.name, sp.measurement, sp.unit_price, COALESCE(inv.available_units, 0) as available_units
                                        FROM supply_products sp
                                        LEFT JOIN inventory inv ON sp.id = inv.product_id
                                        WHERE sp.category_id = 3 AND sp.is_active = 1
                                        ORDER BY sp.name
                                    ");
                                    $stmt->execute();
                                    $bleachResult = $stmt->get_result();
                                    while ($product = $bleachResult->fetch_assoc()) {
                                        $selected = $product['id'] == $prelistData['bleach_product_id'] ? 'selected' : '';
                                        echo "<option value='{$product['id']}' $selected data-price='{$product['unit_price']}' data-stock='{$product['available_units']}'>"
                                            . htmlspecialchars($product['name']) . ($product['measurement'] ? ' - ' . htmlspecialchars($product['measurement']) : '')
                                            . " (₱" . number_format($product['unit_price'], 2) . "/cup)"
                                            . ($product['available_units'] <= 0 ? ' - Out of Stock' : ($product['available_units'] <= 10 ? ' - Low Unit (' . $product['available_units'] . ')' : ''))
                                            . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="accept_bleach_cups" class="form-label">Bleach (Cups)</label>
                                <input type="number" name="bleach_cups" id="accept_bleach_cups" class="form-control"
                                    min="0" max="5" value="<?php echo htmlspecialchars($prelistData['bleach_cups'] ?? 0); ?>" title="Min: 0 cups - Max: 5 cups" readonly>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input type="hidden" name="folding_service" value="0">
                                    <input class="form-check-input" type="checkbox" name="folding_service" id="accept_folding_service" value="1"
                                        title="Check to include folding service" <?php echo ($prelistData['folding_service'] ?? 0) == 1 ? 'checked' : ''; ?> disabled>
                                    <label class="form-check-label" for="folding_service">Folding Service</label>
                                </div>
                            </div>
                            <div class="col-md-6" id="accept_separate_whites_container">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="accept_separate_whites" value="1"
                                        title="Check to separate whites" <?php echo ($prelistData['separate_whites'] ?? 0) == 1 ? 'checked' : ''; ?> disabled>
                                    <label class="form-check-label" for="separate_whites">Separate Whites</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <!-- Payment Section -->
                    <div class="mb-4">
                        <h6 class="fw-bold" style="color: var(--primary-color);">Payment Details</h6>
                        <div id="acceptPaymentDetails" class="mt-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="accept_payment_status" class="form-label">Payment Status</label>
                                    <input type="text" id="accept_payment_status" class="form-control" readonly
                                        style="background-color: #e9ecef; opacity: 1; cursor: not-allowed;" value="<?php echo htmlspecialchars($prelistData['payment_status'] ?? 'Unpaid'); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="accept_amount_tendered" class="form-label">Amount Tendered</label>
                                    <input type="number" name="amount_tendered" id="accept_amount_tendered" class="form-control" value="0.00" min="0.01" step="0.01" required>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label for="accept_total_price" class="form-label">Total Price</label>
                                    <input type="hidden" id="accept_deducted_balance" name="deducted_balance" value="<?php echo htmlspecialchars($prelistData['deducted_balance'] ?? '0.00'); ?>">
                                    <input type="text" id="accept_total_price" name="total_price" class="form-control" readonly
                                        style="background-color: #e9ecef; opacity: 1; cursor: not-allowed;"
                                        value="<?php echo htmlspecialchars($prelistData['adjusted_total_price'] ?? '0.00'); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="accept_change" class="form-label">Change</label>
                                    <input type="text" id="accept_change" class="form-control" readonly
                                        style="background-color: #e9ecef; opacity: 1; cursor: not-allowed;" value="0.00">
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <!-- Remarks -->
                    <div class="mb-4">
                        <label for="accept_remarks" class="form-label">Remarks</label>
                        <textarea name="remarks" id="accept_remarks" class="form-control" rows="3" readonly><?php echo htmlspecialchars($prelistData['remarks'] ?? ''); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        <i class="fas fa-user-circle me-1"></i> Pre-listed by:
                        <span id="accept_accommodated_by" class="fw-bold"><?php echo htmlspecialchars($prelistData['accommodated_by'] ?? 'System'); ?></span>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            style="background: #6c757d; color: white; border-color: #6c757d;">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">Accept Order</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const acceptButtons = document.querySelectorAll('.accept-prelist-btn');
        const modal = document.getElementById('acceptPrelistModal');
        const acceptOrderButton = modal.querySelector('button[type="submit"]');
        const accommodatedByElement = document.getElementById('accept_accommodated_by');
        const queueNumberBadge = document.getElementById('accept_queue_number_badge');
        const whitesBadge = document.getElementById('accept_whites_badge');
        const separateWhitesContainer = document.getElementById('accept_separate_whites_container');

        // Form elements
        const formElements = {
            prelistId: document.getElementById('accept_prelist_id'),
            customerName: document.getElementById('accept_customer_name'),
            customerPhone: document.getElementById('accept_customer_phone'),
            paymentStatus: document.getElementById('accept_payment_status'),
            amountTendered: document.getElementById('accept_amount_tendered'),
            totalPrice: document.getElementById('accept_total_price'),
            change: document.getElementById('accept_change'),
            deductedBalance: document.getElementById('accept_deducted_balance'),
            adjustedTotalPrice: document.getElementById('accept_adjusted_total_price'),
            separateWhites: document.getElementById('accept_separate_whites_hidden'),
            isWhitesOrder: document.getElementById('accept_is_whites_order_hidden')
        };

        function initModal() {
            setupAcceptButtons();
            setupEventListeners();
        }

        // Set up accept buttons
        function setupAcceptButtons() {
            acceptButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const data = {
                        id: this.getAttribute('data-id') || '',
                        customerName: this.getAttribute('data-customer-name') || 'N/A',
                        customerPhone: this.getAttribute('data-customer-phone') || 'N/A',
                        totalPrice: this.getAttribute('data-total-price') || '0.00',
                        adjustedTotalPrice: this.getAttribute('data-adjusted-total-price') || '0.00',
                        deductedBalance: this.getAttribute('data-deducted-balance') || '0.00',
                        paymentStatus: this.getAttribute('data-payment-status') || 'Unpaid',
                        remarks: this.getAttribute('data-remarks') || '',
                        roundsOfWash: this.getAttribute('data-rounds-of-wash') || '1',
                        scoopsOfDetergent: this.getAttribute('data-scoops-of-detergent') || '0',
                        dryerPreference: this.getAttribute('data-dryer-preference') || '0',
                        foldingService: this.getAttribute('data-folding-service') || '0',
                        fabconCups: this.getAttribute('data-fabcon-cups') || '0',
                        bleachCups: this.getAttribute('data-bleach-cups') || '0',
                        detergent_product_id: this.getAttribute('data-detergent-product-id') || '',
                        fabcon_product_id: this.getAttribute('data-fabcon-product-id') || '',
                        bleach_product_id: this.getAttribute('data-bleach-product-id') || '',
                        separateWhites: this.getAttribute('data-separate-whites') || '0',
                        isWhitesOrder: this.getAttribute('data-is-whites-order') || '0',
                        accommodatedBy: this.getAttribute('data-accommodated-by') || 'System',
                        queueNumber: this.getAttribute('data-queue-number') || 'N/A'
                    };

                    populateForm(data);
                    accommodatedByElement.textContent = data.accommodatedBy;
                    queueNumberBadge.textContent = `#${data.queueNumber}`;
                    
                    // Show/hide whites order badge and separate whites checkbox
                    if (data.isWhitesOrder === '1' || data.isWhitesOrder === 1) {
                        whitesBadge.style.display = 'inline-flex';
                        separateWhitesContainer.style.display = 'none';
                    } else {
                        whitesBadge.style.display = 'none';
                        separateWhitesContainer.style.display = 'block';
                    }
                    
                    calculateChange(parseFloat(data.adjustedTotalPrice));
                });
            });
        }

        // Populate form with pre-listed order data
        function populateForm(data) {
            formElements.prelistId.value = data.id;
            formElements.customerName.value = data.customerName;
            formElements.customerPhone.value = data.customerPhone;
            formElements.paymentStatus.value = data.paymentStatus;
            formElements.totalPrice.value = data.adjustedTotalPrice;
            formElements.deductedBalance.value = data.deductedBalance || '0.00';
            formElements.adjustedTotalPrice.value = data.adjustedTotalPrice;
            formElements.separateWhites.value = data.separateWhites;
            formElements.isWhitesOrder.value = data.isWhitesOrder;
            document.getElementById('accept_rounds_of_wash').value = data.roundsOfWash;
            document.getElementById('accept_dryer_preference').value = data.dryerPreference;
            document.getElementById('accept_scoops_of_detergent').value = data.scoopsOfDetergent;
            document.getElementById('accept_fabcon_cups').value = data.fabconCups;
            document.getElementById('accept_bleach_cups').value = data.bleachCups;
            document.getElementById('accept_folding_service').checked = data.foldingService === '1';
            document.getElementById('accept_separate_whites').checked = data.separateWhites === '1';
            document.getElementById('accept_detergent_product').value = data.detergent_product_id;
            document.getElementById('accept_fabcon_product').value = data.fabcon_product_id;
            document.getElementById('accept_bleach_product').value = data.bleach_product_id;
            document.getElementById('accept_remarks').value = data.remarks;
        }

        // Calculate change based on amount tendered
        function calculateChange(totalPrice) {
            const amountTendered = parseFloat(formElements.amountTendered.value) || 0;
            const change = Math.max(0, amountTendered - totalPrice);
            formElements.change.value = change.toFixed(2);

            // Validate minimum payment (50% of total)
            const minimumPayment = totalPrice * 0.5;
            acceptOrderButton.disabled = amountTendered < minimumPayment;
            if (amountTendered < minimumPayment) {
                addFeedbackMessage(formElements.amountTendered, `Minimum payment required: ₱${minimumPayment.toFixed(2)}`, true);
            } else {
                removeFeedbackMessage(formElements.amountTendered);
            }
        }

        // Add feedback message to input
        function addFeedbackMessage(element, message, isWarning = false) {
            removeFeedbackMessage(element);
            const feedbackDiv = document.createElement('div');
            feedbackDiv.className = `${isWarning ? 'text-warning' : 'text-danger'} small mt-1`;
            feedbackDiv.textContent = message;
            element.parentNode.appendChild(feedbackDiv);
        }

        // Remove feedback message from input
        function removeFeedbackMessage(element) {
            const existingFeedback = element.parentNode.querySelector('.text-warning, .text-danger');
            if (existingFeedback) {
                existingFeedback.remove();
            }
        }

        // Set up event listeners
        function setupEventListeners() {
            formElements.amountTendered.addEventListener('input', () => {
                calculateChange(parseFloat(formElements.totalPrice.value) || 0);
            });

            // Form submission validation
            const form = document.getElementById('acceptPrelistModal').querySelector('form');
            form.addEventListener('submit', function(e) {
                const totalPrice = parseFloat(formElements.totalPrice.value) || 0;
                const amountTendered = parseFloat(formElements.amountTendered.value) || 0;
                const minimumPayment = totalPrice * 0.5;

                if (amountTendered < minimumPayment) {
                    e.preventDefault();
                    alert(`Insufficient payment. Minimum required: ₱${minimumPayment.toFixed(2)}`);
                    return false;
                }

                // Add hidden field to set status to "Pending"
                const statusInput = document.createElement('input');
                statusInput.type = 'hidden';
                statusInput.name = 'status';
                statusInput.value = 'Pending';
                form.appendChild(statusInput);
            });
        }

        // Initialize the modal
        initModal();
    });
</script>