<?php
// modals/cancel-laundry.php
?>
<link href="assets/css/laundry-modals.css" rel="stylesheet">

<div class="modal fade" id="cancelLaundryModal" tabindex="-1" aria-labelledby="cancelLaundryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 rounded-lg">
            <!-- Modal Header -->
            <div class="modal-header" style="background: #dc3545; color: white;">
                <h5 class="modal-title" id="cancelLaundryModalLabel">
                    Cancel Laundry Order
                    <span id="cancel_queue_number_badge" class="badge bg-light text-dark ms-2">#00</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1);"></button>
            </div>

            <form method="POST" action="req/cancel-laundry.php" id="cancelLaundryForm">
                <input type="hidden" name="laundry_id" id="cancel_laundry_id">
                <input type="hidden" name="customer_id" id="cancel_customer_id">
                <input type="hidden" name="deducted_balance" id="cancel_deducted_balance">

                <div class="modal-body">
                    <!-- Customer Info -->
                    <div class="mb-3">
                        <h6 class="fw-bold text-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Cancel Order Confirmation
                        </h6>
                        <p class="text-muted small mb-2">
                            Customer: <strong id="cancel_customer_name">N/A</strong>
                        </p>
                        <!-- <p class="text-muted small">
                            Order Status: <span id="cancel_order_status" class="badge bg-secondary">N/A</span>
                        </p> -->
                    </div>

                    <hr>

                    <!-- Cancellation Reason -->
                    <div class="mb-3">
                        <label for="cancellation_reason" class="form-label">
                            Reason for Cancellation <span class="text-danger">*</span>
                        </label>
                        <select name="cancellation_reason" id="cancellation_reason" class="form-select" required>
                            <option value="">-- Select Reason --</option>
                            <option value="Customer Request">Customer Request</option>
                            <option value="Payment Issues">Payment Issues</option>
                            <option value="Service Unavailable">Service Unavailable</option>
                            <option value="Duplicate Order">Duplicate Order</option>
                            <option value="Customer No-Show">Customer No-Show</option>
                            <option value="Equipment Malfunction">Equipment Malfunction</option>
                            <option value="Staff Error">Staff Error</option>
                            <option value="Other">Other (Please specify)</option>
                        </select>
                    </div>

                    <!-- Additional Notes (shown when "Other" is selected) -->
                    <div class="mb-3" id="other_reason_container" style="display: none;">
                        <label for="cancellation_notes" class="form-label">
                            Please Specify <span class="text-danger">*</span>
                        </label>
                        <textarea name="cancellation_notes" id="cancellation_notes"
                            class="form-control" rows="3"
                            placeholder="Please provide details about the cancellation..."></textarea>
                    </div>

                    <!-- Optional Notes (always visible) -->
                    <div class="mb-3" id="general_notes_container">
                        <label for="general_cancellation_notes" class="form-label">
                            Additional Notes (Optional)
                        </label>
                        <textarea name="general_cancellation_notes" id="general_cancellation_notes"
                            class="form-control" rows="2"
                            placeholder="Any additional information..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        style="background: #6c757d; color: white; border-color: #6c757d;">
                        <i class="fas fa-times me-1"></i> Close
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban me-1"></i> Cancel Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cancelModal = document.getElementById('cancelLaundryModal');
        const reasonSelect = document.getElementById('cancellation_reason');
        const otherReasonContainer = document.getElementById('other_reason_container');
        const cancellationNotes = document.getElementById('cancellation_notes');
        const cancelForm = document.getElementById('cancelLaundryForm');

        // Show/hide "Other" reason input
        reasonSelect.addEventListener('change', function() {
            if (this.value === 'Other') {
                otherReasonContainer.style.display = 'block';
                cancellationNotes.required = true;
            } else {
                otherReasonContainer.style.display = 'none';
                cancellationNotes.required = false;
                cancellationNotes.value = '';
            }
        });

        // Populate modal when opened
        cancelModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;

            // Get data from button
            const laundryId = button.getAttribute('data-id');
            const queueNumber = button.getAttribute('data-queue-number');
            const customerName = button.getAttribute('data-customer-name');
            const customerId = button.getAttribute('data-customer-id');
            const orderStatus = button.getAttribute('data-status');
            const deductedBalance = parseFloat(button.getAttribute('data-deducted-balance') || 0);

            // Populate form fields
            document.getElementById('cancel_laundry_id').value = laundryId;
            document.getElementById('cancel_customer_id').value = customerId;
            document.getElementById('cancel_deducted_balance').value = deductedBalance;
            document.getElementById('cancel_queue_number_badge').textContent = '#' + queueNumber;
            document.getElementById('cancel_customer_name').textContent = customerName;

            // Reset form
            reasonSelect.value = '';
            cancellationNotes.value = '';
            document.getElementById('general_cancellation_notes').value = '';
            otherReasonContainer.style.display = 'none';
            cancellationNotes.required = false;
        });

        // Form submission with SweetAlert2 confirmation
        cancelForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Validate form
            if (!reasonSelect.value) {
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Please select a reason for cancellation.',
                    icon: 'error',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'btn btn-primary custom-swal-button',
                    }
                });
                return;
            }

            if (reasonSelect.value === 'Other' && !cancellationNotes.value.trim()) {
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Please specify the reason for cancellation.',
                    icon: 'error',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'btn btn-primary custom-swal-button',
                    }
                });
                cancellationNotes.focus();
                return;
            }

            const queueNumber = document.getElementById('cancel_queue_number_badge').textContent;
            const customerName = document.getElementById('cancel_customer_name').textContent;
            const reason = reasonSelect.options[reasonSelect.selectedIndex].text;

            // Show final confirmation with SweetAlert2
            Swal.fire({
                title: 'Final Confirmation',
                html: `Are you sure you want to cancel order <strong>${queueNumber}</strong> for <strong>${customerName}</strong>?<br><br>
                  <strong>Reason:</strong> ${reason}<br><br>
                  This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Cancel Order',
                cancelButtonText: 'Go Back',
                background: '#fff',
                customClass: {
                    popup: 'rounded-3',
                    confirmButton: 'btn btn-danger custom-swal-button',
                    cancelButton: 'btn btn-secondary custom-swal-button',
                },
                buttonsStyling: false,
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    Swal.fire({
                        title: 'Cancelling Order...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Submit the form
                    cancelForm.submit();
                }
            });
        });
    });
</script>