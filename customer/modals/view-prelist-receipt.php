<?php
require_once '../config/db_conn.php';

$priceQuery = $conn->query("SELECT item_name, price FROM laundry_prices");
$prices = [];
while ($row = $priceQuery->fetch_assoc()) {
    $prices[$row['item_name']] = $row['price'];
}

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
<link href="assets/css/receipt.css" rel="stylesheet">

<style>
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

<div class="modal fade" id="viewPrelistReceiptModal" tabindex="-1" aria-labelledby="viewPrelistReceiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 rounded-lg">
            <div class="modal-header" style="background: var(--primary-color); color: white; padding: 0.75rem 1rem;">
                <h5 class="modal-title fs-5" id="viewPrelistReceiptModalLabel">
                    Pre-list Quotation Details
                    <span id="view_prelist_receipt_whites_badge" class="whites-order-badge ms-2" style="display: none;">
                        <i class="fas fa-tshirt"></i>
                        <span>Whites Order</span>
                    </span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1);"></button>
            </div>
            <div class="modal-body p-3">
                <div class="card border-0">
                    <div class="card-body p-2">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="info-group">
                                    <label class="info-label">Quotation Number:</label>
                                    <span class="info-value" id="view_prelist_receipt_number">N/A</span>
                                </div>
                                <div class="info-group">
                                    <label class="info-label">Customer Name:</label>
                                    <span class="info-value" id="view_prelist_receipt_customer_name">N/A</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <label class="info-label">Created At:</label>
                                    <span class="info-value" id="view_prelist_receipt_created_at">N/A</span>
                                </div>
                                <div class="info-group">
                                    <label class="info-label">Accommodated by:</label>
                                    <span class="info-value" id="view_prelist_accommodated_by">N/A</span>
                                </div>
                            </div>
                        </div>

                        <div class="receipt-order-details mb-3">
                            <h6 class="fw-bold mb-2" style="font-size: 0.9rem;">Order Details:</h6>
                            <div id="view_prelist_receipt_order_details" class="receipt-details-content" style="font-size: 0.85rem; padding: 0.5rem;">
                                <!-- Details will be rendered heres -->
                            </div>
                        </div>

                        <div class="receipt-amount-highlight">
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <div class="info-group">
                                        <label class="info-label">Total Price:</label>
                                        <span class="info-value fw-bold" style="font-size: 1.1em;" id="view_prelist_receipt_total_price">₱0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-end align-items-center py-1 px-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"
                    style="background: #6c757d; color: white; border-color: #6c757d; padding: 0.2rem 0.4rem; font-size: 0.75rem;">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        $('#viewPrelistReceiptModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var modal = $(this);

            modal.find('#view_prelist_receipt_number').text(button.data('receipt-number') || 'N/A');
            modal.find('#view_prelist_receipt_customer_name').text(button.data('customer-name') || 'N/A');
            modal.find('#view_prelist_receipt_order_id').text(button.data('id') || 'N/A');

            var paymentStatus = button.data('payment-status') || 'N/A';
            var statusElement = modal.find('#view_prelist_receipt_payment_status');
            if (statusElement.length) {
                statusElement.text(paymentStatus);
                statusElement.removeClass('status-paid status-unpaid status-partial');
                if (paymentStatus.toLowerCase() === 'paid') {
                    statusElement.addClass('status-paid');
                } else if (paymentStatus.toLowerCase() === 'unpaid') {
                    statusElement.addClass('status-unpaid');
                } else if (paymentStatus.toLowerCase() === 'partial') {
                    statusElement.addClass('status-partial');
                }
            }

            modal.find('#view_prelist_receipt_amount_tendered').text('₱' + parseFloat(button.data('amount-tendered') || 0).toFixed(2));
            modal.find('#view_prelist_receipt_total_price').text('₱' + parseFloat(button.data('total-price') || 0).toFixed(2));
            modal.find('#view_prelist_receipt_amount_change').text('₱' + parseFloat(button.data('amount-change') || 0).toFixed(2));
            
            var orderDetails = button.data('order-details') || 'No details available';
            
            var isWhitesOrder = button.data('is-whites-order');
            var separateWhites = button.data('separate-whites');
            
            isWhitesOrder = isWhitesOrder === 1 || isWhitesOrder === '1' || isWhitesOrder === true;
            separateWhites = separateWhites === 1 || separateWhites === '1' || separateWhites === true;
            
            console.log('Is Whites Order (Prelist):', isWhitesOrder, 'Separate Whites:', separateWhites);
            
            if (isWhitesOrder) {
                modal.find('#view_prelist_receipt_whites_badge').show();
            } else {
                modal.find('#view_prelist_receipt_whites_badge').hide();
            }
            
            orderDetails = orderDetails.replace(/Separate Whites:.*?(<br>|$)/gi, '');
            
            if (separateWhites && !isWhitesOrder) {
                orderDetails += '<br>Separate Whites: Yes';
            }
            
            modal.find('#view_prelist_receipt_order_details').html(orderDetails.replace(/\n/g, '<br>'));

            var createdAtRaw = button.data('created-at') || 'N/A';
            if (createdAtRaw !== 'N/A') {
                var dateObj = new Date(createdAtRaw);
                if (!isNaN(dateObj.getTime())) {
                    var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                    var month = months[dateObj.getMonth()];
                    var day = String(dateObj.getDate()).padStart(2, '0');
                    var year = dateObj.getFullYear();
                    var hours = dateObj.getHours();
                    var minutes = String(dateObj.getMinutes()).padStart(2, '0');
                    var ampm = hours >= 12 ? 'PM' : 'AM';
                    hours = hours % 12;
                    hours = hours ? hours : 12;
                    hours = String(hours).padStart(2, '0');
                    createdAtRaw = `${month} ${day}, ${year} - ${hours}:${minutes} ${ampm}`;
                }
            }
            modal.find('#view_prelist_receipt_created_at').text(createdAtRaw);

            var accommodatedBy = button.data('accommodated-by') || 'System';
            modal.find('#view_prelist_accommodated_by').text(accommodatedBy);
        });
    });
</script>