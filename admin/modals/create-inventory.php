<div class="modal fade" id="manageSupplyModal" tabindex="-1" aria-labelledby="manageSupplyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manageSupplyModalLabel">Manage Supply</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="manageSupplyForm" method="POST" action="req/store-inventory.php">
                    <div class="mb-3">
                        <label for="supply_id" class="form-label">Supply Name</label>
                        <select class="form-select" id="supply_id" name="supply_id" required>
                            <option value="" disabled selected>Select Supply</option>
                            <?php generateSupplyOptions($conn); ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="type" class="form-label">Transaction Type</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="IN">Stock In</option>
                            <option value="OUT">Stock Out</option>
                            <option value="Used">Used</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
/**
 * Generate supply options for the dropdown.
 */
function generateSupplyOptions($conn) {
    $stmt = $conn->query("SELECT id, name FROM supply_list");
    while ($row = $stmt->fetch_assoc()) {
        echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</option>';
    }
}
?>