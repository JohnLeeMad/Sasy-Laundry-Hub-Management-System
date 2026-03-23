document.addEventListener("DOMContentLoaded", function () {
  // DOM Elements
  const editButtons = document.querySelectorAll(".edit-laundry-btn");
  const modal = document.getElementById("editLaundryModal");
  const updateOrderButton = modal.querySelector('button[type="submit"]');
  const accommodatedByElement = document.getElementById("edit_accommodated_by");
  const queueNumberBadge = document.getElementById("edit_queue_number_badge");
  const whitesBadge = document.getElementById("edit_whites_badge");
  const storeChangeContainer = document.getElementById(
    "store_change_container",
  );

  // Form elements
  const formElements = {
    laundryId: document.getElementById("edit_laundry_id"),
    customerName: document.getElementById("edit_customer_name"),
    customerId: document.getElementById("edit_customer_id"),
    customerPhone: document.getElementById("edit_customer_phone"),
    status: document.getElementById("edit_status"),
    paymentStatus: document.getElementById("edit_payment_status"),
    amountTendered: document.getElementById("edit_amount_tendered"),
    totalPrice: document.getElementById("edit_total_price"),
    change: document.getElementById("edit_change"),
    remarks: document.getElementById("edit_remarks"),
    roundsOfWash: document.getElementById("edit_rounds_of_wash"),
    scoopsOfDetergent: document.getElementById("edit_scoops_of_detergent"),
    dryerPreference: document.getElementById("edit_dryer_preference"),
    foldingService: document.getElementById("edit_folding_service"),
    separateWhites: document.getElementById("edit_separate_whites"),
    isWhitesOrder: document.getElementById("edit_is_whites_order"),
    fabconCups: document.getElementById("edit_fabcon_cups"),
    bleachCups: document.getElementById("edit_bleach_cups"),
    storeChangeAsBalance: document.getElementById("store_change_as_balance"),
    changeStoredAsBalance: document.getElementById(
      "edit_change_stored_as_balance",
    ),
  };

  // Product selection elements
  const formInputs = {
    detergentProduct: document.getElementById("edit_detergent_product"),
    fabconProduct: document.getElementById("edit_fabcon_product"),
    bleachProduct: document.getElementById("edit_bleach_product"),
    scoopsOfDetergent: document.getElementById("edit_scoops_of_detergent"),
    fabconCups: document.getElementById("edit_fabcon_cups"),
    bleachCups: document.getElementById("edit_bleach_cups"),
  };

  // Stock info elements
  const stockInfoElements = {
    detergent: document.getElementById("edit_detergent_stock_info"),
    fabcon: document.getElementById("edit_fabcon_stock_info"),
    bleach: document.getElementById("edit_bleach_stock_info"),
  };

  // Initialize modal
  function initModal() {
    setupEditButtons();
    setupEventListeners();
    setupProductSelectionHandlers();
  }

  // Set up edit buttons
  function setupEditButtons() {
    editButtons.forEach((button) => {
      button.addEventListener("click", function () {
        const data = {
          id: this.getAttribute("data-id") || "",
          customerName: this.getAttribute("data-customer-name") || "N/A",
          customerId: this.getAttribute("data-customer-id") || "N/A",
          customerType: this.getAttribute("data-customer-type") || "Registered",
          customerPhone: this.getAttribute("data-customer-phone") || "N/A",
          status: this.getAttribute("data-status") || "Pending",
          totalPrice: this.getAttribute("data-total-price") || "0.00",
          adjustedTotalPrice:
            this.getAttribute("data-adjusted-total-price") || "0.00",
          deductedBalance: this.getAttribute("data-deducted-balance") || "0.00",
          paymentStatus: this.getAttribute("data-payment-status") || "Unpaid",
          changeStoredAsBalance:
            this.getAttribute("data-change-stored-as-balance") || "0",
          remarks: this.getAttribute("data-remarks") || "",
          roundsOfWash: this.getAttribute("data-rounds-of-wash") || "1",
          scoopsOfDetergent:
            this.getAttribute("data-scoops-of-detergent") || "0",
          dryerPreference: this.getAttribute("data-dryer-preference") || "0",
          foldingService: this.getAttribute("data-folding-service") || "0",
          separateWhites: this.getAttribute("data-separate-whites") || "0",
          isWhitesOrder: this.getAttribute("data-is-whites-order") || "0",
          fabconCups: this.getAttribute("data-fabcon-cups") || "0",
          bleachCups: this.getAttribute("data-bleach-cups") || "0",
          detergent_product_id:
            this.getAttribute("data-detergent-product-id") || "",
          fabcon_product_id: this.getAttribute("data-fabcon-product-id") || "",
          bleach_product_id: this.getAttribute("data-bleach-product-id") || "",
          amountTendered: this.getAttribute("data-amount-tendered") || "0.00",
          change: this.getAttribute("data-change") || "0.00",
          accommodatedBy: this.getAttribute("data-accommodated-by") || "N/A",
          queueNumber: this.getAttribute("data-queue-number") || "N/A",
        };

        // Clean up NULL values
        if (
          data.detergent_product_id === "NULL" ||
          data.detergent_product_id === "null"
        ) {
          data.detergent_product_id = "";
        }
        if (
          data.fabcon_product_id === "NULL" ||
          data.fabcon_product_id === "null"
        ) {
          data.fabcon_product_id = "";
        }
        if (
          data.bleach_product_id === "NULL" ||
          data.bleach_product_id === "null"
        ) {
          data.bleach_product_id = "";
        }

        populateForm(data);
        accommodatedByElement.textContent = data.accommodatedBy;
        queueNumberBadge.textContent = `#${data.queueNumber}`;
        
        // Show/hide whites order badge
        if (data.isWhitesOrder === "1" || data.isWhitesOrder === 1) {
          whitesBadge.style.display = "inline-block";
        } else {
          whitesBadge.style.display = "none";
        }
        
        handlePaymentStatus(data.paymentStatus);
        setupStatusChangeValidation();
        toggleStoreChangeVisibility(data.status);
      });
    });
  }

  // Setup product selection handlers
  function setupProductSelectionHandlers() {
    // Detergent product selection
    formInputs.detergentProduct.addEventListener("change", function () {
      handleProductSelection(
        "detergent",
        this,
        formInputs.scoopsOfDetergent,
        stockInfoElements.detergent,
        10,
      );
    });

    // Fabric conditioner product selection
    formInputs.fabconProduct.addEventListener("change", function () {
      handleProductSelection(
        "fabcon",
        this,
        formInputs.fabconCups,
        stockInfoElements.fabcon,
        10,
      );
    });

    // Bleach product selection
    formInputs.bleachProduct.addEventListener("change", function () {
      handleProductSelection(
        "bleach",
        this,
        formInputs.bleachCups,
        stockInfoElements.bleach,
        5,
      );
    });
  }

  // Handle product selection changes
  function handleProductSelection(
    productType,
    selectElement,
    quantityInput,
    stockInfoElement,
    maxQuantity,
  ) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];

    if (selectedOption.value) {
      const stock = parseInt(selectedOption.dataset.stock) || 0;
      const unitPrice = parseFloat(selectedOption.dataset.price) || 0;

      // Enable quantity input only if product is selected and has stock
      quantityInput.disabled = stock <= 0;
      quantityInput.min = 1; // Set minimum to 1 for all products
      quantityInput.max = Math.min(maxQuantity, stock);

      // Set to 1 if the current value is 0
      if (parseInt(quantityInput.value) === 0) {
        quantityInput.value = 1;
      }

      // Update stock information
      updateStockInfo(stockInfoElement, stock, unitPrice, productType);

      // Set data attributes for price calculation
      quantityInput.dataset.price = unitPrice;
      quantityInput.dataset.stock = stock;
    } else {
      // Clear selection and disable quantity input
      quantityInput.disabled = true;
      quantityInput.value = 0;
      quantityInput.dataset.price = 0;
      quantityInput.dataset.stock = 0;

      // Clear stock information
      stockInfoElement.innerHTML = "";
    }

    calculatePrice();
  }

  // Update stock information display
  function updateStockInfo(element, stock, unitPrice, productType) {
    let message = "";
    let className = "";

    if (stock <= 0) {
      message = "Out of stock";
      className = "text-danger";
    } else if (stock <= 10) {
      message = `Low unit: ${stock} units available`;
      className = "text-warning";
    } else {
      message = `${stock} units available`;
      className = "text-success";
    }

    element.innerHTML = `<span class="${className}">${message}</span>`;
  }

  // Updated toggleFieldsDisabled function
  function toggleFieldsDisabled(status) {
    const restrictedStatuses = ["Claimed", "Ongoing", "Ready for Pickup"];
    const isRestrictedStatus = restrictedStatuses.includes(status);

    const fieldsToDisable = [
      formElements.roundsOfWash,
      formElements.dryerPreference,
      formElements.foldingService,
      formElements.remarks,
      formInputs.detergentProduct,
      formInputs.fabconProduct,
      formInputs.bleachProduct,
      formInputs.scoopsOfDetergent,
      formInputs.fabconCups,
      formInputs.bleachCups,
    ];

    fieldsToDisable.forEach((field) => {
      if (field) {
        // Change from disabled to readonly
        field.readOnly = isRestrictedStatus;
        // If it's a select, also disable pointer events for readonly effect
        if (field.tagName === "SELECT") {
          field.style.pointerEvents = isRestrictedStatus ? "none" : "";
          field.style.backgroundColor = isRestrictedStatus ? "#e9ecef" : "";
        }
      }
    });

    // Handle amount tendered field separately
    const isPaid = formElements.paymentStatus.value === "Paid";

    if (status === "Pending") {
      // For Pending status, amount tendered is always editable regardless of payment status
      formElements.amountTendered.readOnly = false;
      formElements.amountTendered.disabled = false;
    } else if (status === "Ongoing" || status === "Ready for Pickup") {
      // For Ongoing and Ready for Pickup, readonly/disabled only if paid
      formElements.amountTendered.readOnly = isPaid;
      formElements.amountTendered.disabled = false;
    } else if (status === "Claimed") {
      // For Claimed status, readonly/disabled only if paid
      formElements.amountTendered.readOnly = isPaid;
      formElements.amountTendered.disabled = false;
    }

    // Handle store change as balance checkbox
    if (
      status === "Ready for Pickup" &&
      parseFloat(formElements.change.value) > 0
    ) {
      formElements.storeChangeAsBalance.disabled = false;
      formElements.storeChangeAsBalance.readOnly = false;
    } else {
      formElements.storeChangeAsBalance.disabled = true;
    }

    // Toggle store change visibility
    toggleStoreChangeVisibility(status);
  }

  // Toggle visibility of store change as balance checkbox
  function toggleStoreChangeVisibility(status) {
    const hasChange = parseFloat(formElements.change.value) > 0;
    const isReadyForPickup = status === "Ready for Pickup";
    const isWalkIn =
      document.getElementById("edit_customer_type").value === "walk-in";
    const alreadyStored = formElements.changeStoredAsBalance.value === "1";

    storeChangeContainer.style.display =
      isReadyForPickup && hasChange && !isWalkIn ? "block" : "none";

    // Remove any existing tooltip messages first
    const existingTooltip = storeChangeContainer.querySelector(
      ".text-muted.d-block",
    );
    if (existingTooltip) {
      existingTooltip.remove();
    }

    if (alreadyStored) {
      formElements.storeChangeAsBalance.checked = true;
      formElements.storeChangeAsBalance.disabled = true;
      // Add a tooltip or message explaining why it's disabled
      const tooltip = document.createElement("small");
      tooltip.className = "text-muted d-block";
      tooltip.textContent = "Change already stored as balance";
      storeChangeContainer.appendChild(tooltip);
    } else {
      formElements.storeChangeAsBalance.disabled =
        !isReadyForPickup || !hasChange || isWalkIn;
    }
  }

  // Populate form with data
  function populateForm(data) {
    // Basic form fields
    formElements.laundryId.value = data.id;
    formElements.customerName.value = data.customerName;
    formElements.customerPhone.value = data.customerPhone;
    formElements.status.value = data.status;
    formElements.paymentStatus.value = data.paymentStatus;
    formElements.remarks.value = data.remarks;

    formElements.roundsOfWash.value = data.roundsOfWash;
    formElements.dryerPreference.value = data.dryerPreference;
    formElements.foldingService.checked = data.foldingService === "1";
    formElements.separateWhites.value = data.separateWhites || "0";
    formElements.isWhitesOrder.value = data.isWhitesOrder || "0";

    formElements.amountTendered.value = data.amountTendered;
    formElements.change.value = data.change;
    document.getElementById("edit_deducted_balance").value =
      data.deductedBalance;
    document.getElementById("edit_customer_type").value = data.customerType;
    document.getElementById("edit_customer_id").value = data.customerId || "";
    document.getElementById("edit_change_stored_as_balance").value =
      data.changeStoredAsBalance || "0";

    // Convert string values to numbers for proper comparison
    const scoopsOfDetergent = parseInt(data.scoopsOfDetergent) || 0;
    const fabconCups = parseInt(data.fabconCups) || 0;
    const bleachCups = parseInt(data.bleachCups) || 0;

    console.log("Populating form with data:", {
      detergent_product_id: data.detergent_product_id,
      scoopsOfDetergent: scoopsOfDetergent,
      fabcon_product_id: data.fabcon_product_id,
      fabconCups: fabconCups,
      bleach_product_id: data.bleach_product_id,
      bleachCups: bleachCups,
    });

    // Clear all product selections and stock info first
    formInputs.detergentProduct.value = "";
    formInputs.fabconProduct.value = "";
    formInputs.bleachProduct.value = "";
    stockInfoElements.detergent.innerHTML = "";
    stockInfoElements.fabcon.innerHTML = "";
    stockInfoElements.bleach.innerHTML = "";

    // Initialize quantity fields
    formInputs.scoopsOfDetergent.value = scoopsOfDetergent;
    formInputs.fabconCups.value = fabconCups;
    formInputs.bleachCups.value = bleachCups;

    // Initially disable all quantity inputs
    formInputs.scoopsOfDetergent.disabled = true;
    formInputs.fabconCups.disabled = true;
    formInputs.bleachCups.disabled = true;

    // Set product selections and trigger their handlers
    if (data.detergent_product_id && data.detergent_product_id !== "") {
      formInputs.detergentProduct.value = data.detergent_product_id;
      formInputs.detergentProduct.dispatchEvent(new Event("change"));
    }

    if (data.fabcon_product_id && data.fabcon_product_id !== "") {
      formInputs.fabconProduct.value = data.fabcon_product_id;
      formInputs.fabconProduct.dispatchEvent(new Event("change"));
    }

    if (data.bleach_product_id && data.bleach_product_id !== "") {
      formInputs.bleachProduct.value = data.bleach_product_id;
      formInputs.bleachProduct.dispatchEvent(new Event("change"));
    }

    formElements.storeChangeAsBalance.checked =
      data.changeStoredAsBalance === "1";
    formElements.changeStoredAsBalance.value =
      data.changeStoredAsBalance || "0";

    // Set initial total price with deducted balance applied
    formElements.totalPrice.value = data.adjustedTotalPrice;
    formElements.totalPrice.dataset.deductedBalance = data.deductedBalance;
    calculateChange(parseFloat(data.adjustedTotalPrice));

    // Handle status-based field disabling
    toggleFieldsDisabled(data.status);

    // Handle payment status
    handlePaymentStatus(data.paymentStatus);
  }

  // Updated handlePaymentStatus function
  function handlePaymentStatus(paymentStatus) {
    const isPaid = paymentStatus === "Paid";
    const currentStatus = formElements.status.value;

    updateOrderButton.disabled =
      !isPaid &&
      parseFloat(formElements.amountTendered.value) <
        parseFloat(formElements.totalPrice.value) * 0.5;
    validateStatusOptions();
  }

  // Set up status change validation
  function setupStatusChangeValidation() {
    formElements.status.addEventListener("change", function () {
      toggleFieldsDisabled(this.value);
      validateStatusOptions();
    });
    formElements.amountTendered.addEventListener(
      "input",
      validateStatusOptions,
    );
  }

  // Validate status options based on payment
  function validateStatusOptions() {
    const totalPrice = parseFloat(formElements.totalPrice.value) || 0;
    const amountTendered = parseFloat(formElements.amountTendered.value) || 0;
    const selectedStatus = formElements.status.value;
    const isPaid = amountTendered >= totalPrice;
    const isClaimed = selectedStatus === "Claimed";

    removeStatusWarning();

    if (isClaimed && !isPaid) {
      formElements.status.classList.add("border-danger");

      const warningDiv = document.createElement("div");
      warningDiv.className = "text-danger small mt-1 status-warning";
      warningDiv.textContent =
        "Full payment required before marking as Claimed";
      formElements.status.parentNode.appendChild(warningDiv);

      updateOrderButton.disabled = true;
    } else {
      formElements.status.classList.remove("border-danger");
      updateOrderButton.disabled = isClaimed
        ? false
        : amountTendered < totalPrice * 0.5;
    }
  }

  // Remove status warning message
  function removeStatusWarning() {
    const existingWarning = document.querySelector(".status-warning");
    if (existingWarning) {
      existingWarning.remove();
    }
  }

  // Calculate total price dynamically
  function calculatePrice() {
    const values = getInputValues();
    const originalTotal = calculateTotalPrice(values);
    const deductedBalance =
      parseFloat(formElements.totalPrice.dataset.deductedBalance) || 0;
    const adjustedTotal = Math.max(0, originalTotal - deductedBalance);

    formElements.totalPrice.value = adjustedTotal.toFixed(2);
    formElements.totalPrice.dataset.deductedBalance = deductedBalance;
    calculateChange(adjustedTotal);
    validateStatusOptions();
  }

  // Get current input values
  function getInputValues() {
    return {
      roundsOfWash: parseInt(formElements.roundsOfWash.value) || 1,
      scoopsOfDetergent: parseInt(formInputs.scoopsOfDetergent.value) || 0,
      detergentUnitPrice:
        parseFloat(formInputs.scoopsOfDetergent.dataset.price) || 0,
      dryerPreference: parseInt(formElements.dryerPreference.value) || 0,
      foldingService: formElements.foldingService.checked,
      fabconCups: parseInt(formInputs.fabconCups.value) || 0,
      fabconUnitPrice: parseFloat(formInputs.fabconCups.dataset.price) || 0,
      bleachCups: parseInt(formInputs.bleachCups.value) || 0,
      bleachUnitPrice: parseFloat(formInputs.bleachCups.dataset.price) || 0,
      amountTendered: parseFloat(formElements.amountTendered.value) || 0,
    };
  }

  // Calculate total price based on input values
  function calculateTotalPrice(values) {
    if (formElements.status.value === "Claimed") {
      return parseFloat(formElements.totalPrice.value) || 0;
    }

    return (
      values.roundsOfWash * PRICES.washFeePerRound +
      values.dryerPreference * PRICES.dryerFeePerRound +
      values.scoopsOfDetergent * values.detergentUnitPrice +
      values.fabconCups * values.fabconUnitPrice +
      values.bleachCups * values.bleachUnitPrice +
      (values.foldingService ? PRICES.foldingFee : 0)
    );
  }

  // Calculate change and validate payment
  function calculateChange(totalPrice) {
    const amountTendered = parseFloat(formElements.amountTendered.value) || 0;
    const change = Math.max(0, amountTendered - totalPrice);

    formElements.change.value = change.toFixed(2);
    const minimumPayment = totalPrice * 0.5;

    if (amountTendered < minimumPayment) {
      removeFeedbackMessage(formElements.amountTendered, "remaining-payment");
      addFeedbackMessage(
        formElements.amountTendered,
        `Partial payment required. Minimum: ₱${minimumPayment.toFixed(2)}`,
        true,
        "partial-payment",
      );
      updateOrderButton.disabled = true;
    } else if (amountTendered < totalPrice) {
      const remainingPayment = (totalPrice - amountTendered).toFixed(2);
      removeFeedbackMessage(formElements.amountTendered, "partial-payment");
      addFeedbackMessage(
        formElements.amountTendered,
        `Remaining payment: ₱${remainingPayment}`,
        true,
        "remaining-payment",
      );
      updateOrderButton.disabled = formElements.status.value === "Claimed";
    } else {
      removeFeedbackMessage(formElements.amountTendered, "partial-payment");
      removeFeedbackMessage(formElements.amountTendered, "remaining-payment");
      updateOrderButton.disabled = false;
    }

    formElements.paymentStatus.value =
      amountTendered >= totalPrice ? "Paid" : "Unpaid";
    validateStatusOptions();

    // Update store change as balance checkbox visibility and state
    toggleStoreChangeVisibility(formElements.status.value);
  }

  // Validate stock availability
  function validateStock() {
    let hasInsufficientStock = false;
    const currentStatus = formElements.status.value;
    const isRestrictedStatus = [
      "Ongoing",
      "Ready for Pickup",
      "Claimed",
    ].includes(currentStatus);

    // For restricted statuses, skip stock validation entirely
    // because the stock was already deducted when the order was created/moved to these statuses
    if (isRestrictedStatus) {
      // Clear any existing error messages
      removeFeedbackMessage(formInputs.scoopsOfDetergent);
      removeFeedbackMessage(formInputs.fabconCups);
      removeFeedbackMessage(formInputs.bleachCups);
      return true; // Skip validation for restricted statuses
    }

    // Check detergent stock (only for Pending status)
    if (
      formInputs.detergentProduct.value &&
      parseInt(formInputs.scoopsOfDetergent.value) > 0
    ) {
      const detergentStock =
        parseInt(formInputs.scoopsOfDetergent.dataset.stock) || 0;
      const detergentNeeded = parseInt(formInputs.scoopsOfDetergent.value) || 0;

      if (detergentNeeded > detergentStock) {
        addFeedbackMessage(
          formInputs.scoopsOfDetergent,
          `Only ${detergentStock} scoops available`,
        );
        hasInsufficientStock = true;
      } else {
        removeFeedbackMessage(formInputs.scoopsOfDetergent);
      }
    }

    // Check fabric conditioner stock (only for Pending status)
    if (
      formInputs.fabconProduct.value &&
      parseInt(formInputs.fabconCups.value) > 0
    ) {
      const fabconStock = parseInt(formInputs.fabconCups.dataset.stock) || 0;
      const fabconNeeded = parseInt(formInputs.fabconCups.value) || 0;

      if (fabconNeeded > fabconStock) {
        addFeedbackMessage(
          formInputs.fabconCups,
          `Only ${fabconStock} cups available`,
        );
        hasInsufficientStock = true;
      } else {
        removeFeedbackMessage(formInputs.fabconCups);
      }
    }

    // Check bleach stock (only for Pending status)
    if (
      formInputs.bleachProduct.value &&
      parseInt(formInputs.bleachCups.value) > 0
    ) {
      const bleachStock = parseInt(formInputs.bleachCups.dataset.stock) || 0;
      const bleachNeeded = parseInt(formInputs.bleachCups.value) || 0;

      if (bleachNeeded > bleachStock) {
        addFeedbackMessage(
          formInputs.bleachCups,
          `Only ${bleachStock} cups available`,
        );
        hasInsufficientStock = true;
      } else {
        removeFeedbackMessage(formInputs.bleachCups);
      }
    }

    return !hasInsufficientStock;
  }

  // Add feedback message to input
  function addFeedbackMessage(
    element,
    message,
    isWarning = false,
    customClass = "",
  ) {
    removeFeedbackMessage(element, customClass);

    const feedbackDiv = document.createElement("div");
    feedbackDiv.className = `${
      isWarning ? "text-warning" : "text-danger"
    } small mt-1 ${customClass}`;
    feedbackDiv.textContent = message;
    feedbackDiv.dataset.feedback = customClass || "true";

    element.parentNode.appendChild(feedbackDiv);
  }

  // Remove feedback message from input
  function removeFeedbackMessage(element, customClass = "") {
    const feedbackSelector = customClass
      ? `.${customClass}`
      : '[data-feedback="true"]';
    const existingFeedback = element.parentNode.querySelector(feedbackSelector);
    if (existingFeedback) {
      existingFeedback.remove();
    }
  }

  // Set up event listeners
  function setupEventListeners() {
    const priceInputs = [
      formElements.roundsOfWash,
      formInputs.scoopsOfDetergent,
      formElements.dryerPreference,
      formElements.foldingService,
      formInputs.fabconCups,
      formInputs.bleachCups,
    ];

    priceInputs.forEach((input) => {
      if (input) {
        const eventType = input.type === "checkbox" ? "change" : "input";
        input.addEventListener(eventType, calculatePrice);
      }
    });

    formElements.amountTendered.addEventListener("input", () => {
      calculateChange(parseFloat(formElements.totalPrice.value) || 0);
    });

    formElements.storeChangeAsBalance.addEventListener("change", function () {
      formElements.changeStoredAsBalance.value = this.checked ? "1" : "0";
    });

    // Quantity input validation
    const quantityInputs = [
      formInputs.scoopsOfDetergent,
      formInputs.fabconCups,
      formInputs.bleachCups,
    ];

    quantityInputs.forEach((input) => {
      if (input) {
        input.addEventListener("input", function () {
          // If quantity is set to 0, clear the product selection
          if (parseInt(this.value) === 0) {
            const productSelect = this.id.includes("scoops")
              ? formInputs.detergentProduct
              : this.id.includes("fabcon")
                ? formInputs.fabconProduct
                : formInputs.bleachProduct;
            productSelect.value = "";
            this.disabled = true;
          }
          validateStock();
          calculatePrice();
        });

        input.addEventListener("blur", function () {
          // Ensure value is within bounds
          const min = parseInt(this.min) || 0;
          const max = parseInt(this.max) || 0;
          let value = parseInt(this.value) || 0;

          if (value < min) {
            this.value = min;
          } else if (value > max) {
            this.value = max;
          }

          validateStock();
          calculatePrice();
        });
      }
    });

    // Form submission validation
    const form = document
      .getElementById("editLaundryModal")
      .querySelector("form");
    if (form) {
      form.addEventListener("submit", function (e) {
        // Clear product selections if quantity is 0
        if (parseInt(formInputs.scoopsOfDetergent.value) === 0) {
          formInputs.detergentProduct.value = "";
        }
        if (parseInt(formInputs.fabconCups.value) === 0) {
          formInputs.fabconProduct.value = "";
        }
        if (parseInt(formInputs.bleachCups.value) === 0) {
          formInputs.bleachProduct.value = "";
        }

        if (!validateStock()) {
          e.preventDefault();
          alert(
            "Please check product quantities. Some items exceed available stock.",
          );
          return false;
        }

        const totalPrice = parseFloat(formElements.totalPrice.value) || 0;
        const amountTendered =
          parseFloat(formElements.amountTendered.value) || 0;
        const minimumPayment = totalPrice * 0.5;
        const storeChangeValue = formElements.storeChangeAsBalance.checked
          ? "1"
          : "0";
        document.getElementById("edit_change_stored_as_balance").value =
          storeChangeValue;

        if (amountTendered < minimumPayment) {
          e.preventDefault();
          alert(
            `Insufficient payment. Minimum required: ₱${minimumPayment.toFixed(
              2,
            )}`,
          );
          return false;
        }
      });
    }
  }

  // Initialize the modal
  initModal();
});