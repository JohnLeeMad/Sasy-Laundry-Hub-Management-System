<link href="assets/css/laundry-modals.css" rel="stylesheet">

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--primary-color); color: white;">
                <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="req/add-user.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="type" id="add_type" value="registered">
                    
                    <div class="mb-3">
                        <label for="user_name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="user_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="user_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="user_email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="user_contact_num" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="user_contact_num" name="contact_num" required>
                    </div>

                    <div class="mb-3">
                        <label for="user_type" class="form-label">Type</label>
                        <select class="form-select" id="user_type" name="type" required>
                            <option value="registered">Registered</option>
                            <option value="walk-in">Walk-In</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="user_password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="user_password" name="password" required>
                        <small class="text-muted">Minimum 8 characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="user_confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="user_confirm_password" name="confirm_password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        style="background: #6c757d; color: white; border-color: #6c757d;">
                        Cancel
                    </button>                    
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>