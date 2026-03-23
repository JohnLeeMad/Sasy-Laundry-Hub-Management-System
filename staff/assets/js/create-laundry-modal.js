document.addEventListener("DOMContentLoaded", function () {
  // DOM Elements
  const customerTypeRadios = document.querySelectorAll(
    'input[name="customer_type"]',
  );
  const registeredDiv = document.getElementById("registeredCustomerFields");
  const walkInDiv = document.getElementById("walkInCustomerFields");
  const customerIdField = document.getElementById("customer_id");
  const customerNameField = document.getElementById("customer_name");
  const customerPhoneField = document.getElementById("customer_phone");
  const registeredCustomerPhone = document.getElementById(
    "registered_customer_phone",
  );

  // Orders management
  const ordersContainer = document.getElementById("ordersContainer");
  const addOrderButton = document.getElementById("addOrderButton");
  const orderTemplate = document.getElementById("orderTemplate");
  const grandTotalField = document.getElementById("grand_total");
  const amountTenderedField = document.getElementById("amount_tendered");
  const changeField = document.getElementById("change");
  const createAllOrdersButton = document.getElementById(
    "createAllOrdersButton",
  );
  const ordersDataField = document.getElementById("orders_data");
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

    toggleCustomerFields(
      document.querySelector('input[name="customer_type"]:checked').value,
    );
    setupEventListeners();
    addFirstOrder();
    initializeCustomerSelect2();

    // Calculate initial total
    const firstOrder = document.querySelector(".order-item");
    if (firstOrder) {
      calculateOrderTotal(firstOrder);
    }
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

  // Duplicate data from one order to another
  function duplicateOrderData(sourceOrder, targetOrder) {
    // Duplicate basic fields
    const roundsOfWash = sourceOrder.querySelector(".rounds-of-wash");
    const dryerPreference = sourceOrder.querySelector(".dryer-preference");
    const foldingService = sourceOrder.querySelector(".folding-service");
    const separateWhites = sourceOrder.querySelector(".separate-whites");
    const orderRemarks = sourceOrder.querySelector(".order-remarks");

    const targetRoundsOfWash = targetOrder.querySelector(".rounds-of-wash");
    const targetDryerPreference = targetOrder.querySelector(".dryer-preference");
    const targetFoldingService = targetOrder.querySelector(".folding-service");
    const targetSeparateWhites = targetOrder.querySelector(".separate-whites");
    const targetOrderRemarks = targetOrder.querySelector(".order-remarks");

    if (roundsOfWash && targetRoundsOfWash) {
      targetRoundsOfWash.value = roundsOfWash.value;
    }
    if (dryerPreference && targetDryerPreference) {
      targetDryerPreference.value = dryerPreference.value;
    }
    if (foldingService && targetFoldingService) {
      targetFoldingService.checked = foldingService.checked;
    }
    if (separateWhites && targetSeparateWhites) {
      targetSeparateWhites.checked = separateWhites.checked;
    }
    if (orderRemarks && targetOrderRemarks) {
      targetOrderRemarks.value = orderRemarks.value;
    }

    // Duplicate product selections
    const detergentProduct = sourceOrder.querySelector(".detergent-product");
    const fabconProduct = sourceOrder.querySelector(".fabcon-product");
    const bleachProduct = sourceOrder.querySelector(".bleach-product");

    const targetDetergentProduct = targetOrder.querySelector(".detergent-product");
    const targetFabconProduct = targetOrder.querySelector(".fabcon-product");
    const targetBleachProduct = targetOrder.querySelector(".bleach-product");

    if (detergentProduct && targetDetergentProduct) {
      targetDetergentProduct.value = detergentProduct.value;
      // Trigger change event to update stock info and enable quantity input
      targetDetergentProduct.dispatchEvent(new Event('change'));
      
      // Copy quantity after change event
      setTimeout(() => {
        const scoopsInput = sourceOrder.querySelector(".scoops-of-detergent");
        const targetScoopsInput = targetOrder.querySelector(".scoops-of-detergent");
        if (scoopsInput && targetScoopsInput) {
          targetScoopsInput.value = scoopsInput.value;
        }
      }, 50);
    }

    if (fabconProduct && targetFabconProduct) {
      targetFabconProduct.value = fabconProduct.value;
      targetFabconProduct.dispatchEvent(new Event('change'));
      
      setTimeout(() => {
        const fabconInput = sourceOrder.querySelector(".fabcon-cups");
        const targetFabconInput = targetOrder.querySelector(".fabcon-cups");
        if (fabconInput && targetFabconInput) {
          targetFabconInput.value = fabconInput.value;
        }
      }, 50);
    }

    if (bleachProduct && targetBleachProduct) {
      targetBleachProduct.value = bleachProduct.value;
      targetBleachProduct.dispatchEvent(new Event('change'));
      
      setTimeout(() => {
        const bleachInput = sourceOrder.querySelector(".bleach-cups");
        const targetBleachInput = targetOrder.querySelector(".bleach-cups");
        if (bleachInput && targetBleachInput) {
          targetBleachInput.value = bleachInput.value;
        }
      }, 50);
    }

    // Duplicate clothing items
    const clothingFields = [
      ".clothing-tops",
      ".clothing-bottoms",
      ".clothing-undergarments",
      ".clothing-delicates",
      ".clothing-linens",
      ".clothing-curtains-drapes",
      ".clothing-blankets-comforters",
      ".clothing-others"
    ];

    clothingFields.forEach(selector => {
      const sourceField = sourceOrder.querySelector(selector);
      const targetField = targetOrder.querySelector(selector);
      if (sourceField && targetField) {
        targetField.value = sourceField.value;
      }
    });

    // Duplicate clothing section visibility
    const sourceClothingSection = sourceOrder.querySelector(".clothing-items-section");
    const targetClothingSection = targetOrder.querySelector(".clothing-items-section");
    const targetToggleButton = targetOrder.querySelector(".toggle-clothing-items");
    
    if (sourceClothingSection && targetClothingSection && !sourceClothingSection.classList.contains("d-none")) {
      targetClothingSection.classList.remove("d-none");
      if (targetToggleButton) {
        targetToggleButton.textContent = "Hide Clothing & Household Items";
      }
    }
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

    // FIXED: Save the order total from the form
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
    // FIXED: Check if there's only one non-whites order left
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
      
      // FIXED: Don't allow deletion if there's only one non-whites order left
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
    
    // FIXED: Return the stored order total if it exists
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

    // Add product costs - get prices from DOM select options
    const orderElement = document.querySelector(".order-item");
    if (orderElement) {
      // Detergent cost
      if (data.detergentProduct) {
        const detergentSelect = orderElement.querySelector(".detergent-product");
        const detergentOption = detergentSelect?.querySelector(`option[value="${data.detergentProduct}"]`);
        const price = parseFloat(detergentOption?.dataset.price || 0);
        total += (parseInt(data.detergentQuantity) || 0) * price;
      }

      // Fabric conditioner cost
      if (data.fabconProduct) {
        const fabconSelect = orderElement.querySelector(".fabcon-product");
        const fabconOption = fabconSelect?.querySelector(`option[value="${data.fabconProduct}"]`);
        const price = parseFloat(fabconOption?.dataset.price || 0);
        total += (parseInt(data.fabconQuantity) || 0) * price;
      }

      // Bleach cost
      if (data.bleachProduct) {
        const bleachSelect = orderElement.querySelector(".bleach-product");
        const bleachOption = bleachSelect?.querySelector(`option[value="${data.bleachProduct}"]`);
        const price = parseFloat(bleachOption?.dataset.price || 0);
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

    // Remove button - no longer used in single-form view, kept for template compatibility
    const removeButton = orderElement.querySelector(".remove-order-btn");
    if (removeButton) {
      removeButton.style.display = "none"; // Hide the remove button
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

    if (detergentProduct) {
      detergentProduct.addEventListener("change", function () {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
          const stock = parseInt(selectedOption.dataset.stock) || 0;
          const price = parseFloat(selectedOption.dataset.price) || 0;
          const measurement = selectedOption.dataset.measurement || "";

          if (detergentStockInfo) {
            detergentStockInfo.innerHTML = `<small class="text-${stock > 0 ? "success" : "danger"}"><i class="fas fa-box"></i> ${stock} ${measurement} available</small>`;
          }

          scoopsInput.disabled = false;
          scoopsInput.dataset.stock = stock;
          scoopsInput.dataset.price = price;
          scoopsInput.max = Math.min(10, stock);

          // Set default value to 1 if currently 0 or empty
          if (!scoopsInput.value || parseInt(scoopsInput.value) === 0) {
            scoopsInput.value = 1;
          }

          // If current value exceeds stock, adjust it
          if (parseInt(scoopsInput.value) > stock) {
            scoopsInput.value = stock;
          }

          calculateOrderTotal(orderElement);
        } else {
          if (detergentStockInfo) {
            detergentStockInfo.innerHTML = "";
          }
          scoopsInput.disabled = true;
          scoopsInput.value = 0;
          scoopsInput.dataset.stock = 0;
          scoopsInput.dataset.price = 0;
        }
      });
    }

    if (fabconProduct) {
      fabconProduct.addEventListener("change", function () {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
          const stock = parseInt(selectedOption.dataset.stock) || 0;
          const price = parseFloat(selectedOption.dataset.price) || 0;
          const measurement = selectedOption.dataset.measurement || "";

          if (fabconStockInfo) {
            fabconStockInfo.innerHTML = `<small class="text-${stock > 0 ? "success" : "danger"}"><i class="fas fa-box"></i> ${stock} ${measurement} available</small>`;
          }

          fabconInput.disabled = false;
          fabconInput.dataset.stock = stock;
          fabconInput.dataset.price = price;
          fabconInput.max = Math.min(10, stock);

          if (parseInt(fabconInput.value) > stock) {
            fabconInput.value = stock;
          }

          calculateOrderTotal(orderElement);
        } else {
          if (fabconStockInfo) {
            fabconStockInfo.innerHTML = "";
          }
          fabconInput.disabled = true;
          fabconInput.value = 0;
          fabconInput.dataset.stock = 0;
          fabconInput.dataset.price = 0;
        }
      });
    }

    if (bleachProduct) {
      bleachProduct.addEventListener("change", function () {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
          const stock = parseInt(selectedOption.dataset.stock) || 0;
          const price = parseFloat(selectedOption.dataset.price) || 0;
          const measurement = selectedOption.dataset.measurement || "";

          if (bleachStockInfo) {
            bleachStockInfo.innerHTML = `<small class="text-${stock > 0 ? "success" : "danger"}"><i class="fas fa-box"></i> ${stock} ${measurement} available</small>`;
          }

          bleachInput.disabled = false;
          bleachInput.dataset.stock = stock;
          bleachInput.dataset.price = price;
          bleachInput.max = Math.min(10, stock);

          if (parseInt(bleachInput.value) > stock) {
            bleachInput.value = stock;
          }

          calculateOrderTotal(orderElement);
        } else {
          if (bleachStockInfo) {
            bleachStockInfo.innerHTML = "";
          }
          bleachInput.disabled = true;
          bleachInput.value = 0;
          bleachInput.dataset.stock = 0;
          bleachInput.dataset.price = 0;
        }
      });
    }

    // Quantity input handlers with validation
    if (scoopsInput) {
      scoopsInput.addEventListener("input", function () {
        validateQuantity(this);
        calculateOrderTotal(orderElement);
      });

      scoopsInput.addEventListener("blur", function () {
        enforceQuantityLimits(this);
        calculateOrderTotal(orderElement);
      });
    }

    if (fabconInput) {
      fabconInput.addEventListener("input", function () {
        validateQuantity(this);
        calculateOrderTotal(orderElement);
      });

      fabconInput.addEventListener("blur", function () {
        enforceQuantityLimits(this);
        calculateOrderTotal(orderElement);
      });
    }

    if (bleachInput) {
      bleachInput.addEventListener("input", function () {
        validateQuantity(this);
        calculateOrderTotal(orderElement);
      });

      bleachInput.addEventListener("blur", function () {
        enforceQuantityLimits(this);
        calculateOrderTotal(orderElement);
      });
    }

    // Other field handlers
    const roundsOfWash = orderElement.querySelector(".rounds-of-wash");
    const dryerPreference = orderElement.querySelector(".dryer-preference");
    const foldingService = orderElement.querySelector(".folding-service");
    const separateWhites = orderElement.querySelector(".separate-whites");

    if (roundsOfWash) {
      roundsOfWash.addEventListener("change", function () {
        calculateOrderTotal(orderElement);
      });
    }

    if (dryerPreference) {
      dryerPreference.addEventListener("change", function () {
        calculateOrderTotal(orderElement);
      });
    }

    if (foldingService) {
      foldingService.addEventListener("change", function () {
        calculateOrderTotal(orderElement);
      });
    }

    if (separateWhites) {
      separateWhites.addEventListener("change", function () {
        handleSeparateWhitesChange(this.checked);
      });
    }

    // Toggle clothing items section
    const toggleButton = orderElement.querySelector(".toggle-clothing-items");
    const clothingSection = orderElement.querySelector(
      ".clothing-items-section",
    );

    if (toggleButton && clothingSection) {
      toggleButton.addEventListener("click", function () {
        clothingSection.classList.toggle("d-none");
        this.textContent = clothingSection.classList.contains("d-none")
          ? "Show Clothing & Household Items"
          : "Hide Clothing & Household Items";
      });
    }

    // Clothing quantity inputs validation
    const clothingInputs = orderElement.querySelectorAll(
      '.clothing-items-section input[type="number"]',
    );
    clothingInputs.forEach((input) => {
      input.addEventListener("input", function () {
        validateClothingQuantity(this);
      });

      input.addEventListener("blur", function () {
        enforceClothingQuantityLimits(this);
      });
    });
  }

  // Validate quantity input
  function validateQuantity(input) {
    const value = parseInt(input.value) || 0;
    const min = parseInt(input.min) || 0;
    const max = parseInt(input.max) || 0;
    const stock = parseInt(input.dataset.stock) || 0;

    // Remove existing feedback
    removeInputFeedback(input);

    if (value > stock) {
      addInputFeedback(input, `Only ${stock} units available`, "danger");
      return false;
    } else if (value < min) {
      addInputFeedback(input, `Minimum ${min} required`, "warning");
      return false;
    } else if (value > max) {
      addInputFeedback(input, `Maximum ${max} allowed`, "warning");
      return false;
    }

    return true;
  }

  // Validate clothing quantity input
  function validateClothingQuantity(input) {
    const value = parseInt(input.value) || 0;
    const min = parseInt(input.min) || 0;
    const max = parseInt(input.max) || 0;

    // Remove existing feedback
    removeInputFeedback(input);

    if (value < min) {
      addInputFeedback(input, `Minimum ${min} required`, "warning");
      return false;
    } else if (value > max) {
      addInputFeedback(input, `Maximum ${max} allowed`, "warning");
      return false;
    }

    return true;
  }

  // Enforce quantity limits (for products)
  function enforceQuantityLimits(input) {
    const min = parseInt(input.min) || 0;
    const max = parseInt(input.max) || 0;
    const stock = parseInt(input.dataset.stock) || 0;
    let value = parseInt(input.value) || 0;

    const actualMax = Math.min(max, stock);

    if (value < min) {
      input.value = min;
    } else if (value > actualMax) {
      input.value = actualMax;
    }

    removeInputFeedback(input);
  }

  // Enforce clothing quantity limits
  function enforceClothingQuantityLimits(input) {
    const min = parseInt(input.min) || 0;
    const max = parseInt(input.max) || 0;
    let value = parseInt(input.value) || 0;

    if (value < min) {
      input.value = min;
    } else if (value > max) {
      input.value = max;
    }

    removeInputFeedback(input);
  }

  // Add feedback to input
  function addInputFeedback(input, message, type = "danger") {
    removeInputFeedback(input);

    const feedbackDiv = document.createElement("div");
    feedbackDiv.className = `text-${type} small mt-1`;
    feedbackDiv.textContent = message;
    feedbackDiv.dataset.feedback = "true";

    input.parentNode.appendChild(feedbackDiv);
  }

  // Remove feedback from input
  function removeInputFeedback(input) {
    const existingFeedback = input.parentNode.querySelector(
      '[data-feedback="true"]',
    );
    if (existingFeedback) {
      existingFeedback.remove();
    }
  }

  // Calculate order total
  function calculateOrderTotal(orderElement) {
    const roundsOfWash =
      parseInt(orderElement.querySelector(".rounds-of-wash").value) || 1;
    const dryerPreference =
      parseInt(orderElement.querySelector(".dryer-preference").value) || 0;
    const foldingService =
      orderElement.querySelector(".folding-service").checked;

    const scoopsInput = orderElement.querySelector(".scoops-of-detergent");
    const fabconInput = orderElement.querySelector(".fabcon-cups");
    const bleachInput = orderElement.querySelector(".bleach-cups");

    const scoopsOfDetergent = parseInt(scoopsInput.value) || 0;
    const fabconCups = parseInt(fabconInput.value) || 0;
    const bleachCups = parseInt(bleachInput.value) || 0;

    const detergentUnitPrice = parseFloat(scoopsInput.dataset.price) || 0;
    const fabconUnitPrice = parseFloat(fabconInput.dataset.price) || 0;
    const bleachUnitPrice = parseFloat(bleachInput.dataset.price) || 0;

    const total =
      roundsOfWash * PRICES.washFeePerRound +
      dryerPreference * PRICES.dryerFeePerRound +
      scoopsOfDetergent * detergentUnitPrice +
      fabconCups * fabconUnitPrice +
      bleachCups * bleachUnitPrice +
      (foldingService ? PRICES.foldingFee : 0);

    // Update order total display
    orderElement.querySelector(".order-total").textContent = total.toFixed(2);

    // Recalculate grand total
    calculateGrandTotal();

    return total;
  }

  // Calculate grand total with balance deduction
  function calculateGrandTotal() {
    // Save current order data before calculating
    const currentOrderElement = ordersContainer.querySelector(".order-item");
    if (currentOrderElement) {
      saveCurrentOrderData(currentOrderIndex);
    }

    let grandTotal = 0;
    let originalGrandTotal = 0;

    // Calculate total from the current displayed order
    if (currentOrderElement) {
      const orderTotal =
        parseFloat(currentOrderElement.querySelector(".order-total")?.textContent) || 0;
      grandTotal += orderTotal;
      originalGrandTotal += orderTotal;
    }

    // Add totals from all other orders
    orders.forEach((order, index) => {
      if (index !== currentOrderIndex && order.data) {
        const orderTotal = parseFloat(getOrderTotal(index)) || 0;
        grandTotal += orderTotal;
        originalGrandTotal += orderTotal;
      }
    });

    const customerBalance = parseFloat(customerBalanceSpan.textContent) || 0;
    if (useBalanceCheckbox.checked && customerBalance > 0) {
      const deductedBalance = Math.min(customerBalance, originalGrandTotal);
      grandTotal = Math.max(0, originalGrandTotal - deductedBalance);
    }

    grandTotalField.value = `₱${grandTotal.toFixed(2)}`;

    // Recalculate change
    calculateChange(grandTotal, originalGrandTotal);

    // Update order list totals
    updateOrderListUI();

    return { grandTotal, originalGrandTotal };
  }

  // Calculate change
  function calculateChange(grandTotal, originalGrandTotal) {
    const amountTendered = parseFloat(amountTenderedField.value) || 0;
    const customerBalance = parseFloat(customerBalanceSpan.textContent) || 0;
    const deductedBalance = useBalanceCheckbox.checked
      ? Math.min(customerBalance, originalGrandTotal)
      : 0;
    const change = Math.max(
      0,
      amountTendered - (originalGrandTotal - deductedBalance),
    );
    const minimumPayment = (originalGrandTotal - deductedBalance) * 0.5;

    changeField.value = `₱${change.toFixed(2)}`;

    // Validate payment
    validatePayment(
      amountTendered,
      originalGrandTotal - deductedBalance,
      minimumPayment,
    );

    return change;
  }

  // Validate payment
  function validatePayment(amountTendered, adjustedTotal, minimumPayment) {
    const paymentFeedback = document.getElementById("payment-feedback");

    if (paymentFeedback) {
      paymentFeedback.remove();
    }

    if (amountTendered < minimumPayment) {
      const feedbackDiv = document.createElement("div");
      feedbackDiv.id = "payment-feedback";
      feedbackDiv.className = "text-danger small mt-1";
      feedbackDiv.textContent = `Minimum payment: ₱${minimumPayment.toFixed(
        2,
      )}`;
      amountTenderedField.parentNode.appendChild(feedbackDiv);

      // Disable create orders button
      createAllOrdersButton.disabled = true;
      createAllOrdersButton.textContent = "Insufficient Payment";

      return false;
    } else {
      // Enable create orders button
      createAllOrdersButton.disabled = false;
      createAllOrdersButton.textContent = "Create All Orders";

      return true;
    }
  }

  // Remove order with limit update
  // Toggle customer fields
  function toggleCustomerFields(customerType) {
    if (customerType === "registered") {
      registeredDiv.style.display = "block";
      walkInDiv.style.display = "none";

      // Clear walk-in fields
      customerNameField.value = "";
      customerPhoneField.value = "";
    } else {
      registeredDiv.style.display = "none";
      walkInDiv.style.display = "block";

      // Clear registered fields
      customerIdField.value = "";
      if (registeredCustomerPhone) {
        registeredCustomerPhone.value = "";
      }
      customerBalanceSpan.textContent = "0.00";
      useBalanceCheckbox.checked = false;
    }
  }

  // Setup main event listeners
  function setupEventListeners() {
    // Customer type radio buttons
    customerTypeRadios.forEach((radio) => {
      radio.addEventListener("change", function () {
        toggleCustomerFields(this.value);
      });
    });

    // Add order button
    if (addOrderButton) {
      addOrderButton.addEventListener("click", function () {
        addNewOrder();
      });
    }

    // Amount tendered
    if (amountTenderedField) {
      amountTenderedField.addEventListener("input", function () {
        const { grandTotal, originalGrandTotal } = calculateGrandTotal();
        calculateChange(grandTotal, originalGrandTotal);
      });
    }

    // Use balance checkbox
    if (useBalanceCheckbox) {
      useBalanceCheckbox.addEventListener("change", function () {
        calculateGrandTotal();
      });
    }

    // Walk-in phone number validation
    if (customerPhoneField) {
      // Validate on input
      customerPhoneField.addEventListener("input", function () {
        validatePhoneNumber(this);
      });

      // Prevent non-numeric characters from being typed
      customerPhoneField.addEventListener("keypress", function (e) {
        const char = String.fromCharCode(e.which);
        if (!/[\d]/.test(char)) {
          e.preventDefault();
        }
      });

      // Prevent paste of non-numeric characters
      customerPhoneField.addEventListener("paste", function (e) {
        e.preventDefault();
        const pastedText = (e.clipboardData || window.clipboardData).getData(
          "text",
        );
        const numbersOnly = pastedText.replace(/\D/g, "");
        document.execCommand("insertText", false, numbersOnly);
      });
    }

    // Create all orders button - UPDATED EVENT LISTENER
    if (createAllOrdersButton) {
      createAllOrdersButton.addEventListener("click", function (e) {
        // Only prevent default if validation fails
        if (!createAllOrders()) {
          e.preventDefault();
        }
        // If validation passes, the form will submit normally
      });
    }
  }

  // Fetch customer data (placeholder function)
  function fetchCustomerData(customerId) {
    // This would typically make an AJAX request to fetch customer data
    // For now, it's a placeholder
    console.log("Fetching customer data for ID:", customerId);

    const selectedOption =
      customerIdField.options[customerIdField.selectedIndex];
    if (selectedOption) {
      registeredCustomerPhone.value =
        selectedOption.dataset.contact_num || "N/A";
      customerBalanceSpan.textContent = parseFloat(
        selectedOption.dataset.balance || 0,
      ).toFixed(2);
    }
  }

  // Create all orders - FIXED VERSION
  function createAllOrders() {
    const customerType = document.querySelector(
      'input[name="customer_type"]:checked',
    ).value;
    const orderElements = document.querySelectorAll(".order-item");

    // Validate customer information
    if (!validateCustomerInfo(customerType)) {
      return false; // Return false to prevent form submission
    }

    // Validate payment
    const { grandTotal, originalGrandTotal } = calculateGrandTotal();
    const amountTendered = parseFloat(amountTenderedField.value) || 0;
    const customerBalance = parseFloat(customerBalanceSpan.textContent) || 0;
    const deductedBalance = useBalanceCheckbox.checked
      ? Math.min(customerBalance, originalGrandTotal)
      : 0;
    const minimumPayment = (originalGrandTotal - deductedBalance) * 0.5;

    if (
      !validatePayment(
        amountTendered,
        originalGrandTotal - deductedBalance,
        minimumPayment,
      )
    ) {
      return false; // Return false to prevent form submission
    }

    // Save current order data before submission
    saveCurrentOrderData(currentOrderIndex);

    // Collect and filter order data from orders array
    const ordersData = [];
    console.log("Number of orders in array:", orders.length);

    orders.forEach((order) => {
      if (order.data && Object.keys(order.data).length > 0) {
        // Convert saved data format to the expected format, passing the order object
        const orderData = convertSavedDataToOrderData(order.data, order);
        if (orderData) {
          ordersData.push(orderData);
        }
      }
    });
    
    console.log("Number of orders collected:", ordersData.length);

    if (ordersData.length === 0) {
      Swal.fire({
        title: "No Valid Orders",
        text: "Please add at least one valid order with detergent selected.",
        icon: "warning",
        confirmButtonText: "OK",
        background: "#fff",
        customClass: {
          popup: "rounded-3",
          confirmButton: "btn btn-primary custom-swal-button",
        },
        buttonsStyling: false,
      });
      return false; // Return false to prevent form submission
    }

    // Prepare form data with adjusted total and deducted balance
    const formData = {
      customer_type: customerType,
      customer_id: customerType === "registered" ? customerIdField.value : null,
      customer_name:
        customerType === "walk_in" ? customerNameField.value : null,
      customer_phone:
        customerType === "walk_in" ? customerPhoneField.value : null,
      grand_total: grandTotal,
      amount_tendered: amountTendered,
      change: parseFloat(changeField.value.replace("₱", "")) || 0,
      use_balance: useBalanceCheckbox.checked,
      balance_used: deductedBalance,
      orders: ordersData,
    };

    // Update hidden field with orders data
    if (ordersDataField) {
      ordersDataField.value = JSON.stringify(formData);
    }

    // SUBMIT THE FORM - Remove the preventDefault and actually submit
    document.getElementById("orderForm").submit();
    return true; // Return true to allow form submission
  }

  // Validate customer information with SweetAlert2
  function validateCustomerInfo(customerType) {
    if (customerType === "registered") {
      if (!customerIdField.value.trim()) {
        Swal.fire({
          title: "Customer Required",
          text: "Please select a customer from the list.",
          icon: "warning",
          confirmButtonText: "OK",
          background: "#fff",
          customClass: {
            popup: "rounded-3",
            confirmButton: "btn btn-primary custom-swal-button",
          },
          buttonsStyling: false,
        });
        return false;
      }
    } else if (customerType === "walk_in") {
      const name = customerNameField.value.trim();
      const phone = customerPhoneField.value.trim();

      if (!name) {
        Swal.fire({
          title: "Name Required",
          text: "Please enter the customer's name.",
          icon: "warning",
          confirmButtonText: "OK",
          background: "#fff",
          customClass: {
            popup: "rounded-3",
            confirmButton: "btn btn-primary custom-swal-button",
          },
          buttonsStyling: false,
        });
        return false;
      }

      if (!phone) {
        Swal.fire({
          title: "Phone Number Required",
          text: "Please enter the customer's phone number.",
          icon: "warning",
          confirmButtonText: "OK",
          background: "#fff",
          customClass: {
            popup: "rounded-3",
            confirmButton: "btn btn-primary custom-swal-button",
          },
          buttonsStyling: false,
        });
        return false;
      }

      // Validate Philippine phone number format
      const phoneRegex = /^09\d{9}$/;
      if (!phoneRegex.test(phone)) {
        Swal.fire({
          title: "Invalid Phone Number",
          text: "Please enter a valid 11-digit Philippine phone number starting with 09.",
          icon: "error",
          confirmButtonText: "OK",
          background: "#fff",
          customClass: {
            popup: "rounded-3",
            confirmButton: "btn btn-primary custom-swal-button",
          },
          buttonsStyling: false,
        });
        return false;
      }

      // Check if phone number already exists
      if (EXISTING_PHONES.includes(phone)) {
        Swal.fire({
          title: "Phone Number Exists",
          text: "This phone number is already registered to a customer. Please use a different number.",
          icon: "error",
          confirmButtonText: "OK",
          background: "#fff",
          customClass: {
            popup: "rounded-3",
            confirmButton: "btn btn-primary custom-swal-button",
          },
          buttonsStyling: false,
        });
        return false;
      }
    }

    return true;
  }

  function convertSavedDataToOrderData(savedData, orderObject) {
    // Validate that detergent is selected
    if (!savedData.detergentProduct || !savedData.detergentQuantity || parseInt(savedData.detergentQuantity) < 1) {
      return null; // Skip invalid orders
    }

    const orderData = {
      rounds_of_wash: parseInt(savedData.roundsOfWash) || 1,
      dryer_preference: parseInt(savedData.dryerPreference) || 0,
      folding_service: savedData.foldingService || false,
      separate_whites: savedData.separateWhites || false,
      is_whites_order: orderObject.isWhitesOrder || false, // Mark if this is a whites order
      original_total: savedData.orderTotal || 0, // Use the stored order total
      products: [],
      items: {
        tops: parseInt(savedData.clothingTops) || 0,
        bottoms: parseInt(savedData.clothingBottoms) || 0,
        undergarments: parseInt(savedData.clothingUndergarments) || 0,
        delicates: parseInt(savedData.clothingDelicates) || 0,
        linens: parseInt(savedData.clothingLinens) || 0,
        curtains_drapes: parseInt(savedData.clothingCurtains) || 0,
        blankets_comforters: parseInt(savedData.clothingBlankets) || 0,
        others: parseInt(savedData.clothingOthers) || 0,
      },
    };

    // Get product prices from the DOM select elements (they have data-price attributes)
    const orderElement = document.querySelector(".order-item");
    if (orderElement) {
      // Add detergent
      const detergentSelect = orderElement.querySelector(".detergent-product");
      const detergentOption = detergentSelect?.querySelector(`option[value="${savedData.detergentProduct}"]`);
      orderData.products.push({
        type: "detergent",
        product_id: savedData.detergentProduct,
        quantity: parseInt(savedData.detergentQuantity),
        unit_price: parseFloat(detergentOption?.dataset.price || 0),
      });

      // Add fabric conditioner if present
      if (savedData.fabconProduct && parseInt(savedData.fabconQuantity) > 0) {
        const fabconSelect = orderElement.querySelector(".fabcon-product");
        const fabconOption = fabconSelect?.querySelector(`option[value="${savedData.fabconProduct}"]`);
        orderData.products.push({
          type: "fabric_conditioner",
          product_id: savedData.fabconProduct,
          quantity: parseInt(savedData.fabconQuantity),
          unit_price: parseFloat(fabconOption?.dataset.price || 0),
        });
      }

      // Add bleach if present
      if (savedData.bleachProduct && parseInt(savedData.bleachQuantity) > 0) {
        const bleachSelect = orderElement.querySelector(".bleach-product");
        const bleachOption = bleachSelect?.querySelector(`option[value="${savedData.bleachProduct}"]`);
        orderData.products.push({
          type: "bleach",
          product_id: savedData.bleachProduct,
          quantity: parseInt(savedData.bleachQuantity),
          unit_price: parseFloat(bleachOption?.dataset.price || 0),
        });
      }
    }

    return orderData;
  }

  function collectOrderData(orderElement) {
    // Validate required fields first
    const detergentSelect = orderElement.querySelector(".detergent-product");
    const scoopsInput = orderElement.querySelector(".scoops-of-detergent");

    // Detergent is required
    if (!detergentSelect.value || parseInt(scoopsInput.value) < 1) {
      return null; // Skip invalid orders
    }

    const orderData = {
      order_number: parseInt(orderElement.dataset.orderNumber),
      rounds_of_wash:
        parseInt(orderElement.querySelector(".rounds-of-wash").value) || 1,
      dryer_preference:
        parseInt(orderElement.querySelector(".dryer-preference").value) || 0,
      folding_service: orderElement.querySelector(".folding-service").checked,
      separate_whites: orderElement.querySelector(".separate-whites").checked,
      original_total:
        parseFloat(orderElement.querySelector(".order-total").textContent) || 0,
      products: [],

      items: {
        tops: parseInt(orderElement.querySelector(".clothing-tops").value) || 0,
        bottoms:
          parseInt(orderElement.querySelector(".clothing-bottoms").value) || 0,
        undergarments:
          parseInt(
            orderElement.querySelector(".clothing-undergarments").value,
          ) || 0,
        delicates:
          parseInt(orderElement.querySelector(".clothing-delicates").value) ||
          0,
        linens:
          parseInt(orderElement.querySelector(".clothing-linens").value) || 0,
        curtains_drapes:
          parseInt(
            orderElement.querySelector(".clothing-curtains-drapes").value,
          ) || 0,
        blankets_comforters:
          parseInt(
            orderElement.querySelector(".clothing-blankets-comforters").value,
          ) || 0,
        others:
          parseInt(orderElement.querySelector(".clothing-others").value) || 0,
      },
    };

    // Collect detergent data (required)
    orderData.products.push({
      type: "detergent",
      product_id: detergentSelect.value,
      quantity: parseInt(scoopsInput.value),
      unit_price: parseFloat(scoopsInput.dataset.price) || 0,
    });

    // Collect optional products only if they have values
    const fabconSelect = orderElement.querySelector(".fabcon-product");
    const fabconInput = orderElement.querySelector(".fabcon-cups");
    if (fabconSelect.value && parseInt(fabconInput.value) > 0) {
      orderData.products.push({
        type: "fabric_conditioner",
        product_id: fabconSelect.value,
        quantity: parseInt(fabconInput.value),
        unit_price: parseFloat(fabconInput.dataset.price) || 0,
      });
    }

    const bleachSelect = orderElement.querySelector(".bleach-product");
    const bleachInput = orderElement.querySelector(".bleach-cups");
    if (bleachSelect.value && parseInt(bleachInput.value) > 0) {
      orderData.products.push({
        type: "bleach",
        product_id: bleachSelect.value,
        quantity: parseInt(bleachInput.value),
        unit_price: parseFloat(bleachInput.dataset.price) || 0,
      });
    }

    return orderData;
  }

  // Update the initializeCustomerSelect2 function to add a placeholder
  function initializeCustomerSelect2() {
    const customerSelect = $("#customer_id");

    if (customerSelect.length) {
      // Initialize Select2
      customerSelect.select2({
        theme: "bootstrap-5",
        placeholder: "Search and select customer...",
        allowClear: true,
        width: "100%",
        templateResult: formatCustomer,
        templateSelection: formatCustomerSelection,
        matcher: customMatcher,
        dropdownParent: $("#createLaundryModal"), // Ensure dropdown appears within modal
        language: {
          searching: function () {
            return "Searching...";
          },
          noResults: function () {
            return "No customers found";
          },
          inputTooShort: function (args) {
            var remainingChars = args.minimum - args.input.length;
            return (
              "Please enter " +
              remainingChars +
              " more character" +
              (remainingChars > 1 ? "s" : "")
            );
          },
        },
      });

      // Set the placeholder text initially
      customerSelect
        .data("select2")
        .$dropdown.find(".select2-search__field")
        .attr("placeholder", "Type name or phone number...");

      // Handle selection change
      customerSelect.on("select2:select", function (e) {
        updateCustomerInfo(e.params.data);
      });

      // Handle clearing selection
      customerSelect.on("select2:clear", function (e) {
        clearCustomerInfo();
      });

      // Update placeholder when dropdown opens
      customerSelect.on("select2:open", function (e) {
        setTimeout(function () {
          const searchField = $(".select2-search__field");
          if (searchField.length) {
            searchField.attr("placeholder", "Type name or phone number...");
          }
        }, 100);
      });
    }
  }

  // Custom formatter for dropdown options
  function formatCustomer(customer) {
    if (!customer.id) {
      return customer.text;
    }

    const text = customer.text;
    const match = text.match(/\[(.*?)\]\s*(.*?)\s*-\s*(.*)$/);

    if (match) {
      const [, type, name, phone] = match;
      const badgeClass =
        type.toLowerCase() === "registered"
          ? "badge-registered"
          : "badge-walkin";

      return $(`
              <div class="customer-option">
                  <div class="customer-name">
                      <span class="customer-type-badge ${badgeClass}">${type}</span>
                      ${name}
                  </div>
                  <div class="customer-details">${phone}</div>
              </div>
          `);
    }

    return $("<span>" + customer.text + "</span>");
  }

  // Custom formatter for selected option
  function formatCustomerSelection(customer) {
    if (!customer.id) {
      return customer.text;
    }

    const text = customer.text;
    const match = text.match(/\[(.*?)\]\s*(.*?)\s*-\s*(.*)$/);

    if (match) {
      const [, type, name] = match;
      return `[${type}] ${name}`;
    }

    return customer.text;
  }

  // Custom matcher for better search
  function customMatcher(params, data) {
    // If there are no search terms, return all data
    if ($.trim(params.term) === "") {
      return data;
    }

    // Skip if there is no 'text' property
    if (typeof data.text === "undefined") {
      return null;
    }

    // Search in customer name and phone number
    const searchTerm = params.term.toLowerCase();
    const customerText = data.text.toLowerCase();

    // Extract name and phone from the text
    const match = customerText.match(/\[(.*?)\]\s*(.*?)\s*-\s*(.*)$/);
    if (match) {
      const [, type, name, phone] = match;
      if (
        name.includes(searchTerm) ||
        phone.includes(searchTerm) ||
        type.includes(searchTerm)
      ) {
        return data;
      }
    } else if (customerText.includes(searchTerm)) {
      return data;
    }

    return null;
  }

  // Update customer information when selected
  function updateCustomerInfo(customerData) {
    const element = customerData.element;
    const contactNum = $(element).data("contact_num") || "N/A";
    const balance = parseFloat($(element).data("balance") || 0);

    // Update phone number field
    $("#registered_customer_phone").val(contactNum);

    // Update balance display and checkbox
    const customerBalanceSpan = $("#customer_balance");
    const useBalanceCheckbox = $("#use_balance");

    if (customerBalanceSpan.length) {
      customerBalanceSpan.text(balance.toFixed(2));

      if (balance > 0) {
        useBalanceCheckbox.prop("disabled", false);
        useBalanceCheckbox.css({
          opacity: "1",
          filter: "none",
        });
      } else {
        useBalanceCheckbox.prop("disabled", true);
        useBalanceCheckbox.prop("checked", false);
        useBalanceCheckbox.css({
          opacity: "0.5",
          filter: "grayscale(100%)",
        });
      }
    }
  }

  // Validate phone number in real-time (visual feedback only)
  function validatePhoneNumber(input) {
    const phone = input.value.trim();
    const feedback = document.getElementById("phone-feedback");

    // Remove all non-digit characters
    const cleanedPhone = phone.replace(/\D/g, "");
    input.value = cleanedPhone;

    // Validate Philippine phone number format (09XXXXXXXXX)
    const phoneRegex = /^09\d{9}$/;
    const isValidFormat = phoneRegex.test(cleanedPhone);

    // Check if phone number already exists
    const phoneExists = EXISTING_PHONES.includes(cleanedPhone);

    // Update validation styling (visual feedback only, no alerts)
    if (cleanedPhone === "") {
      input.classList.remove("is-valid", "is-invalid");
      if (feedback) {
        feedback.style.display = "none";
      }
    } else if (!isValidFormat) {
      input.classList.remove("is-valid");
      input.classList.add("is-invalid");
      if (feedback) {
        feedback.textContent =
          "Please enter a valid 11-digit Philippine phone number starting with 09.";
        feedback.style.display = "block";
      }
    } else if (phoneExists) {
      input.classList.remove("is-valid");
      input.classList.add("is-invalid");
      if (feedback) {
        feedback.textContent =
          "This phone number is already registered. Please use a different number.";
        feedback.style.display = "block";
      }
    } else {
      input.classList.remove("is-invalid");
      input.classList.add("is-valid");
      if (feedback) feedback.style.display = "none";
    }

    return isValidFormat && !phoneExists;
  }

  initModal();
});