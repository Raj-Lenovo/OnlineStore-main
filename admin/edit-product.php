<?php
require_once '../includes/config.php';
require_once '../includes/image-upload.php';

requireAdmin();

$pageTitle = 'Edit Product';

$pdo = getDB();

$product_id = intval($_GET['id'] ?? 0);

if (!$product_id) {
    $_SESSION['error'] = 'Invalid product ID.';
    redirect('dashboard.php');
}

// Get product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['error'] = 'Product not found.';
    redirect('dashboard.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $category_id = intval($_POST['category_id'] ?? 0);
    $image = $product['image']; // Keep existing image by default
    
    // Handle image upload
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        // Delete old image if exists
        if (!empty($product['image'])) {
            deleteImage($product['image']);
        }
        
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
            $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category_id = ?, image = ? WHERE id = ?");
            $stmt->execute([$name, $description, $price, $stock, $category_id ?: null, $image ?: null, $product_id]);
            $_SESSION['success'] = 'Product updated successfully.';
            redirect('dashboard.php');
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Failed to update product: ' . $e->getMessage();
        }
    }
    
    // Reload product data
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
}

// Get categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

include '../includes/header.php';
?>

<h2>Edit Product</h2>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name *</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo h($product['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5"><?php echo h($product['description']); ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="price" class="form-label">Price *</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" value="<?php echo $product['price']; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="stock" class="form-label">Stock</label>
                                <input type="number" class="form-control" id="stock" name="stock" min="0" value="<?php echo $product['stock']; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo $product['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo h($category['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <?php if (!empty($product['image'])): ?>
                        <div class="mb-2">
                            <label class="form-label">Current Image</label>
                            <div>
                                <img src="<?php echo getImageUrl($product['image']); ?>" alt="Current product image" class="img-thumbnail" style="max-height: 150px;">
                            </div>
                        </div>
                        <?php endif; ?>
                        <label for="image_file" class="form-label">Upload New Image</label>
                        <input type="file" class="form-control" id="image_file" name="image_file" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                        <small class="form-text text-muted">Max file size: 5MB. Allowed formats: JPEG, PNG, GIF, WebP</small>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Or Enter Image Path</label>
                        <input type="text" class="form-control" id="image" name="image" value="<?php echo h($product['image']); ?>" placeholder="e.g., assets/images/products/product1.jpg">
                        <small class="form-text text-muted">Leave blank if uploading a file above</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Product</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

