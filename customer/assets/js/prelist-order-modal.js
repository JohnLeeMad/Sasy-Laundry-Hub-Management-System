document.addEventListener("DOMContentLoaded", function () {
  // DOM Elements
  const ordersContainer = document.getElementById("ordersContainer");
  const addOrderButton = document.getElementById("addOrderButton");
  const orderTemplate = document.getElementById("orderTemplate");
  const prelistAllOrdersButton = document.getElementById(
    "prelistAllOrdersButton"
  );
  const ordersDataField = document.getElementById("orders_data");
  const globalRemarksField = document.getElementById("global_remarks");
  const grandTotalField = document.getElementById("grand_total");
  const useBalanceCheckbox = document.getElementById("use_balance");
  const customerBalanceSpan = document.getElementById("customer_balance");

  let orderCounter = 0;
  let orders = [];
  let currentOrderIndex = 0; // Track which order is currently being viewed/edited

  // Configure maximum number of orders allowed
  const MAX_ORDERS = 10; // You can change this value as needed

  // Initialize modal
  function initModal() {
    window.PRICES = window.PRICES || {
      washFeePerRound: 70,
      dryerFeePerRound: 70,
      foldingFee: 0,
    };

    setupEventListeners();
    addFirstOrder();
  }

  // Add first order when modal opens
  function addFirstOrder() {
    addNewOrder();
  }

  // Add new order with limit check
  function addNewOrder() {
    const currentOrderCount = orders.length;

    // Check if we've reached the maximum limit
    if (currentOrderCount >= MAX_ORDERS) {
      alert(`Maximum of ${MAX_ORDERS} orders allowed per transaction.`);
      return;
    }

    // Save current order data before creating new one
    const currentOrderElement = ordersContainer.querySelector(".order-item");
    if (currentOrderElement) {
      saveCurrentOrderData(currentOrderIndex);
    }

    console.log("Adding new order, counter:", orderCounter);
    orderCounter++;
    currentOrderIndex = orderCounter - 1;
    
    // Create new order object with default or duplicated data
    const newOrderData = {
      orderNumber: orderCounter,
      data: {}
    };
    
    // If there are existing orders, duplicate the last one's data
    if (orders.length > 0) {
      newOrderData.data = JSON.parse(JSON.stringify(orders[orders.length - 1].data));
    }
    
    orders.push(newOrderData);
    
    // Display the new order
    displayOrder(currentOrderIndex);
    
    // Update order list UI
    updateOrderListUI();

    // Update add order button state
    updateAddOrderButtonState();

    // Calculate totals
    calculateGrandTotal();
  }

  // Update add order button state based on current count
  function updateAddOrderButtonState() {
    const currentOrderCount = orders.length;

    if (currentOrderCount >= MAX_ORDERS) {
      addOrderButton.disabled = true;
      addOrderButton.innerHTML = `<i class="fas fa-plus me-1"></i> Maximum Orders Reached (${MAX_ORDERS})`;
      addOrderButton.style.opacity = "0.6";
      addOrderButton.style.cursor = "not-allowed";
    } else {
      addOrderButton.disabled = false;
      addOrderButton.innerHTML = `<i class="fas fa-plus me-1"></i> Add Order (${currentOrderCount}/${MAX_ORDERS})`;
      addOrderButton.style.opacity = "1";
      addOrderButton.style.cursor = "pointer";
    }
  }

  // Create order element from template
  function createOrderElement(orderNumber) {
    const template = orderTemplate.content.cloneNode(true);
    const orderDiv = template.querySelector(".order-item");

    // Set unique ID and order number
    orderDiv.dataset.orderNumber = orderNumber;
    orderDiv.querySelector(".order-number").textContent = orderNumber;

    return orderDiv;
  }

  // Save current order data to orders array
  function saveCurrentOrderData(orderIndex) {
    const orderElement = ordersContainer.querySelector(".order-item");
    if (!orderElement || !orders[orderIndex]) return;

    const orderData = {};

    // Save basic fields
    orderData.roundsOfWash = orderElement.querySelector(".rounds-of-wash")?.value || "1";
    orderData.dryerPreference = orderElement.querySelector(".dryer-preference")?.value || "0";
    orderData.foldingService = orderElement.querySelector(".folding-service")?.checked || false;
    orderData.separateWhites = orderElement.querySelector(".separate-whites")?.checked || false;
    orderData.orderRemarks = orderElement.querySelector(".order-remarks")?.value || "";

    // Save the order total from the form
    orderData.orderTotal = parseFloat(orderElement.querySelector(".order-total")?.textContent) || 0;

    // Save product selections
    const detergentProduct = orderElement.querySelector(".detergent-product");
    const fabconProduct = orderElement.querySelector(".fabcon-product");
    const bleachProduct = orderElement.querySelector(".bleach-product");

    orderData.detergentProduct = detergentProduct?.value || "";
    orderData.detergentQuantity = orderElement.querySelector(".scoops-of-detergent")?.value || "0";
    
    // Save detergent price
    const detergentInput = orderElement.querySelector(".scoops-of-detergent");
    orderData.detergentPrice = detergentInput?.dataset.price || "0";
    
    orderData.fabconProduct = fabconProduct?.value || "";
    orderData.fabconQuantity = orderElement.querySelector(".fabcon-cups")?.value || "0";
    
    // Save fabcon price
    const fabconInput = orderElement.querySelector(".fabcon-cups");
    orderData.fabconPrice = fabconInput?.dataset.price || "0";
    
    orderData.bleachProduct = bleachProduct?.value || "";
    orderData.bleachQuantity = orderElement.querySelector(".bleach-cups")?.value || "0";
    
    // Save bleach price
    const bleachInput = orderElement.querySelector(".bleach-cups");
    orderData.bleachPrice = bleachInput?.dataset.price || "0";

    // Save clothing items
    orderData.clothingTops = orderElement.querySelector(".clothing-tops")?.value || "0";
    orderData.clothingBottoms = orderElement.querySelector(".clothing-bottoms")?.value || "0";
    orderData.clothingUndergarments = orderElement.querySelector(".clothing-undergarments")?.value || "0";
    orderData.clothingDelicates = orderElement.querySelector(".clothing-delicates")?.value || "0";
    orderData.clothingLinens = orderElement.querySelector(".clothing-linens")?.value || "0";
    orderData.clothingCurtains = orderElement.querySelector(".clothing-curtains-drapes")?.value || "0";
    orderData.clothingBlankets = orderElement.querySelector(".clothing-blankets-comforters")?.value || "0";
    orderData.clothingOthers = orderElement.querySelector(".clothing-others")?.value || "0";

    // Save clothing section visibility
    const clothingSection = orderElement.querySelector(".clothing-items-section");
    orderData.clothingSectionVisible = clothingSection && !clothingSection.classList.contains("d-none");

    orders[orderIndex].data = orderData;
  }

  // Display order at given index
  function displayOrder(orderIndex) {
    if (!orders[orderIndex]) return;

    // Clear current container
    ordersContainer.innerHTML = "";

    // Create new order element
    const orderElement = createOrderElement(orders[orderIndex].orderNumber);
    ordersContainer.appendChild(orderElement);

    // Setup event listeners
    setupOrderEventListeners(orderElement);

    // Load saved data if exists
    if (orders[orderIndex].data && Object.keys(orders[orderIndex].data).length > 0) {
      loadOrderData(orderElement, orders[orderIndex].data);
    }

    // Update separate whites checkbox based on whether this order has a linked whites order
    const separateWhitesCheckbox = orderElement.querySelector(".separate-whites");
    if (separateWhitesCheckbox) {
      separateWhitesCheckbox.checked = orders[orderIndex].hasLinkedWhitesOrder || false;
    }

    // If this is a whites order, disable the separate whites checkbox and show info
    if (orders[orderIndex].isWhitesOrder) {
      if (separateWhitesCheckbox) {
        separateWhitesCheckbox.disabled = true;
        separateWhitesCheckbox.checked = false;
        const label = separateWhitesCheckbox.parentElement;
        if (label) {
          label.style.opacity = '0.5';
          label.title = 'This is already a whites order';
        }
      }
    }

    // Update current index
    currentOrderIndex = orderIndex;

    // Calculate total for this order
    calculateOrderTotal(orderElement);
  }

  // Load data into order element
  function loadOrderData(orderElement, data) {
    // Load basic fields
    const roundsOfWash = orderElement.querySelector(".rounds-of-wash");
    const dryerPreference = orderElement.querySelector(".dryer-preference");
    const foldingService = orderElement.querySelector(".folding-service");
    const separateWhites = orderElement.querySelector(".separate-whites");
    const orderRemarks = orderElement.querySelector(".order-remarks");

    if (roundsOfWash) roundsOfWash.value = data.roundsOfWash || "1";
    if (dryerPreference) dryerPreference.value = data.dryerPreference || "0";
    if (foldingService) foldingService.checked = data.foldingService || false;
    if (separateWhites) separateWhites.checked = data.separateWhites || false;
    if (orderRemarks) orderRemarks.value = data.orderRemarks || "";

    // Load product selections
    const detergentProduct = orderElement.querySelector(".detergent-product");
    const fabconProduct = orderElement.querySelector(".fabcon-product");
    const bleachProduct = orderElement.querySelector(".bleach-product");

    if (detergentProduct && data.detergentProduct) {
      detergentProduct.value = data.detergentProduct;
      detergentProduct.dispatchEvent(new Event('change'));
      setTimeout(() => {
        const scoopsInput = orderElement.querySelector(".scoops-of-detergent");
        if (scoopsInput) {
          scoopsInput.value = data.detergentQuantity || "1";
          // Restore the price data attribute
          if (data.detergentPrice) {
            scoopsInput.dataset.price = data.detergentPrice;
          }
        }
      }, 50);
    }

    if (fabconProduct && data.fabconProduct) {
      fabconProduct.value = data.fabconProduct;
      fabconProduct.dispatchEvent(new Event('change'));
      setTimeout(() => {
        const fabconInput = orderElement.querySelector(".fabcon-cups");
        if (fabconInput) {
          fabconInput.value = data.fabconQuantity || "0";
          // Restore the price data attribute
          if (data.fabconPrice) {
            fabconInput.dataset.price = data.fabconPrice;
          }
        }
      }, 50);
    }

    if (bleachProduct && data.bleachProduct) {
      bleachProduct.value = data.bleachProduct;
      bleachProduct.dispatchEvent(new Event('change'));
      setTimeout(() => {
        const bleachInput = orderElement.querySelector(".bleach-cups");
        if (bleachInput) {
          bleachInput.value = data.bleachQuantity || "0";
          // Restore the price data attribute
          if (data.bleachPrice) {
            bleachInput.dataset.price = data.bleachPrice;
          }
        }
      }, 50);
    }

    // Load clothing items
    const clothingTops = orderElement.querySelector(".clothing-tops");
    const clothingBottoms = orderElement.querySelector(".clothing-bottoms");
    const clothingUndergarments = orderElement.querySelector(".clothing-undergarments");
    const clothingDelicates = orderElement.querySelector(".clothing-delicates");
    const clothingLinens = orderElement.querySelector(".clothing-linens");
    const clothingCurtains = orderElement.querySelector(".clothing-curtains-drapes");
    const clothingBlankets = orderElement.querySelector(".clothing-blankets-comforters");
    const clothingOthers = orderElement.querySelector(".clothing-others");

    if (clothingTops) clothingTops.value = data.clothingTops || "0";
    if (clothingBottoms) clothingBottoms.value = data.clothingBottoms || "0";
    if (clothingUndergarments) clothingUndergarments.value = data.clothingUndergarments || "0";
    if (clothingDelicates) clothingDelicates.value = data.clothingDelicates || "0";
    if (clothingLinens) clothingLinens.value = data.clothingLinens || "0";
    if (clothingCurtains) clothingCurtains.value = data.clothingCurtains || "0";
    if (clothingBlankets) clothingBlankets.value = data.clothingBlankets || "0";
    if (clothingOthers) clothingOthers.value = data.clothingOthers || "0";

    // Handle clothing section visibility
    if (data.clothingSectionVisible) {
      const clothingSection = orderElement.querySelector(".clothing-items-section");
      const toggleButton = orderElement.querySelector(".toggle-clothing-items");
      if (clothingSection) {
        clothingSection.classList.remove("d-none");
        if (toggleButton) toggleButton.textContent = "Hide Clothing & Household Items";
      }
    }
  }

  // Navigate to specific order
  function navigateToOrder(orderIndex) {
    // Save current order first
    saveCurrentOrderData(currentOrderIndex);

    // Display the selected order
    displayOrder(orderIndex);

    // Recalculate grand total
    calculateGrandTotal();
  }

  // Delete an order
  function deleteOrder(orderIndex) {
    // Check if there's only one non-whites order left
    const nonWhitesOrders = orders.filter(order => !order.isWhitesOrder);
    if (nonWhitesOrders.length === 1) {
      alert("You must have at least one order.");
      return;
    }

    const orderToDelete = orders[orderIndex];
    let confirmMessage = `Are you sure you want to delete Order #${orderToDelete.orderNumber}?`;
    
    // Check if this order has a linked whites order
    if (orderToDelete.hasLinkedWhitesOrder) {
      confirmMessage = `This order has a linked whites order. Both will be deleted. Continue?`;
    }

    if (!confirm(confirmMessage)) {
      return;
    }

    // If this order has a linked whites order, delete it too
    if (orderToDelete.hasLinkedWhitesOrder) {
      const whitesOrderIndex = orders.findIndex(order => 
        order.linkedToOrder === orderToDelete.orderNumber && order.isWhitesOrder
      );
      if (whitesOrderIndex !== -1) {
        // Remove whites order first (to avoid index issues)
        if (whitesOrderIndex > orderIndex) {
          orders.splice(whitesOrderIndex, 1);
          orders.splice(orderIndex, 1);
        } else {
          orders.splice(orderIndex, 1);
          orders.splice(whitesOrderIndex, 1);
        }
      } else {
        orders.splice(orderIndex, 1);
      }
    } else {
      orders.splice(orderIndex, 1);
    }

    // Renumber all remaining orders and update links
    orders.forEach((order, index) => {
      const oldOrderNumber = order.orderNumber;
      order.orderNumber = index + 1;
      
      // Update linkedToOrder references
      if (order.linkedToOrder) {
        const linkedOrder = orders.find(o => 
          !o.isWhitesOrder && o.hasLinkedWhitesOrder && 
          orders.indexOf(o) < index
        );
        if (linkedOrder) {
          order.linkedToOrder = linkedOrder.orderNumber;
        }
      }
    });

    // Update orderCounter to match the highest order number
    orderCounter = orders.length;

    // Adjust current index if needed
    if (currentOrderIndex >= orders.length) {
      currentOrderIndex = orders.length - 1;
    }

    // Display current order
    displayOrder(currentOrderIndex);

    // Update UI
    updateOrderListUI();
    updateAddOrderButtonState();
    calculateGrandTotal();
  }

  // Handle separate whites checkbox change
  function handleSeparateWhitesChange(isChecked) {
    // Save current order data first
    saveCurrentOrderData(currentOrderIndex);
    
    const currentOrder = orders[currentOrderIndex];
    
    if (isChecked) {
      // Check if there's already a linked whites order
      const hasLinkedWhitesOrder = orders.some((order, index) => 
        order.linkedToOrder === currentOrder.orderNumber && order.isWhitesOrder
      );
      
      if (!hasLinkedWhitesOrder) {
        // Create a new order for whites
        orderCounter++;
        const whitesOrderData = {
          orderNumber: orderCounter,
          isWhitesOrder: true,
          linkedToOrder: currentOrder.orderNumber,
          data: JSON.parse(JSON.stringify(currentOrder.data)) // Clone the data
        };
        
        // Mark the current order as having a linked whites order
        currentOrder.hasLinkedWhitesOrder = true;
        
        orders.push(whitesOrderData);
        
        // Renumber all orders
        orders.forEach((order, index) => {
          order.orderNumber = index + 1;
        });
        orderCounter = orders.length;
        
        // Update UI
        updateOrderListUI();
        updateAddOrderButtonState();
        calculateGrandTotal();
        
        // Show notification
        showNotification(`Whites order created! Navigate to Order #${whitesOrderData.orderNumber} to customize it.`);
      }
    } else {
      // Find and remove the linked whites order
      const whitesOrderIndex = orders.findIndex((order, index) => 
        order.linkedToOrder === currentOrder.orderNumber && order.isWhitesOrder
      );
      
      if (whitesOrderIndex !== -1) {
        if (confirm('This will delete the linked whites order. Continue?')) {
          orders.splice(whitesOrderIndex, 1);
          currentOrder.hasLinkedWhitesOrder = false;
          
          // Renumber all orders
          orders.forEach((order, index) => {
            order.orderNumber = index + 1;
          });
          orderCounter = orders.length;
          
          // Adjust current index if needed
          if (currentOrderIndex >= orders.length) {
            currentOrderIndex = orders.length - 1;
          }
          
          // Refresh display
          displayOrder(currentOrderIndex);
          updateOrderListUI();
          updateAddOrderButtonState();
          calculateGrandTotal();
        } else {
          // User cancelled, recheck the checkbox
          const separateWhitesCheckbox = document.querySelector(".separate-whites");
          if (separateWhitesCheckbox) {
            separateWhitesCheckbox.checked = true;
          }
        }
      }
    }
  }

  // Show notification helper
  function showNotification(message) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'order-notification';
    notification.innerHTML = `
      <i class="fas fa-info-circle me-2"></i>
      ${message}
    `;
    notification.style.cssText = `
      position: fixed;
      top: 80px;
      right: 20px;
      background-color: var(--primary-color);
      color: white;
      padding: 12px 20px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      z-index: 9999;
      animation: slideIn 0.3s ease-out;
    `;
    
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
      notification.style.animation = 'slideOut 0.3s ease-out';
      setTimeout(() => notification.remove(), 300);
    }, 3000);
  }

  // Update order list UI
  function updateOrderListUI() {
    const orderListContainer = document.getElementById("orderListContainer");
    if (!orderListContainer) return;

    orderListContainer.innerHTML = "";

    orders.forEach((order, index) => {
      const orderItem = document.createElement("div");
      const isWhitesOrder = order.isWhitesOrder;
      const whitesClass = isWhitesOrder ? 'whites-order' : '';
      orderItem.className = `order-list-item ${index === currentOrderIndex ? 'active' : ''} ${whitesClass}`;
      
      const orderLabel = isWhitesOrder 
        ? `Order #${order.orderNumber} <span class="whites-badge"><i class="fas fa-tshirt"></i> Whites</span>` 
        : `Order #${order.orderNumber}`;
      
      // Don't allow deletion if there's only one non-whites order left
      const nonWhitesOrders = orders.filter(o => !o.isWhitesOrder);
      const canDelete = nonWhitesOrders.length > 1 && !isWhitesOrder;
      
      orderItem.innerHTML = `
        <div class="order-list-item-content" onclick="window.navigateToOrder(${index})">
          <span class="order-list-number">${orderLabel}</span>
          <span class="order-list-total">₱<span class="order-list-total-value">${getOrderTotal(index)}</span></span>
        </div>
        ${canDelete ? `<button type="button" class="btn btn-sm btn-outline-danger order-list-delete" onclick="window.deleteOrder(${index})" title="Delete order"><i class="fas fa-trash"></i></button>` : ''}
      `;
      orderListContainer.appendChild(orderItem);
    });
  }

  // Get total for a specific order
  function getOrderTotal(orderIndex) {
    if (!orders[orderIndex] || !orders[orderIndex].data) return "0.00";
    
    const data = orders[orderIndex].data;
    
    // Return the stored order total if it exists
    if (data.orderTotal !== undefined && data.orderTotal !== null) {
      return data.orderTotal.toFixed(2);
    }
    
    // Fallback calculation (for backwards compatibility or if total wasn't saved)
    let total = 0;

    // Add wash fees
    const rounds = parseInt(data.roundsOfWash) || 1;
    total += rounds * (window.PRICES?.washFeePerRound || 70);

    // Add dryer fees
    const dryerRounds = parseInt(data.dryerPreference) || 0;
    total += dryerRounds * (window.PRICES?.dryerFeePerRound || 70);

    // Add folding fee
    if (data.foldingService) {
      total += window.PRICES?.foldingFee || 0;
    }

    // Add product costs
    const orderElement = document.querySelector(".order-item");
    if (orderElement) {
      // Detergent cost
      if (data.detergentProduct) {
        const price = parseFloat(data.detergentPrice || 0);
        total += (parseInt(data.detergentQuantity) || 0) * price;
      }

      // Fabric conditioner cost
      if (data.fabconProduct) {
        const price = parseFloat(data.fabconPrice || 0);
        total += (parseInt(data.fabconQuantity) || 0) * price;
      }

      // Bleach cost
      if (data.bleachProduct) {
        const price = parseFloat(data.bleachPrice || 0);
        total += (parseInt(data.bleachQuantity) || 0) * price;
      }
    }

    return total.toFixed(2);
  }

  // Expose functions to window for onclick handlers
  window.navigateToOrder = navigateToOrder;
  window.deleteOrder = deleteOrder;

  // Setup event listeners for a specific order
  function setupOrderEventListeners(orderElement) {
    const orderNumber = orderElement.dataset.orderNumber;

    // Remove button - hide it in single-form view
    const removeButton = orderElement.querySelector(".remove-order-btn");
    if (removeButton) {
      removeButton.style.display = "none";
    }

    // Product selection handlers
    const detergentProduct = orderElement.querySelector(".detergent-product");
    const fabconProduct = orderElement.querySelector(".fabcon-product");
    const bleachProduct = orderElement.querySelector(".bleach-product");

    const scoopsInput = orderElement.querySelector(".scoops-of-detergent");
    const fabconInput = orderElement.querySelector(".fabcon-cups");
    const bleachInput = orderElement.querySelector(".bleach-cups");

    const detergentStockInfo = orderElement.querySelector(
      ".detergent-stock-info",
    );
    const fabconStockInfo = orderElement.querySelector(".fabcon-stock-info");
    const bleachStockInfo = orderElement.querySelector(".bleach-stock-info");

    // Product selection events
    detergentProduct.addEventListener("change", function () {
      handleProductSelection(
        "detergent",
        this,
        scoopsInput,
        detergentStockInfo,
        10
      );
      calculateOrderTotal(orderElement);
    });

    fabconProduct.addEventListener("change", function () {
      handleProductSelection("fabcon", this, fabconInput, fabconStockInfo, 10);
      calculateOrderTotal(orderElement);
    });

    bleachProduct.addEventListener("change", function () {
      handleProductSelection("bleach", this, bleachInput, bleachStockInfo, 5);
      calculateOrderTotal(orderElement);
    });

    // Product quantity change events (with validation and integer enforcement)
    [scoopsInput, fabconInput, bleachInput].forEach((input) => {
      // Prevent decimal input
      input.addEventListener("keydown", preventDecimalInput);
      
      input.addEventListener("input", () => {
        enforceIntegerValue(input);
        validateQuantity(input);
        calculateOrderTotal(orderElement);
      });

      input.addEventListener("blur", () => {
        enforceIntegerValue(input);
        enforceQuantityLimits(input);
        calculateOrderTotal(orderElement);
      });

      // Prevent paste of decimal values
      input.addEventListener("paste", (e) => {
        setTimeout(() => {
          enforceIntegerValue(input);
          validateQuantity(input);
          calculateOrderTotal(orderElement);
        }, 0);
      });
    });

    // Clothing and household items
    const clothingInputs = [
      ".clothing-tops",
      ".clothing-bottoms",
      ".clothing-undergarments",
      ".clothing-delicates",
      ".clothing-linens",
      ".clothing-curtains-drapes",
      ".clothing-blankets-comforters",
      ".clothing-others",
    ].map((selector) => orderElement.querySelector(selector));

    // Other order inputs
    const roundsOfWash = orderElement.querySelector(".rounds-of-wash");
    const dryerPreference = orderElement.querySelector(".dryer-preference");
    const foldingService = orderElement.querySelector(".folding-service");
    const separateWhites = orderElement.querySelector(".separate-whites");
    const orderRemarks = orderElement.querySelector(".order-remarks");
    const toggleClothingButton = orderElement.querySelector(
      ".toggle-clothing-items"
    );
    const clothingItemsSection = orderElement.querySelector(
      ".clothing-items-section"
    );

    // Toggle clothing items section
    toggleClothingButton.addEventListener("click", () => {
      clothingItemsSection.classList.toggle("d-none");
      toggleClothingButton.textContent =
        clothingItemsSection.classList.contains("d-none")
          ? "Show Clothing & Household Items"
          : "Hide Clothing & Household Items";
    });

    // Add validation for clothing inputs with integer enforcement
    clothingInputs.forEach((input) => {
      if (input) {
        // Prevent decimal input
        input.addEventListener("keydown", preventDecimalInput);
        
        input.addEventListener("input", () => {
          enforceIntegerValue(input);
          validateClothingQuantity(input);
          calculateOrderTotal(orderElement);
        });

        input.addEventListener("blur", () => {
          enforceIntegerValue(input);
          enforceClothingQuantityLimits(input);
          calculateOrderTotal(orderElement);
        });

        // Prevent paste of decimal values
        input.addEventListener("paste", (e) => {
          setTimeout(() => {
            enforceIntegerValue(input);
            validateClothingQuantity(input);
            calculateOrderTotal(orderElement);
          }, 0);
        });
      }
    });

    // Prevent decimal input for rounds and dryer preference
    [roundsOfWash, dryerPreference].forEach((input) => {
      if (input && input.type === "number") {
        input.addEventListener("keydown", preventDecimalInput);
        
        input.addEventListener("input", () => {
          enforceIntegerValue(input);
          calculateOrderTotal(orderElement);
        });

        input.addEventListener("paste", (e) => {
          setTimeout(() => {
            enforceIntegerValue(input);
            calculateOrderTotal(orderElement);
          }, 0);
        });
      }
    });

    // Separate whites checkbox with new handler
    if (separateWhites) {
      separateWhites.addEventListener("change", function() {
        handleSeparateWhitesChange(this.checked);
        calculateOrderTotal(orderElement);
      });
    }

    // Other inputs
    [foldingService, orderRemarks].forEach((input) => {
      if (input) {
        const eventType = input.type === "checkbox" ? "change" : "input";
        input.addEventListener(eventType, () =>
          calculateOrderTotal(orderElement)
        );
      }
    });
  }

  // Prevent decimal input and enforce max limit (e, E, +, -, .)
  function preventDecimalInput(e) {
    if (
      e.key === "." ||
      e.key === "e" ||
      e.key === "E" ||
      e.key === "+" ||
      e.key === "-"
    ) {
      e.preventDefault();
    }
  }

  // Enforce integer value (remove decimals)
  function enforceIntegerValue(input) {
    const value = parseFloat(input.value);
    if (!isNaN(value) && value !== Math.floor(value)) {
      input.value = Math.floor(value);
    }
  }

  // Handle product selection
  function handleProductSelection(
    productType,
    selectElement,
    quantityInput,
    stockInfo,
    maxQuantity
  ) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    if (selectedOption.value) {
      const stock = parseInt(selectedOption.dataset.stock) || 0;
      const price = parseFloat(selectedOption.dataset.price) || 0;
      const measurement = selectedOption.dataset.measurement || "";

      if (stockInfo) {
        stockInfo.innerHTML = `<small class="text-${stock > 0 ? "success" : "danger"}"><i class="fas fa-box"></i> ${stock} ${measurement} available</small>`;
      }

      quantityInput.disabled = false;
      quantityInput.dataset.stock = stock;
      quantityInput.dataset.price = price;
      quantityInput.max = Math.min(maxQuantity, stock);

      // Set default value to 1 for detergent if currently 0 or empty
      if (productType === "detergent" && (!quantityInput.value || parseInt(quantityInput.value) === 0)) {
        quantityInput.value = 1;
      }

      // If current value exceeds stock, adjust it
      if (parseInt(quantityInput.value) > stock) {
        quantityInput.value = stock;
      }
    } else {
      if (stockInfo) {
        stockInfo.innerHTML = "";
      }
      quantityInput.disabled = true;
      quantityInput.value = 0;
      quantityInput.dataset.stock = 0;
      quantityInput.dataset.price = 0;
    }
  }

  // Validate quantity (visual feedback, no blocking)
  function validateQuantity(input) {
    const value = parseInt(input.value) || 0;
    const max = parseInt(input.max) || 0;
    const stock = parseInt(input.dataset.stock) || 0;

    if (value > stock || value > max) {
      input.classList.add("is-invalid");
    } else {
      input.classList.remove("is-invalid");
    }
  }

  // Enforce quantity limits (hard enforcement on blur)
  function enforceQuantityLimits(input) {
    let value = parseInt(input.value) || 0;
    const min = parseInt(input.min) || 0;
    const max = parseInt(input.max) || 0;
    const stock = parseInt(input.dataset.stock) || 0;

    // Enforce minimum
    if (value < min) {
      value = min;
    }

    // Enforce maximum (stock or max, whichever is lower)
    const actualMax = Math.min(max, stock);
    if (value > actualMax) {
      value = actualMax;
    }

    input.value = value;
    input.classList.remove("is-invalid");
  }

  // Validate clothing quantity
  function validateClothingQuantity(input) {
    const value = parseInt(input.value) || 0;
    const max = parseInt(input.max) || 0;

    if (value > max) {
      input.classList.add("is-invalid");
    } else {
      input.classList.remove("is-invalid");
    }
  }

  // Enforce clothing quantity limits
  function enforceClothingQuantityLimits(input) {
    let value = parseInt(input.value) || 0;
    const min = parseInt(input.min) || 0;
    const max = parseInt(input.max) || 0;

    if (value < min) {
      value = min;
    }

    if (value > max) {
      value = max;
    }

    input.value = value;
    input.classList.remove("is-invalid");
  }

  // Calculate order total for a specific order
  function calculateOrderTotal(orderElement) {
    let total = 0;

    // Wash fees
    const roundsOfWash = parseInt(
      orderElement.querySelector(".rounds-of-wash").value
    ) || 1;
    total += roundsOfWash * PRICES.washFeePerRound;

    // Dryer fees
    const dryerPreference = parseInt(
      orderElement.querySelector(".dryer-preference").value
    ) || 0;
    total += dryerPreference * PRICES.dryerFeePerRound;

    // Folding service
    if (orderElement.querySelector(".folding-service").checked) {
      total += PRICES.foldingFee;
    }

    // Product costs
    const scoopsInput = orderElement.querySelector(".scoops-of-detergent");
    const fabconInput = orderElement.querySelector(".fabcon-cups");
    const bleachInput = orderElement.querySelector(".bleach-cups");

    if (scoopsInput.value && !scoopsInput.disabled) {
      const price = parseFloat(scoopsInput.dataset.price) || 0;
      total += parseInt(scoopsInput.value) * price;
    }

    if (fabconInput.value && !fabconInput.disabled) {
      const price = parseFloat(fabconInput.dataset.price) || 0;
      total += parseInt(fabconInput.value) * price;
    }

    if (bleachInput.value && !bleachInput.disabled) {
      const price = parseFloat(bleachInput.dataset.price) || 0;
      total += parseInt(bleachInput.value) * price;
    }

    // Update order total display
    const orderTotalElement = orderElement.querySelector(".order-total");
    if (orderTotalElement) {
      orderTotalElement.textContent = total.toFixed(2);
    }

    // Save the total to the current order data
    if (orders[currentOrderIndex]) {
      if (!orders[currentOrderIndex].data) {
        orders[currentOrderIndex].data = {};
      }
      orders[currentOrderIndex].data.orderTotal = total;
    }

    // Recalculate grand total and update order list display
    calculateGrandTotal();
    updateOrderListUI();

    return total;
  }

  // Calculate grand total from all orders
  function calculateGrandTotal() {
    // Save current order data first
    saveCurrentOrderData(currentOrderIndex);

    let grandTotal = 0;

    // Sum up all order totals
    orders.forEach((order) => {
      if (order.data && order.data.orderTotal) {
        grandTotal += order.data.orderTotal;
      }
    });

    // Store original grand total before balance
    const originalGrandTotal = grandTotal;

    // Apply balance if checkbox is checked
    let balanceUsed = 0;
    if (useBalanceCheckbox && useBalanceCheckbox.checked) {
      const availableBalance = parseFloat(customerBalanceSpan.textContent) || 0;
      balanceUsed = Math.min(availableBalance, grandTotal);
      grandTotal -= balanceUsed;
    }

    // Update grand total display
    if (grandTotalField) {
      grandTotalField.value = "₱" + grandTotal.toFixed(2);
    }

    return { grandTotal, originalGrandTotal, balanceUsed };
  }

  // Calculate all order totals (for backward compatibility)
  function calculateAllOrderTotals() {
    calculateGrandTotal();
  }

  // Setup main event listeners
  function setupEventListeners() {
    // Add order button
    if (addOrderButton) {
      addOrderButton.addEventListener("click", addNewOrder);
    }

    // Use balance checkbox
    if (useBalanceCheckbox) {
      useBalanceCheckbox.addEventListener("change", () => {
        calculateGrandTotal();
      });
    }

    // Prelist all orders button - prevent default form submission
    if (prelistAllOrdersButton) {
      prelistAllOrdersButton.addEventListener("click", function(e) {
        e.preventDefault(); // Prevent default form submission
        prelistAllOrders(e);
      });
    }
    
    // Add form submit handler to prevent accidental submission
    const form = document.querySelector('#prelistOrderModal form');
    if (form) {
      form.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent default submission
        prelistAllOrders(e);
        return false;
      });
    }
  }

  // Validate all orders before submission
  function validateAllOrders() {
    let isValid = true;
    
    // Validate all orders in the orders array
    orders.forEach((order, index) => {
      const data = order.data;
      
      // Check detergent product and quantity
      if (!data.detergentProduct) {
        isValid = false;
      }
      
      if (data.detergentProduct && parseInt(data.detergentQuantity) < 1) {
        isValid = false;
      }
    });
    
    return isValid;
  }

  // Prelist all orders
  function prelistAllOrders(e) {
    if (e) {
      e.preventDefault();
      e.stopPropagation();
    }
    
    // Save current order data before validation
    saveCurrentOrderData(currentOrderIndex);

    // Validate all orders first
    if (!validateAllOrders()) {
      alert("Please ensure all orders have valid detergent selection and quantities within allowed limits.");
      return false;
    }

    // Collect and filter order data
    const ordersData = [];
    orders.forEach((order) => {
      const orderData = collectOrderDataFromArray(order);
      if (orderData) {
        ordersData.push(orderData);
      }
    });

    if (ordersData.length === 0) {
      alert("Please add at least one valid order with detergent selected.");
      return false;
    }

    // Prepare form data
    const formData = {
      orders: ordersData,
      grand_total: calculateGrandTotal().grandTotal,
      use_balance: useBalanceCheckbox.checked,
      balance_used: useBalanceCheckbox.checked
        ? Math.min(
            parseFloat(customerBalanceSpan.textContent) || 0,
            calculateGrandTotal().originalGrandTotal
          )
        : 0,
      global_remarks: globalRemarksField.value.trim(),
    };

    // Update hidden field with orders data
    if (ordersDataField) {
      ordersDataField.value = JSON.stringify(formData);
    }

    // Submit form programmatically
    const form = document.querySelector('#prelistOrderModal form');
    if (form) {
      // Remove any event listeners temporarily
      const newForm = form.cloneNode(true);
      form.parentNode.replaceChild(newForm, form);
      
      // Set the hidden field value again on the new form
      const newOrdersDataField = newForm.querySelector('#orders_data');
      if (newOrdersDataField) {
        newOrdersDataField.value = JSON.stringify(formData);
      }
      
      // Submit the new form
      newForm.submit();
    }
    
    return false;
  }

  // Collect order data from order object in array
  function collectOrderDataFromArray(order) {
    const data = order.data;
    
    // Validate required fields first
    if (!data.detergentProduct || parseInt(data.detergentQuantity) < 1) {
      return null; // Skip invalid orders
    }

    const orderData = {
      order_number: order.orderNumber,
      is_whites_order: order.isWhitesOrder || false,
      linked_to_order: order.linkedToOrder || null,
      rounds_of_wash: parseInt(data.roundsOfWash) || 1,
      dryer_preference: parseInt(data.dryerPreference) || 0,
      folding_service: data.foldingService || false,
      separate_whites: data.separateWhites || false,
      remarks: data.orderRemarks || "",
      products: [],
      items: {
        tops: parseInt(data.clothingTops) || 0,
        bottoms: parseInt(data.clothingBottoms) || 0,
        undergarments: parseInt(data.clothingUndergarments) || 0,
        delicates: parseInt(data.clothingDelicates) || 0,
        linens: parseInt(data.clothingLinens) || 0,
        curtains_drapes: parseInt(data.clothingCurtains) || 0,
        blankets_comforters: parseInt(data.clothingBlankets) || 0,
        others: parseInt(data.clothingOthers) || 0,
      },
    };

    // Collect detergent data (required)
    orderData.products.push({
      type: "detergent",
      product_id: data.detergentProduct,
      quantity: parseInt(data.detergentQuantity),
      unit_price: parseFloat(data.detergentPrice) || 0,
    });

    // Collect optional products only if they have values
    if (data.fabconProduct && parseInt(data.fabconQuantity) > 0) {
      orderData.products.push({
        type: "fabric_conditioner",
        product_id: data.fabconProduct,
        quantity: parseInt(data.fabconQuantity),
        unit_price: parseFloat(data.fabconPrice) || 0,
      });
    }

    if (data.bleachProduct && parseInt(data.bleachQuantity) > 0) {
      orderData.products.push({
        type: "bleach",
        product_id: data.bleachProduct,
        quantity: parseInt(data.bleachQuantity),
        unit_price: parseFloat(data.bleachPrice) || 0,
      });
    }

    return orderData;
  }

  // Initialize the application
  initModal();
});