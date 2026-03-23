<link href="assets/css/laundry-modals.css" rel="stylesheet">

<div class="modal fade" id="addUtilityModal" tabindex="-1" aria-labelledby="addUtilityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 rounded-lg">
            <div class="modal-header" style="background: var(--primary-color); color: white;">
                <h5 class="modal-title" id="addUtilityModalLabel">Add Utility Bill</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1);"></button>
            </div>
            <div class="modal-body">
                <form id="utilityBillForm" method="POST" action="req/store-utility.php" onsubmit="return validateBillDate()">
                    <div class="mb-3">
                        <label for="type" class="form-label" style="color: var(--primary-color);">Bill Type</label>
                        <select class="form-select" id="type" name="type" required onchange="toggleDescriptionField()">
                            <option value="" disabled selected>Select Type</option>
                            <option value="Electricity">Electricity</option>
                            <option value="Water">Water</option>
                            <option value="Maintenance">Maintenance</option>
                        </select>
                    </div>

                    <div class="mb-3" id="descriptionSection" style="display: none;">
                        <label for="description" class="form-label" style="color: var(--primary-color);">Description</label>
                        <input type="text" class="form-control" id="description" name="description" placeholder="Brief description of maintenance work">
                    </div>

                    <div class="mb-3">
                        <label for="amount" class="form-label" style="color: var(--primary-color);">Amount</label>
                        <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0" required>
                    </div>

                    <div class="mb-3">
                        <label for="bill_date" class="form-label" style="color: var(--primary-color);">Bill Date</label>
                        <input type="date" class="form-control" id="bill_date" name="bill_date" required>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            style="background: #6c757d; color: white; border-color: #6c757d;">
                            Close
                        </button>
                        <button type="submit" class="btn btn-primary">Save Bill</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleDescriptionField() {
        const typeSelect = document.getElementById('type');
        const descriptionSection = document.getElementById('descriptionSection');
        const descriptionInput = document.getElementById('description');

        if (typeSelect.value === 'Maintenance') {
            descriptionSection.style.display = 'block';
            descriptionInput.required = true;
        } else {
            descriptionSection.style.display = 'none';
            descriptionInput.required = false;
            descriptionInput.value = '';
        }
    }

    function validateBillDate() {
        const type = document.getElementById('type').value;
        const billDate = document.getElementById('bill_date').value;
        const description = document.getElementById('description').value;

        if (type === 'Maintenance') {
            if (!description.trim()) {
                alert('Please provide a description for the maintenance work.');
                return false;
            }
            return true;
        }

        if (!billDate) {
            alert('Please select a bill date.');
            return false;
        }

        return true;
    }
</script>