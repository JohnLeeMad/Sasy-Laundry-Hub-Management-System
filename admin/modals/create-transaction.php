<div class="modal fade" id="manageSupplyModal" tabindex="-1" aria-labelledby="manageSupplyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 rounded-lg">
            <div class="modal-header" style="background: var(--primary-color); color: white;">
                <h5 class="modal-title" id="manageSupplyModalLabel">Manage Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1);"></button>
            </div>
            <div class="modal-body">
                <form id="manageSupplyForm" method="POST" action="req/store-inventory.php">
                    <!-- Product Name Section -->
                    <div class="mb-3">
                        <label for="product_id" class="form-label" style="color: var(--primary-color);">Product Name</label>
                        <select class="form-select" id="product_id" name="product_id" required>
                            <option value="" disabled selected>Select Product</option>
                            <?php
                            $stmt = $conn->query("SELECT sp.id, sp.name, sc.name as category_name, 
                                                COALESCE(i.stock_quantity, 0) as stock_quantity
                                                FROM supply_products sp
                                                JOIN supply_categories sc ON sp.category_id = sc.id
                                                LEFT JOIN inventory i ON sp.id = i.product_id
                                                WHERE sp.is_active = 1
                                                ORDER BY sc.name, sp.name");
                            while ($row = $stmt->fetch_assoc()) {
                                $stockText = $row['stock_quantity'] . ' in stock';
                                echo '<option value="' . $row['id'] . '" data-stock="' . $row['stock_quantity'] . '">' .
                                    htmlspecialchars($row['name'] . ' (' . $row['category_name'] . ') - ' . $stockText) .
                                    '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Quantity Section -->
                    <div class="mb-3">
                        <label for="quantity" class="form-label" style="color: var(--primary-color);">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="1" required>
                    </div>

                    <!-- Transaction Type Section -->
                    <div class="mb-3">
                        <label for="type" class="form-label" style="color: var(--primary-color);">Transaction Type</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="IN">Stock In</option>
                            <option value="OUT">Stock Out</option>
                            <option value="Used">Used</option>
                        </select>
                    </div>

                    <!-- Description Section -->
                    <div class="mb-3">
                        <label for="description" class="form-label" style="color: var(--primary-color);">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter transaction description (optional)"></textarea>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            style="background: #6c757d; color: white; border-color: #6c757d;">
                            Close
                        </button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('product_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const stockQuantity = selectedOption.getAttribute('data-stock');
        const productId = this.value;
        const typeSelect = document.getElementById('type');
        const quantityInput = document.getElementById('quantity');

        // Update max quantity for OUT and Used transactions
        if (productId && (typeSelect.value === 'OUT' || typeSelect.value === 'Used')) {
            quantityInput.max = stockQuantity;
            
            // If current quantity exceeds available stock, adjust it
            if (parseInt(quantityInput.value) > parseInt(stockQuantity)) {
                quantityInput.value = stockQuantity;
            }
        } else {
            quantityInput.removeAttribute('max');
        }

        // Update the visual feedback for stock level
        updateStockDisplay(stockQuantity);
    });

    document.getElementById('type').addEventListener('change', function() {
        const productId = document.getElementById('product_id').value;
        const selectedOption = document.getElementById('product_id').options[document.getElementById('product_id').selectedIndex];
        const stockQuantity = selectedOption ? selectedOption.getAttribute('data-stock') : 0;
        
        if (productId && (this.value === 'OUT' || this.value === 'Used')) {
            document.getElementById('quantity').max = stockQuantity;
            
            // If current quantity exceeds available stock, adjust it
            if (parseInt(document.getElementById('quantity').value) > parseInt(stockQuantity)) {
                document.getElementById('quantity').value = stockQuantity;
            }
        } else {
            document.getElementById('quantity').removeAttribute('max');
        }
    });

    // Function to update stock display (optional visual enhancement)
    function updateStockDisplay(stockQuantity) {
        // Remove existing stock display if any
        const existingDisplay = document.getElementById('stock-display');
        if (existingDisplay) {
            existingDisplay.remove();
        }

        // Add new stock display
        const productSelect = document.getElementById('product_id');
        const stockDisplay = document.createElement('div');
        stockDisplay.id = 'stock-display';
        stockDisplay.className = 'mt-2 p-2 rounded';
        
        if (stockQuantity > 5) {
            stockDisplay.innerHTML = '<small class="text-success"><i class="fas fa-boxes"></i> <strong>' + stockQuantity + '</strong> items in stock</small>';
            stockDisplay.style.backgroundColor = '#f0f9f0';
        } else if (stockQuantity > 0) {
            stockDisplay.innerHTML = '<small class="text-warning"><i class="fas fa-exclamation-triangle"></i> <strong>' + stockQuantity + '</strong> items in stock - Low stock</small>';
            stockDisplay.style.backgroundColor = '#fffbf0';
        } else {
            stockDisplay.innerHTML = '<small class="text-danger"><i class="fas fa-times-circle"></i> <strong>Out of stock</strong></small>';
            stockDisplay.style.backgroundColor = '#fef0f0';
        }
        
        productSelect.parentNode.appendChild(stockDisplay);
    }

    // Initialize stock display on page load if a product is already selected
    document.addEventListener('DOMContentLoaded', function() {
        const initialProduct = document.getElementById('product_id');
        if (initialProduct.value) {
            const selectedOption = initialProduct.options[initialProduct.selectedIndex];
            const stockQuantity = selectedOption.getAttribute('data-stock');
            updateStockDisplay(stockQuantity);
        }
    });
</script>