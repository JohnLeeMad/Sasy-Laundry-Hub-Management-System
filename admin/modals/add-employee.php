<link href="assets/css/laundry-modals.css" rel="stylesheet">

<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--primary-color); color: white;">
            <h5 class="modal-title" id="addEmployeeModalLabel">Add New Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="req/add-employee.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="type" id="add_role" value="admin">
                    
                    <div class="mb-3">
                        <label for="employee_name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="employee_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="employee_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="employee_email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="employee_contact_num" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="employee_contact_num" name="contact_num" required>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="Admin">Admin</option>
                            <option value="Staff">Staff</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="employee_password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="employee_password" name="password" required>
                        <small class="text-muted">Minimum 8 characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="employee_confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="employee_confirm_password" name="confirm_password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        style="background: #6c757d; color: white; border-color: #6c757d;">
                        Cancel
                    </button>                    
                    <button type="submit" class="btn btn-primary">Add Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>