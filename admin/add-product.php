<?php
require_once '../includes/config.php';
require_once '../includes/image-upload.php';

requireAdmin();

$pageTitle = 'Add Product';

$pdo = getDB();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = floatval($_POST['price'] ?? 0);
    $stock       = intval($_POST['stock'] ?? 0);
    $category_id = intval($_POST['category_id'] ?? 0);
    $image       = '';

    // Handle image upload
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadImage($_FILES['image_file']);
        if ($uploadResult['success']) {
            $image = $uploadResult['filename'];
        } else {
            $_SESSION['error'] = $uploadResult['message'];
        }
    } elseif (!empty($_POST['image'])) {
        // Allow manual image path entry
        $image = trim($_POST['image']);
    }

    if (empty($name) || $price <= 0) {
        $_SESSION['error'] = 'Product name and valid price are required.';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO products (name, description, price, stock, category_id, image)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $name,
                $description,
                $price,
                $stock,
                $category_id ?: null,
                $image ?: null
            ]);

            $_SESSION['success'] = 'Product added successfully.';
            redirect('dashboard.php');
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Failed to add product: ' . $e->getMessage();
        }
    }
}

// Get categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

/**
 * Map category names to keywords.
 * Edit this array to match YOUR categories.
 * Keys = category name in DB
 * Value = comma-separated keywords to look for in product name
 */
$categoryKeywordMap = [
    'Laptops'   => 'laptop,notebook',
    'Desktops'  => 'desktop,pc,tower',
    'Monitors'  => 'monitor,screen,display',
    'Keyboards' => 'keyboard',
    'Mice'      => 'mouse,wireless mouse,gaming mouse',
    // add more as needed...
];

include '../includes/header.php';
?>

<h2>Add New Product</h2>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="price" class="form-label">Price *</label>
                                <input type="number" class="form-control" id="price" name="price"
                                       step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="stock" class="form-label">Stock</label>
                                <input type="number" class="form-control" id="stock" name="stock"
                                       min="0" value="0">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <?php
                                $catName  = $category['name'];
                                $keywords = $categoryKeywordMap[$catName] ?? '';
                                ?>
                                <option
                                    value="<?php echo $category['id']; ?>"
                                    data-keywords="<?php echo h($keywords); ?>"
                                >
                                    <?php echo h($catName); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">
                            Category will auto-select based on product name, but you can still change it manually.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="image_file" class="form-label">Upload Product Image</label>
                        <input type="file" class="form-control" id="image_file" name="image_file"
                               accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                        <small class="form-text text-muted">
                            Max file size: 5MB. Allowed formats: JPEG, PNG, GIF, WebP
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Or Enter Image Path</label>
                        <input type="text" class="form-control" id="image" name="image"
                               placeholder="e.g., assets/images/products/product1.jpg">
                        <small class="form-text text-muted">Leave blank if uploading a file above.</small>
                    </div>

                    <button type="submit" class="btn btn-primary">Add Product</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Simple JS: auto-select category based on product name -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const nameInput     = document.getElementById('name');
    const categorySelect = document.getElementById('category_id');

    if (!nameInput || !categorySelect) return;

    nameInput.addEventListener('input', function () {
        const text = nameInput.value.toLowerCase();
        if (!text) {
            return;
        }

        let matchedValue = '';

        // Loop through options and match against data-keywords OR category text
        categorySelect.querySelectorAll('option').forEach(function (opt) {
            const keywordsAttr = (opt.getAttribute('data-keywords') || '').toLowerCase();
            const optionText   = opt.textContent.toLowerCase();

            const keywords = keywordsAttr
                ? keywordsAttr.split(',').map(k => k.trim()).filter(Boolean)
                : [];

            // Always also include the category name itself as a keyword
            if (optionText) {
                keywords.push(optionText);
            }

            // Check if any keyword is contained in the product name
            const match = keywords.some(function (kw) {
                return kw && text.includes(kw);
            });

            if (match) {
                matchedValue = opt.value;
            }
        });

        if (matchedValue) {
            categorySelect.value = matchedValue;
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>
