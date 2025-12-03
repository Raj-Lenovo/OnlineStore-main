<?php
require_once 'includes/config.php';
require_once 'includes/image-upload.php';

$pageTitle = 'Home';

$pdo = getDB();

// Get featured products (latest 6 products)
$stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     ORDER BY p.created_at DESC LIMIT 6");
$featuredProducts = $stmt->fetchAll();

// Get categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="hero-section bg-primary text-white text-center py-5 mb-5 rounded">
    <div class="container">
        <h1 class="display-4">Welcome to <?php echo SITE_NAME; ?></h1>
        <p class="lead">Your one-stop shop for quality computer products and accessories</p>
        <a href="products.php" class="btn btn-light btn-lg mt-3">Browse Products</a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <h2>Shop by Category</h2>
    </div>
    <?php foreach ($categories as $category): ?>
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><?php echo h($category['name']); ?></h5>
                <p class="card-text"><?php echo h($category['description'] ?? ''); ?></p>
                <a href="products.php?category=<?php echo $category['id']; ?>" class="btn btn-primary">View Products</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row">
    <div class="col-12">
        <h2>Featured Products</h2>
    </div>
    <?php if (empty($featuredProducts)): ?>
    <div class="col-12">
        <p class="text-muted">No products available yet.</p>
    </div>
    <?php else: ?>
    <?php foreach ($featuredProducts as $product): ?>
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <?php if (!empty($product['image'])): ?>
            <img src="<?php echo getImageUrl($product['image']); ?>" 
                 alt="<?php echo h($product['name']); ?>" 
                 class="card-img-top" 
                 style="height: 200px; object-fit: cover;">
            <?php else: ?>
            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                <span class="text-muted">No Image</span>
            </div>
            <?php endif; ?>
            <div class="card-body">
                <h5 class="card-title"><?php echo h($product['name']); ?></h5>
                <p class="card-text text-muted"><?php echo h($product['category_name'] ?? 'Uncategorized'); ?></p>
                <p class="card-text"><strong><?php echo formatPrice($product['price']); ?></strong></p>
                <p class="card-text">
                    <small class="text-<?php echo $product['stock'] > 0 ? 'success' : 'danger'; ?>">
                        <?php echo $product['stock'] > 0 ? 'In Stock (' . $product['stock'] . ')' : 'Out of Stock'; ?>
                    </small>
                </p>
            </div>
            <div class="card-footer">
                <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn btn-primary w-100">View Details</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="text-center mt-4">
    <a href="products.php" class="btn btn-outline-primary btn-lg">View All Products</a>
</div>

<?php include 'includes/footer.php'; ?>

