document.addEventListener('DOMContentLoaded', function() {
    // Handle product selection
    const productSelectors = document.querySelectorAll('.product-selector');
    
    productSelectors.forEach(selector => {
        selector.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const card = this.closest('.card');
            const priceSection = card.querySelector('.price-input-section');
            const currentPriceDisplay = card.querySelector('.current-price-display');
            const newPriceInput = card.querySelector('.new-price-input');
            const unitTypeDisplays = card.querySelectorAll('.unit-type-display');
            const containerPriceDisplay = card.querySelector('.container-price-display');
            const maxUnitsNumber = card.querySelector('.max-units-number');
            const unitTypeText = card.querySelector('.unit-type-text');
            
            if (this.value) {
                const currentPrice = selectedOption.dataset.currentPrice;
                const containerPrice = selectedOption.dataset.containerPrice;
                const unitType = selectedOption.dataset.unitType;
                const maxUnits = selectedOption.dataset.maxUnits;
                
                // Show price section
                priceSection.style.display = 'block';
                
                // Set current unit price
                currentPriceDisplay.value = parseFloat(currentPrice).toFixed(2);
                
                // Update unit type displays
                unitTypeDisplays.forEach(display => {
                    display.textContent = 'per ' + unitType;
                });
                
                // Update container info
                containerPriceDisplay.textContent = parseFloat(containerPrice).toFixed(2);
                maxUnitsNumber.textContent = maxUnits || '0';
                unitTypeText.textContent = unitType + 's';
                
                // Set the name attribute for the new price input
                newPriceInput.name = 'supply_prices[' + this.value + ']';
                newPriceInput.value = ''; // Clear previous value
                
            } else {
                // Hide price section
                priceSection.style.display = 'none';
                newPriceInput.name = '';
                newPriceInput.value = '';
            }
        });
    });
    
    // Add focus effects to input fields
    const priceInputs = document.querySelectorAll('input[type="number"]');
    priceInputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('shadow-sm');
        });
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('shadow-sm');
        });
    });
    
    // Format numbers on blur
    priceInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value && !this.readOnly) {
                const value = parseFloat(this.value);
                if (!isNaN(value)) {
                    this.value = value.toFixed(2);
                }
            }
        });
    });
});

function resetForm() {
    if (confirm('Are you sure you want to reset all changes?')) {
        // Reset all dropdowns
        document.querySelectorAll('.product-selector').forEach(selector => {
            selector.value = '';
            selector.dispatchEvent(new Event('change'));
        });
        
        // Reset form
        document.getElementById('priceForm').reset();
    }
}