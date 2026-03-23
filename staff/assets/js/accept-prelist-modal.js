document.addEventListener('DOMContentLoaded', function () {
    // DOM Elements
    const acceptButtons = document.querySelectorAll('.accept-prelist-btn');
    const modal = document.getElementById('acceptPrelistModal');
    const acceptOrderButton = modal.querySelector('button[type="submit"]');
    const accommodatedByElement = document.getElementById('accept_accommodated_by');
    const queueNumberBadge = document.getElementById('accept_queue_number_badge');

    // Form elements
    const formElements = {
        prelistId: document.getElementById('accept_prelist_id'),
        customerType: document.getElementById('accept_customer_type'),
        customerId: document.getElementById('accept_customer_id'),
        customerNameHidden: document.getElementById('accept_customer_name_hidden'),
        customerPhoneHidden: document.getElementById('accept_customer_phone_hidden'),
        customerName: document.getElementById('accept_customer_name'),
        customerPhone: document.getElementById('accept_customer_phone'),
        roundsOfWash: document.getElementById('accept_rounds_of_wash'),
        scoopsOfDetergent: document.getElementById('accept_scoops_of_detergent'),
        dryerPreference: document.getElementById('accept_dryer_preference'),
        foldingService: document.getElementById('accept_folding_service'),
        fabconCups: document.getElementById('accept_fabcon_cups'),
        bleachCups: document.getElementById('accept_bleach_cups'),
        detergentProduct: document.getElementById('accept_detergent_product'),
        fabconProduct: document.getElementById('accept_fabcon_product'),
        bleachProduct: document.getElementById('accept_bleach_product'),
        remarks: document.getElementById('accept_remarks'),
        paymentStatus: document.getElementById('accept_payment_status'),
        amountTendered: document.getElementById('accept_amount_tendered'),
        totalPrice: document.getElementById('accept_total_price'),
        change: document.getElementById('accept_change'),
        tops: document.getElementById('accept_tops'),
        bottoms: document.getElementById('accept_bottoms'),
        undergarments: document.getElementById('accept_undergarments'),
        delicates: document.getElementById('accept_delicates'),
        linens: document.getElementById('accept_linens'),
        curtainsDrapes: document.getElementById('accept_curtains_drapes'),
        blanketsComforters: document.getElementById('accept_blankets_comforters'),
        others: document.getElementById('accept_others')
    };

    // Initialize modal
    function initModal() {
        setupAcceptButtons();
        setupEventListeners();
    }

    // Set up accept buttons
    function setupAcceptButtons() {
        acceptButtons.forEach(button => {
            button.addEventListener('click', function() {
                const data = {
                    id: this.getAttribute('data-id') || '',
                    customerName: this.getAttribute('data-customer-name') || 'N/A',
                    customerId: this.getAttribute('data-customer-id') || 'N/A',
                    customerType: this.getAttribute('data-customer-type') || 'Registered',
                    customerPhone: this.getAttribute('data-customer-phone') || 'N/A',
                    roundsOfWash: this.getAttribute('data-rounds-of-wash') || '1',
                    scoopsOfDetergent: this.getAttribute('data-scoops-of-detergent') || '0',
                    dryerPreference: this.getAttribute('data-dryer-preference') || '0',
                    foldingService: this.getAttribute('data-folding-service') || '0',
                    fabconCups: this.getAttribute('data-fabcon-cups') || '0',
                    bleachCups: this.getAttribute('data-bleach-cups') || '0',
                    detergent_product_id: this.getAttribute('data-detergent-product-id') || '',
                    fabcon_product_id: this.getAttribute('data-fabcon-product-id') || '',
                    bleach_product_id: this.getAttribute('data-bleach-product-id') || '',
                    totalPrice: this.getAttribute('data-total-price') || '0.00',
                    adjustedTotalPrice: this.getAttribute('data-adjusted-total-price') || '0.00',
                    deductedBalance: this.getAttribute('data-deducted-balance') || '0.00',
                    remarks: this.getAttribute('data-remarks') || '',
                    accommodatedBy: this.getAttribute('data-accommodated-by') || 'N/A',
                    queueNumber: this.getAttribute('data-queue-number') || 'N/A',
                    tops: this.getAttribute('data-tops') || '0',
                    bottoms: this.getAttribute('data-bottoms') || '0',
                    undergarments: this.getAttribute('data-undergarments') || '0',
                    delicates: this.getAttribute('data-delicates') || '0',
                    linens: this.getAttribute('data-linens') || '0',
                    curtains_drapes: this.getAttribute('data-curtains-drapes') || '0',
                    blankets_comforters: this.getAttribute('data-blankets-comforters') || '0',
                    others: this.getAttribute('data-others') || '0'
                };

                populateForm(data);
                accommodatedByElement.textContent = data.accommodatedBy;
                queueNumberBadge.textContent = `#${data.queueNumber}`;
                calculateChange(parseFloat(data.adjustedTotalPrice));
            });
        });
    }

    // Populate form with prelist data
    function populateForm(data) {
        formElements.prelistId.value = data.id;
        formElements.customerType.value = data.customerType === 'Registered' ? 'registered' : 'walk_in';
        formElements.customerId.value = data.customerId;
        formElements.customerNameHidden.value = data.customerName;
        formElements.customerPhoneHidden.value = data.customerPhone;
        formElements.customerName.value = data.customerName;
        formElements.customerPhone.value = data.customerPhone;
        formElements.roundsOfWash.value = data.roundsOfWash;
        formElements.scoopsOfDetergent.value = data.scoopsOfDetergent;
        formElements.dryerPreference.value = data.dryerPreference === '0' ? 'No Drying' : (data.dryerPreference === '1' ? '1 round' : '2 rounds');
        formElements.foldingService.checked = data.foldingService === '1';
        formElements.fabconCups.value = data.fabconCups;
        formElements.bleachCups.value = data.bleachCups;

        // Fetch product names from PRODUCTS object
        const getProductName = (productId) => {
            for (let category in PRODUCTS) {
                const product = PRODUCTS[category].find(p => p.id === parseInt(productId));
                if (product) return `${product.name} ${product.measurement ? '- ' + product.measurement : ''} (₱${product.unit_price.toFixed(2)})`;
            }
            return 'N/A';
        };

        formElements.detergentProduct.value = data.detergent_product_id ? getProductName(data.detergent_product_id) : 'N/A';
        formElements.fabconProduct.value = data.fabcon_product_id ? getProductName(data.fabcon_product_id) : 'N/A';
        formElements.bleachProduct.value = data.bleach_product_id ? getProductName(data.bleach_product_id) : 'N/A';
        formElements.remarks.value = data.remarks;
        formElements.totalPrice.value = data.adjustedTotalPrice;
        formElements.totalPrice.dataset.deductedBalance = data.deductedBalance;

        // Populate item quantities
        formElements.tops.value = data.tops;
        formElements.bottoms.value = data.bottoms;
        formElements.undergarments.value = data.undergarments;
        formElements.delicates.value = data.delicates;
        formElements.linens.value = data.linens;
        formElements.curtainsDrapes.value = data.curtains_drapes;
        formElements.blanketsComforters.value = data.blankets_comforters;
        formElements.others.value = data.others;
    }

    // Calculate change based on amount tendered
    function calculateChange(totalPrice) {
        const amountTendered = parseFloat(formElements.amountTendered.value) || 0;
        const change = Math.max(0, amountTendered - totalPrice);
        formElements.change.value = change.toFixed(2);

        const minimumPayment = totalPrice * 0.5;
        acceptOrderButton.disabled = amountTendered < minimumPayment;
        formElements.paymentStatus.value = amountTendered >= totalPrice ? 'Paid' : 'Unpaid';
    }

    // Set up event listeners
    function setupEventListeners() {
        formElements.amountTendered.addEventListener('input', () => {
            calculateChange(parseFloat(formElements.totalPrice.value) || 0);
        });

        // Form submission validation
        const form = document.getElementById('acceptPrelistModal').querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const totalPrice = parseFloat(formElements.totalPrice.value) || 0;
                const amountTendered = parseFloat(formElements.amountTendered.value) || 0;
                const minimumPayment = totalPrice * 0.5;

                if (amountTendered < minimumPayment) {
                    e.preventDefault();
                    alert(`Insufficient payment. Minimum required: ₱${minimumPayment.toFixed(2)}`);
                    return false;
                }
            });
        }
    }

    // Initialize the modal
    initModal();
});