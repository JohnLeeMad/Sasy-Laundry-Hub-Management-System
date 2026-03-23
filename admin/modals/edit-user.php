<link href="assets/css/laundry-modals.css" rel="stylesheet">

<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--primary-color); color: white;">
                <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="req/update-user.php" method="POST" id="editUserForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="source_table" id="edit_source_table" value="users">
                    <input type="hidden" name="original_type" id="edit_original_type">

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
                        <label for="edit_type" class="form-label">Type</label>
                        <select class="form-select" id="edit_type" name="type" required>
                            <option value="registered">Registered</option>
                            <option value="walk-in">Walk-In</option>
                        </select>
                    </div>

                    <div class="mb-3" id="password_group">
                        <label for="edit_password" class="form-label">
                            Password
                            <span id="password_required_label" style="display: none; color: red;">*</span>
                            <span id="password_optional_label">(Leave blank to keep current)</span>
                        </label>
                        <input type="password" class="form-control" id="edit_password" name="password">
                        <small class="text-muted">Minimum 8 characters</small>
                    </div>

                    <div class="mb-3" id="confirm_password_group">
                        <label for="edit_confirm_password" class="form-label">
                            Confirm Password
                            <span id="confirm_required_label" style="display: none; color: red;">*</span>
                        </label>
                        <input type="password" class="form-control" id="edit_confirm_password" name="confirm_password">
                    </div>

                    <div class="alert alert-warning" id="type_change_warning" style="display: none;">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Password Required:</strong> Changing from Walk-In to Registered requires setting a password for account access.
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
        const editButtons = document.querySelectorAll('.edit-user-btn');
        const editTypeSelect = document.getElementById('edit_type');
        const editPasswordInput = document.getElementById('edit_password');
        const editConfirmPasswordInput = document.getElementById('edit_confirm_password');
        const passwordRequiredLabel = document.getElementById('password_required_label');
        const passwordOptionalLabel = document.getElementById('password_optional_label');
        const confirmRequiredLabel = document.getElementById('confirm_required_label');
        const typeChangeWarning = document.getElementById('type_change_warning');
        const editUserForm = document.getElementById('editUserForm');

        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const email = this.getAttribute('data-email');
                const contactNum = this.getAttribute('data-contact-num');
                const type = this.getAttribute('data-type');
                const sourceTable = this.getAttribute('data-source-table');

                document.getElementById('edit_id').value = id;
                document.getElementById('edit_name').value = name;
                document.getElementById('edit_email').value = email;
                document.getElementById('edit_contact_num').value = contactNum;
                document.getElementById('edit_type').value = type;
                document.getElementById('edit_source_table').value = sourceTable;
                document.getElementById('edit_original_type').value = type;

                document.getElementById('editUserModalLabel').textContent = 'Edit ' + (type === 'registered' ? 'Registered User' : 'Walk-In User');

                // Reset password fields
                editPasswordInput.value = '';
                editConfirmPasswordInput.value = '';
                editPasswordInput.removeAttribute('required');
                editConfirmPasswordInput.removeAttribute('required');

                passwordRequiredLabel.style.display = 'none';
                passwordOptionalLabel.style.display = 'inline';
                confirmRequiredLabel.style.display = 'none';
                typeChangeWarning.style.display = 'none';
            });
        });

        editTypeSelect.addEventListener('change', function() {
            const originalType = document.getElementById('edit_original_type').value;
            const newType = this.value;

            if (originalType === 'walk-in' && newType === 'registered') {
                editPasswordInput.setAttribute('required', 'required');
                editConfirmPasswordInput.setAttribute('required', 'required');
                passwordRequiredLabel.style.display = 'inline';
                passwordOptionalLabel.style.display = 'none';
                confirmRequiredLabel.style.display = 'inline';
                typeChangeWarning.style.display = 'block';
            } else {
                editPasswordInput.removeAttribute('required');
                editConfirmPasswordInput.removeAttribute('required');
                passwordRequiredLabel.style.display = 'none';
                passwordOptionalLabel.style.display = 'inline';
                confirmRequiredLabel.style.display = 'none';
                typeChangeWarning.style.display = 'none';
            }
        });

        editUserForm.addEventListener('submit', function(e) {
            const password = editPasswordInput.value;
            const confirmPassword = editConfirmPasswordInput.value;

            if (password || confirmPassword) {
                if (password.length < 8) {
                    e.preventDefault();
                    alert('Password must be at least 8 characters long.');
                    return false;
                }

                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Passwords do not match.');
                    return false;
                }
            }

            const originalType = document.getElementById('edit_original_type').value;
            const newType = editTypeSelect.value;

            if (originalType === 'walk-in' && newType === 'registered' && !password) {
                e.preventDefault();
                alert('Password is required when changing from Walk-In to Registered customer.');
                return false;
            }
        });
    });
</script>