<?php
session_start();
include 'req/staff-auth-check.php';

require_once '../config/db_conn.php';

function fetchSupplyCategories($conn)
{
    $stmt = $conn->query("SELECT * FROM supply_categories ORDER BY name");
    if ($stmt) {
        return $stmt->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

function fetchSupplyProducts($conn, $categoryId = null)
{
    $sql = "SELECT sp.*, sc.name as category_name 
            FROM supply_products sp 
            JOIN supply_categories sc ON sp.category_id = sc.id 
            WHERE sp.is_active = 1";

    if ($categoryId) {
        $sql .= " AND sp.category_id = " . intval($categoryId);
    }

    $sql .= " ORDER BY sc.name, sp.name";

    $stmt = $conn->query($sql);
    if ($stmt) {
        return $stmt->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

function fetchSupplyItems($conn)
{
    $stmt = $conn->query("SELECT * FROM supply_list");
    if ($stmt) {
        return $stmt->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

function renderAlert($type, $message)
{
    if (!empty($message)) {
        $icon = $type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
        $alertClass = $type === 'success' ? 'alert-success' : 'alert-error';
        $title = $type === 'success' ? 'Success' : 'Error';

        echo '
            <div class="alert ' . $alertClass . '" role="alert" data-auto-dismiss="4000">
                <i class="' . $icon . ' alert-icon"></i>
                <div class="alert-content">
                    <span class="alert-title">' . $title . '</span>
                    <span>' . htmlspecialchars($message) . '</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <div class="alert-progress"><div class="alert-progress-bar"></div></div>
            </div>';
    }
}

function generateSupplyTableRows($supplyProducts)
{
    $currentCategory = '';
    $index = 0;
    foreach ($supplyProducts as $product) {
        if ($currentCategory !== $product['category_name']) {
            $currentCategory = $product['category_name'];
            $index = 1; 
            echo '<tr class="table-secondary">';
            echo '<td colspan="7" class="fw-bold text-center">' . htmlspecialchars($currentCategory) . '</td>';
            echo '</tr>';
        } else {
            $index++;
        }
        echo '<tr>';
        echo '<td>' . $index . '</td>';
        $datePart = date("M d, Y", strtotime($product['created_at']));
        $timePart = date("h:i A", strtotime($product['created_at']));
        echo '<td class="date-cell text-center">' . $datePart . '<br/><span style="font-size: 0.9em; color: #666;">' . $timePart . '</span></td>';
        echo '<td class="text-center">' . htmlspecialchars($product['name']) . '</td>';
        echo '<td class="text-center">' . htmlspecialchars($product['measurement'] ?? 'N/A') . '</td>';
        echo '<td class="text-center">₱' . number_format($product['price'], 2) . '</td>';
        echo '<td class="text-center">' . htmlspecialchars($product['max_unit_per_container']) . '</td>';
        echo '<td>';
        echo '<div class="d-flex gap-2 justify-content-center">';
        echo '<button class="modern-action-btn" onclick="populateForm(' . $product['id'] . ', ' . $product['category_id'] . ', \'' . htmlspecialchars($product['name']) . '\', \'' . htmlspecialchars($product['measurement'] ?? '') . '\', ' . $product['price'] . ', ' . $product['max_unit_per_container'] . ', \'' . htmlspecialchars($product['description'] ?? '') . '\')" title="Edit Product">';
        echo '<i class="fas fa-edit"></i>';
        echo '</button>';
        if (stripos($product['category_name'], 'plastic bag') === false && stripos($product['name'], 'plastic bag') === false) {
            echo '<button type="button" class="modern-action-btn modern-action-btn-danger" onclick="confirmDeleteProduct(' . $product['id'] . ', \'' . htmlspecialchars(addslashes($product['name'])) . '\')" title="Delete Product">';
            echo '<i class="fas fa-trash-alt"></i>';
            echo '</a>';
        }
        echo '</div>';
        echo '</td>';
        echo '</tr>';
    }
    if (empty($supplyProducts)) {
        echo '<tr><td colspan="6" class="text-center">No products found.</td></tr>';
    }
}

$categories = fetchSupplyCategories($conn);
$categoryFilter = isset($_GET['category']) ? intval($_GET['category']) : null;
$supplyProducts = fetchSupplyProducts($conn, $categoryFilter);
$header = 'Supply Products Management';

$plasticBagCategoryId = null;
foreach ($categories as $category) {
    if (stripos($category['name'], 'plastic bag') !== false) {
        $plasticBagCategoryId = $category['id'];
        break;
    }
}

ob_start();
?>
<link href="assets/css/laundry-modals.css" rel="stylesheet">
<link href="assets/css/laundry-actions.css" rel="stylesheet">

<div class="container-fluid">
    <?php if (isset($_SESSION['success'])): ?>
        <?php renderAlert('success', $_SESSION['success']); ?>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <?php renderAlert('error', $_SESSION['error']); ?>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-header" style="background-color: var(--primary-color); color: white;">
                    <h5 class="mb-0" style="font-size: 1.5rem;">Supply Product Form</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="req/store-product.php" id="productForm">
                        <input type="hidden" name="id" id="productId">

                        <div class="mb-3">
                            <label for="categoryId" class="form-label" style="color: var(--primary-color);">Category</label>
                            <select class="form-select" id="categoryId" name="category_id" required onchange="checkPlasticBagCategory()">
                                <option value="" selected disabled>Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="productName" class="form-label" style="color: var(--primary-color);">Product Name</label>
                            <input
                                type="text"
                                class="form-control"
                                id="productName"
                                name="name"
                                required
                                oninput="checkPlasticBagProduct()">
                        </div>

                        <div class="mb-3">
                            <label for="productMeasurement" class="form-label" style="color: var(--primary-color);">Measurement (Optional)</label>
                            <input
                                type="text"
                                class="form-control"
                                id="productMeasurement"
                                name="measurement"
                                placeholder="e.g., 120 grams, 500ml, 1kg">
                        </div>

                        <div class="mb-3">
                            <label for="productPrice" class="form-label" style="color: var(--primary-color);">Price Per Container</label>
                            <input
                                type="number"
                                class="form-control"
                                id="productPrice"
                                name="price"
                                step="0.01"
                                min="0"
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="maxUnitPerContainer" class="form-label" style="color: var(--primary-color);">Max Unit Per Container</label>
                            <input
                                type="number"
                                class="form-control"
                                id="maxUnitPerContainer"
                                name="max_unit_per_container"
                                min="1"
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="productDescription" class="form-label" style="color: var(--primary-color);">Description (Optional)</label>
                            <textarea
                                class="form-control"
                                id="productDescription"
                                name="description"
                                rows="3"></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary" id="submitButton">
                                Save Product
                            </button>
                            <button type="reset" class="btn btn-secondary"
                                style="background: #6c757d; color: white; border-color: #6c757d;"
                                onclick="clearForm()">
                                Cancel
                            </button>
                        </div>

                        <div id="plasticBagWarning" class="alert alert-warning mt-3" style="display: none;">
                            <i class="fas fa-exclamation-triangle"></i>
                            Adding new plastic bag products is not allowed. You can only edit existing ones.
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center" style="background-color: var(--primary-color); color: white;">
                    <h5 class="mb-0" style="font-size: 1.5rem;">Supply Products</h5>
                    <div>
                        <select class="form-select form-select-sm" style="width: auto; background-color: var(--accent-color); color: white" onchange="window.location.href=this.value">
                            <option value="supply-list.php" <?php echo !isset($_GET['category']) ? 'selected' : ''; ?>>All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="supply-list.php?category=<?php echo $category['id']; ?>"
                                    <?php echo (isset($_GET['category']) && $_GET['category'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-bordered">
                            <thead style="background-color: var(--primary-color); color: white;">
                                <tr>
                                    <th>#</th>
                                    <th class="text-center">Created</th>
                                    <th class="text-center">Product Name</th>
                                    <th class="text-center">Measurement</th>
                                    <th class="text-center">Price Per Container</th>
                                    <th class="text-center">Max Unit Per Container</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php generateSupplyTableRows($supplyProducts); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$slot = ob_get_clean();
include '../layouts/app.php';
?>

<script src="assets/js/supply-products.js"></script>
<script src="../assets/js/jquery.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>

<script>
    const plasticBagCategoryId = <?php echo $plasticBagCategoryId ?: 'null'; ?>;
    let isEditing = false;

    function checkPlasticBagCategory() {
        const categorySelect = document.getElementById('categoryId');
        const productName = document.getElementById('productName').value.toLowerCase();
        const submitButton = document.getElementById('submitButton');
        const warningDiv = document.getElementById('plasticBagWarning');
        const productId = document.getElementById('productId').value;

        if (productId) {
            submitButton.disabled = false;
            warningDiv.style.display = 'none';
            return;
        }

        const isPlasticBagCategory = plasticBagCategoryId && categorySelect.value == plasticBagCategoryId;

        const isPlasticBagName = productName.includes('plastic bag');

        if (isPlasticBagCategory || isPlasticBagName) {
            submitButton.disabled = true;
            warningDiv.style.display = 'block';
        } else {
            submitButton.disabled = false;
            warningDiv.style.display = 'none';
        }
    }

    function checkPlasticBagProduct() {
        checkPlasticBagCategory();
    }

    function populateForm(id, categoryId, name, measurement, price, maxUnit, description) {

        document.getElementById('productId').value = id;
        document.getElementById('categoryId').value = categoryId;
        document.getElementById('productName').value = name;
        document.getElementById('productMeasurement').value = measurement || '';
        document.getElementById('productPrice').value = price;
        document.getElementById('maxUnitPerContainer').value = maxUnit;
        document.getElementById('productDescription').value = description || '';


        document.getElementById('submitButton').disabled = false;
        document.getElementById('plasticBagWarning').style.display = 'none';

        isEditing = true;


        document.getElementById('productForm').scrollIntoView({
            behavior: 'smooth'
        });
    }

    function clearForm() {

        document.getElementById('productForm').reset();
        document.getElementById('productId').value = '';
        document.getElementById('submitButton').disabled = false;
        document.getElementById('plasticBagWarning').style.display = 'none';
        isEditing = false;
    }

    document.addEventListener('DOMContentLoaded', function() {
        checkPlasticBagCategory();
    });
</script>