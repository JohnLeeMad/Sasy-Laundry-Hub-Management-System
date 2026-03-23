<div class="modal fade" id="editTransactionModal" tabindex="-1" aria-labelledby="editTransactionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 rounded-lg">
            <!-- Modal Header -->
            <div class="modal-header" style="background: var(--primary-color); color: white;">
                <h5 class="modal-title" id="editTransactionModalLabel">Edit Product Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1);"></button>
            </div>
            <div class="modal-body">
                <form id="editTransactionForm" method="POST" action="req/edit-transaction.php">
                    <input type="hidden" name="id" id="editTransactionId">

                    <!-- Product Name Section -->
                    <div class="mb-3">
                        <label for="editProductName" class="form-label" style="color: var(--primary-color);">Product Name</label>
                        <input type="text" id="editProductName" name="product_name" class="form-control" readonly
                            style="background-color: #e9ecef; opacity: 1; cursor: not-allowed;">
                    </div>

                    <!-- Quantity Section -->
                    <div class="mb-3">
                        <label for="editQuantity" class="form-label" style="color: var(--primary-color);">Quantity</label>
                        <input type="number" class="form-control" id="editQuantity" name="quantity" min="1" required>
                    </div>

                    <!-- Transaction Type Section -->
                    <div class="mb-3">
                        <label for="editType" class="form-label" style="color: var(--primary-color);">Transaction Type</label>
                        <select class="form-select" id="editType" name="type" required>
                            <option value="IN">Stock In</option>
                            <option value="OUT">Stock Out</option>
                            <option value="Used">Used</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            style="background: #6c757d; color: white; border-color: #6c757d;">
                            Close
                        </button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function populateEditTransactionModal(id, productName, quantity, type, maxStock) {
        document.getElementById('editTransactionId').value = id;
        document.getElementById('editProductName').value = productName;
        document.getElementById('editQuantity').value = quantity;
        document.getElementById('editQuantity').max = maxStock;
        document.getElementById('editType').value = type;

        if ((type === 'OUT' || type === 'Used') && maxStock === 0) {
            document.getElementById('editQuantity').disabled = true;
        } else {
            document.getElementById('editQuantity').disabled = false;
        }
    }
</script>