function confirmLogout() {
  Swal.fire({
    title: "Logout Confirmation",
    text: "Are you sure you want to log out?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Yes, Log Out",
    cancelButtonText: "Cancel",
    background: "#fff",
    customClass: {
      popup: "rounded-3",
      confirmButton: "btn btn-primary custom-swal-button",
      cancelButton: "btn btn-secondary custom-swal-button",
    },
    buttonsStyling: false,
    reverseButtons: true,
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire({
        title: "Logging out...",
        text: "Please wait",
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        },
      });

      const form = document.createElement("form");
      form.method = "POST";
      form.action = "../auth/unified-logout.php";
      document.body.appendChild(form);
      form.submit();
    }
  });
}

function confirmDeclineOrder(orderId, receiptNumber) {
  Swal.fire({
    title: "Decline Order Confirmation",
    html: `Are you sure you want to decline pre-listed order <strong>#${receiptNumber}</strong>?<br><br>This action cannot be undone.`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Yes, Decline Order",
    cancelButtonText: "Cancel",
    background: "#fff",
    customClass: {
      popup: "rounded-3",
      confirmButton: "btn btn-danger custom-swal-button",
      cancelButton: "btn btn-secondary custom-swal-button",
    },
    buttonsStyling: false,
    reverseButtons: true,
  }).then((result) => {
    if (result.isConfirmed) {
      const form = document.createElement("form");
      form.method = "POST";
      form.action = "req/cancel-prelist-order.php";

      const input = document.createElement("input");
      input.type = "hidden";
      input.name = "id";
      input.value = orderId;
      form.appendChild(input);

      document.body.appendChild(form);
      form.submit();
    }
  });
}

function confirmDeleteProduct(productId, productName) {
  Swal.fire({
    title: "Delete Product Confirmation",
    html: `Are you sure you want to delete <strong>${productName}</strong>?<br><br>This action cannot be undone.`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Yes, Delete Product",
    cancelButtonText: "Cancel",
    background: "#fff",
    customClass: {
      popup: "rounded-3",
      confirmButton: "btn btn-danger custom-swal-button",
      cancelButton: "btn btn-secondary custom-swal-button",
    },
    buttonsStyling: false,
    reverseButtons: true,
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire({
        title: "Deleting Product...",
        text: "Please wait",
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        },
      });

      window.location.href = `req/delete-product.php?id=${productId}`;
    }
  });
}

function confirmDeleteTransaction(transactionId, supplyName, quantity, type) {
  Swal.fire({
    title: "Delete Transaction Confirmation",
    html: `Are you sure you want to delete this transaction?<br><br>
               <strong>Details:</strong><br>
               • Supply: <strong>${supplyName}</strong><br>
               • Quantity: <strong>${quantity}</strong><br>
               • Type: <strong>${type}</strong><br><br>
               This action cannot be undone and will adjust inventory accordingly.`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Yes, Delete Transaction",
    cancelButtonText: "Cancel",
    background: "#fff",
    customClass: {
      popup: "rounded-3",
      confirmButton: "btn btn-danger custom-swal-button",
      cancelButton: "btn btn-secondary custom-swal-button",
    },
    buttonsStyling: false,
    reverseButtons: true,
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire({
        title: "Deleting Transaction...",
        text: "Please wait",
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        },
      });

      window.location.href = `req/delete-transaction.php?id=${transactionId}`;
    }
  });
}

function confirmDeleteReview(reviewId, customerName, rating, reviewText) {
  let displayReviewText = reviewText;
  if (displayReviewText.length > 100) {
    displayReviewText = displayReviewText.substring(0, 100) + "...";
  }

  let stars = "";
  for (let i = 1; i <= 5; i++) {
    if (i <= rating) {
      stars += '<i class="fas fa-star text-warning me-1"></i>';
    } else {
      stars += '<i class="far fa-star text-warning me-1"></i>';
    }
  }

  Swal.fire({
    title: "Delete Review Confirmation",
    html: `Are you sure you want to permanently delete this review?<br><br>
               <strong>Review Details:</strong><br>
               <div class="text-start">
                 <strong>Customer:</strong> ${customerName}<br>
                 <strong>Rating:</strong> ${stars}<br>
                 <strong>Review:</strong> "${displayReviewText}"
               </div><br>
               This action cannot be undone.`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Yes, Delete Review",
    cancelButtonText: "Cancel",
    background: "#fff",
    customClass: {
      popup: "rounded-3",
      confirmButton: "btn btn-danger custom-swal-button",
      cancelButton: "btn btn-secondary custom-swal-button",
    },
    buttonsStyling: false,
    reverseButtons: true,
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire({
        title: "Deleting Review...",
        text: "Please wait",
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        },
      });

      const form = document.createElement("form");
      form.method = "POST";
      form.action = "req/delete-review.php";

      const reviewIdInput = document.createElement("input");
      reviewIdInput.type = "hidden";
      reviewIdInput.name = "review_id";
      reviewIdInput.value = reviewId;
      form.appendChild(reviewIdInput);

      document.body.appendChild(form);
      form.submit();
    }
  });
}

function confirmDeleteCustomer(userId, userName, userEmail) {
  Swal.fire({
    title: "Delete Customer Confirmation",
    html: `Are you sure you want to permanently delete this customer?<br><br>
               <strong>Customer Details:</strong><br>
               <div class="text-start">
                 <strong>Name:</strong> ${userName}<br>
                 <strong>Email:</strong> ${userEmail}
               </div><br>
               This action cannot be undone.`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Yes, Delete Customer",
    cancelButtonText: "Cancel",
    background: "#fff",
    customClass: {
      popup: "rounded-3",
      confirmButton: "btn btn-danger custom-swal-button",
      cancelButton: "btn btn-secondary custom-swal-button",
    },
    buttonsStyling: false,
    reverseButtons: true,
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire({
        title: "Deleting Customer...",
        text: "Please wait",
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        },
      });

      const form = document.createElement("form");
      form.method = "POST";
      form.action = "req/delete-user.php";

      const userIdInput = document.createElement("input");
      userIdInput.type = "hidden";
      userIdInput.name = "id";
      userIdInput.value = userId;
      form.appendChild(userIdInput);

      document.body.appendChild(form);
      form.submit();
    }
  });
}

function confirmArchiveEmployee(employeeId, employeeName, employeeRole, action) {
  const isArchive = action === "archive";
  const actionText = isArchive ? "archive" : "restore";
  const actionTextCapitalized = isArchive ? "Archive" : "Restore";

  Swal.fire({
    title: `${actionTextCapitalized} Employee Confirmation`,
    html: `Are you sure you want to ${actionText} this employee?<br><br>
               <strong>Employee Details:</strong><br>
               <div class="text-start">
                 <strong>Name:</strong> ${employeeName}<br>
                 <strong>Role:</strong> ${employeeRole}
               </div><br>
               ${
                 isArchive
                   ? "The employee will be archived and won't be able to log in."
                   : "The employee will be restored and will be able to log in again."
               }`,
    icon: isArchive ? "warning" : "info",
    showCancelButton: true,
    confirmButtonText: `Yes, ${actionTextCapitalized}`,
    cancelButtonText: "Cancel",
    background: "#fff",
    customClass: {
      popup: "rounded-3",
      confirmButton: isArchive
        ? "btn btn-warning custom-swal-button"
        : "btn btn-success custom-swal-button",
      cancelButton: "btn btn-secondary custom-swal-button",
    },
    buttonsStyling: false,
    reverseButtons: true,
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire({
        title: `${actionTextCapitalized}ing Employee...`,
        text: "Please wait",
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        },
      });

      const form = document.createElement("form");
      form.method = "POST";
      form.action = "req/archive-employee.php";

      const idInput = document.createElement("input");
      idInput.type = "hidden";
      idInput.name = "id";
      idInput.value = employeeId;
      form.appendChild(idInput);

      const roleInput = document.createElement("input");
      roleInput.type = "hidden";
      roleInput.name = "role";
      roleInput.value = employeeRole;
      form.appendChild(roleInput);

      const actionInput = document.createElement("input");
      actionInput.type = "hidden";
      actionInput.name = "action";
      actionInput.value = action;
      form.appendChild(actionInput);

      document.body.appendChild(form);
      form.submit();
    }
  });
}

function confirmDeleteEmployee(employeeId, employeeName, employeeRole) {
  Swal.fire({
    title: "Delete Employee Confirmation",
    html: `Are you sure you want to permanently delete this employee?<br><br>
               <strong>Employee Details:</strong><br>
               <div class="text-start">
                 <strong>Name:</strong> ${employeeName}<br>
                 <strong>Role:</strong> ${employeeRole}
               </div><br>
               <span class="text-danger"><strong>⚠️ This action cannot be undone!</strong></span>`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Yes, Delete Employee",
    cancelButtonText: "Cancel",
    background: "#fff",
    customClass: {
      popup: "rounded-3",
      confirmButton: "btn btn-danger custom-swal-button",
      cancelButton: "btn btn-secondary custom-swal-button",
    },
    buttonsStyling: false,
    reverseButtons: true,
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire({
        title: "Deleting Employee...",
        text: "Please wait",
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        },
      });

      const form = document.createElement("form");
      form.method = "POST";
      form.action = "req/delete-employee.php";

      const idInput = document.createElement("input");
      idInput.type = "hidden";
      idInput.name = "id";
      idInput.value = employeeId;
      form.appendChild(idInput);

      const typeInput = document.createElement("input");
      typeInput.type = "hidden";
      typeInput.name = "type";
      typeInput.value = employeeRole.toLowerCase();
      form.appendChild(typeInput);

      document.body.appendChild(form);
      form.submit();
    }
  });
}

function confirmCancelPrelistOrder(orderId, receiptNumber) {
  Swal.fire({
    title: "Cancel Order Confirmation",
    html: `Are you sure you want to cancel pre-listed order <strong>#${receiptNumber}</strong>?<br><br>This action cannot be undone.`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Yes, Cancel Order",
    cancelButtonText: "Cancel",
    background: "#fff",
    customClass: {
      popup: "rounded-3",
      confirmButton: "btn btn-danger custom-swal-button",
      cancelButton: "btn btn-secondary custom-swal-button",
    },
    buttonsStyling: false,
    reverseButtons: true,
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire({
        title: "Canceling Order...",
        text: "Please wait",
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        },
      });

      const form = document.createElement("form");
      form.method = "POST";
      form.action = "req/cancel-prelist-order.php";

      const input = document.createElement("input");
      input.type = "hidden";
      input.name = "id";
      input.value = orderId;
      form.appendChild(input);

      document.body.appendChild(form);
      form.submit();
    }
  });
}

function showCancellationDetails(orderId, reason, notes, cancelledBy) {
  let notesDisplay = notes || "No additional notes provided.";

  Swal.fire({
    title: "Cancellation Details",
    html: `
            <div class="text-start">
                <p><strong>Queue number:</strong> #${orderId}</p>
                <p><strong>Reason:</strong> ${reason}</p>
                <p><strong>Notes:</strong> ${notesDisplay}</p>
                <p><strong>Cancelled By:</strong> ${cancelledBy}</p>
            </div>
        `,
    icon: "info",
    confirmButtonText: "Close",
    background: "#fff",
    customClass: {
      popup: "rounded-3",
      confirmButton: "btn btn-secondary custom-swal-button",
    },
    buttonsStyling: false,
  });
}

function confirmStatusChange(orderId, currentStatus, newStatus, queueNumber) {
  Swal.fire({
    title: "Change Status Confirmation",
    html: `Are you sure you want to change order <strong>#${queueNumber}</strong> status from <strong>${currentStatus}</strong> to <strong>${newStatus}</strong>?`,
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Yes, Change Status",
    cancelButtonText: "Cancel",
    background: "#fff",
    customClass: {
      popup: "rounded-3",
      confirmButton: "btn btn-primary custom-swal-button",
      cancelButton: "btn btn-secondary custom-swal-button",
    },
    buttonsStyling: false,
    reverseButtons: true,
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire({
        title: "Updating Status...",
        text: "Please wait",
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        },
      });

      const form = document.createElement("form");
      form.method = "POST";
      form.action = "req/update-status.php";

      const orderIdInput = document.createElement("input");
      orderIdInput.type = "hidden";
      orderIdInput.name = "order_id";
      orderIdInput.value = orderId;
      form.appendChild(orderIdInput);

      const statusInput = document.createElement("input");
      statusInput.type = "hidden";
      statusInput.name = "new_status";
      statusInput.value = newStatus;
      form.appendChild(statusInput);

      const filterInput = document.createElement("input");
      filterInput.type = "hidden";
      filterInput.name = "current_filter";
      filterInput.value = "<?php echo htmlspecialchars($statusFilter); ?>";
      form.appendChild(filterInput);

      document.body.appendChild(form);
      form.submit();
    }
  });
}

const style = document.createElement("style");
style.textContent = `
.custom-swal-button {
    border-radius: 0.375rem !important;
    padding: 0.5rem 1.5rem !important;
    font-size: 0.875rem !important;
    font-weight: 500 !important;
    border: 1px solid transparent !important;
    transition: all 0.15s ease-in-out !important;
    min-width: 100px !important;
}

.swal2-actions {
    gap: 0.75rem !important;
    margin-top: 1.5rem !important;
}

.btn-primary.custom-swal-button {
    background-color: #644499 !important;
    border-color: #644499 !important;
}

.btn-danger.custom-swal-button {
    background-color: #d33 !important;
    border-color: #d33 !important;
}

.btn-warning.custom-swal-button {
    background-color: #f39c12 !important;
    border-color: #f39c12 !important;
    color: white !important;
}

.btn-success.custom-swal-button {
    background-color: #28a745 !important;
    border-color: #28a745 !important;
}

.btn-secondary.custom-swal-button {
    background-color: #6c757d !important;
    border-color: #6c757d !important;
    color: white !important;
}

.custom-swal-button:hover {
    opacity: 0.9 !important;
    transform: translateY(-1px) !important;
}
`;
document.head.appendChild(style);