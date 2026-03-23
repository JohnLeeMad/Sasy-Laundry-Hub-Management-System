<link href="assets/css/laundry-modals.css" rel="stylesheet">

<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--primary-color); color: white;">
                <h5 class="modal-title" id="editEmployeeModalLabel">Edit Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="req/update-employee.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="type" id="edit_type">
                    <input type="hidden" name="source_table" id="edit_source_table">
                    <input type="hidden" name="is_own_account" id="edit_is_own_account">

                    <div class="alert alert-info" id="edit_own_account_warning" style="display: none;">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> You are editing your own account. Role changes are disabled for security reasons.
                    </div>

                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_contact_num" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="edit_contact_num" name="contact_num" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_role" class="form-label">Role</label>
                        <select class="form-select" id="edit_role" name="role" required>
                            <option value="Admin">Admin</option>
                            <option value="Staff">Staff</option>
                        </select>
                        <small class="text-muted" id="edit_role_help" style="display: none;">
                            <i class="fas fa-lock me-1"></i>Role cannot be changed for your own account.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="edit_password" class="form-label">Password (Leave blank to keep current)</label>
                        <input type="password" class="form-control" id="edit_password" name="password">
                        <small class="text-muted">Minimum 8 characters</small>
                    </div>

                    <div class="mb-3">
                        <label for="edit_confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="edit_confirm_password" name="confirm_password">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        style="background: #6c757d; color: white; border-color: #6c757d;">
                        Cancel
                    </button>                    
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.edit-employee-btn');
    
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const email = this.getAttribute('data-email');
            const contactNum = this.getAttribute('data-contact-num');
            const type = this.getAttribute('data-type');
            const sourceTable = this.getAttribute('data-source-table');
            const role = this.getAttribute('data-role');

            const currentUserId = '<?php echo $_SESSION['user_id'] ?? ''; ?>';
            const currentUserType = '<?php echo strtolower($_SESSION['user_type'] ?? '') . 's'; ?>';
            
            const isOwnAccount = (id === currentUserId && sourceTable === currentUserType);

            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_contact_num').value = contactNum;
            document.getElementById('edit_type').value = type;
            document.getElementById('edit_source_table').value = sourceTable;
            document.getElementById('edit_role').value = role;
            document.getElementById('editEmployeeModalLabel').textContent = 'Edit ' + role;

            const warningDiv = document.getElementById('edit_own_account_warning');
            const roleSelect = document.getElementById('edit_role');
            const roleHelp = document.getElementById('edit_role_help');

            if (isOwnAccount) {
                warningDiv.style.display = 'block';
                roleSelect.disabled = true;
                roleHelp.style.display = 'block';
            } else {
                warningDiv.style.display = 'none';
                roleSelect.disabled = false;
                roleHelp.style.display = 'none';
            }
        });
    });
});
</script>