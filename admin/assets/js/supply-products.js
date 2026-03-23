function populateForm(id, categoryId, name, measurement, price, maxUnit, description) {
    document.getElementById('productId').value = id;
    document.getElementById('categoryId').value = categoryId;
    document.getElementById('productName').value = name;
    document.getElementById('productMeasurement').value = measurement || '';
    document.getElementById('productPrice').value = price;
    document.getElementById('maxUnitPerContainer').value = maxUnit;
    document.getElementById('productDescription').value = description || '';
    
    document.querySelector('.card').scrollIntoView({ behavior: 'smooth' });
}

function clearForm() {
    document.getElementById('productId').value = '';
    document.getElementById('categoryId').value = '';
    document.getElementById('productName').value = '';
    document.getElementById('productMeasurement').value = '';
    document.getElementById('productPrice').value = '';
    document.getElementById('maxUnitPerContainer').value = '';
    document.getElementById('productDescription').value = '';
}