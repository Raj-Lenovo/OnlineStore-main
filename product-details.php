<?php
require_once 'includes/config.php';
require_once 'includes/image-upload.php';

$pageTitle = 'Product Details';

$pdo = getDB();

$product_id = intval($_GET['id'] ?? 0);

if (!$product_id) {
    $_SESSION['error'] = 'Invalid product ID.';
    redirect('products.php');
}

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.id as category_id FROM products p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       WHERE p.id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['error'] = 'Product not found.';
    redirect('products.php');
}

$pageTitle = $product['name'];

// Get reviews and average rating
$stmt = $pdo->prepare("SELECT r.*, u.username, u.full_name FROM reviews r 
                       INNER JOIN users u ON r.user_id = u.id 
                       WHERE r.product_id = ? 
                       ORDER BY r.created_at DESC");
$stmt->execute([$product_id]);
$reviews = $stmt->fetchAll();

// Calculate average rating
$stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM reviews WHERE product_id = ?");
$stmt->execute([$product_id]);
$ratingData = $stmt->fetch();
$avgRating = $ratingData['avg_rating'] ? round($ratingData['avg_rating'], 1) : 0;
$reviewCount = $ratingData['review_count'] ?? 0;

// Check if current user has reviewed this product
$userReview = null;
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT * FROM reviews WHERE product_id = ? AND user_id = ?");
    $stmt->execute([$product_id, $_SESSION['user_id']]);
    $userReview = $stmt->fetch();
}

include 'includes/header.php';
?>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body p-0">
                <?php if (!empty($product['image'])): ?>
                <img src="<?php echo getImageUrl($product['image']); ?>" 
                     alt="<?php echo h($product['name']); ?>" 
                     class="img-fluid w-100" 
                     style="max-height: 500px; object-fit: contain; background: #f8f9fa;">
                <?php else: ?>
                <div class="bg-light d-flex align-items-center justify-content-center" style="min-height: 400px;">
                    <div class="text-center text-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" fill="currentColor" class="bi bi-image" viewBox="0 0 16 16">
                            <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/>
                            <path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-12zm12 1a1 1 0 0 1 1 1v8.755l-3.276-3.276a.75.75 0 0 0-1.06 0L4 13.176V3a1 1 0 0 1 1-1h9z"/>
                        </svg>
                        <p class="mt-2">No Image Available</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <h1><?php echo h($product['name']); ?></h1>
        <p class="text-muted">
            <a href="products.php?category=<?php echo $product['category_id']; ?>" class="text-decoration-none">
                <?php echo h($product['category_name'] ?? 'Uncategorized'); ?>
            </a>
        </p>
        <h3 class="text-primary"><?php echo formatPrice($product['price']); ?></h3>
        
        <!-- Rating Display -->
        <?php if ($reviewCount > 0): ?>
        <div class="mt-2 mb-3">
            <div class="d-flex align-items-center">
                <div class="me-2">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <?php if ($i <= floor($avgRating)): ?>
                            <span class="text-warning">★</span>
                        <?php elseif ($i - 0.5 <= $avgRating): ?>
                            <span class="text-warning">☆</span>
                        <?php else: ?>
                            <span class="text-muted">★</span>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
                <span class="text-muted"><?php echo $avgRating; ?> (<?php echo $reviewCount; ?> <?php echo $reviewCount == 1 ? 'review' : 'reviews'; ?>)</span>
            </div>
        </div>
        <?php else: ?>
        <div class="mt-2 mb-3">
            <span class="text-muted">No reviews yet</span>
        </div>
        <?php endif; ?>
        
        <p class="mt-3">
            <span class="badge bg-<?php echo $product['stock'] > 0 ? 'success' : 'danger'; ?> fs-6">
                <?php echo $product['stock'] > 0 ? 'In Stock (' . $product['stock'] . ' available)' : 'Out of Stock'; ?>
            </span>
        </p>
        <hr>
        <h5>Description</h5>
        <p><?php echo nl2br(h($product['description'] ?: 'No description available.')); ?></p>
        
        <?php if ($product['stock'] > 0 && isLoggedIn()): ?>
        <form method="POST" action="add-to-cart.php" class="mt-4">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            <div class="row">
                <div class="col-md-4">
                    <label for="quantity" class="form-label">Quantity</label>
                    <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-lg mt-3">Add to Cart</button>
        </form>
        <?php elseif ($product['stock'] <= 0): ?>
        <button class="btn btn-secondary btn-lg mt-3" disabled>Out of Stock</button>
        <?php else: ?>
        <p class="text-muted mt-3">Please <a href="login.php">login</a> to add items to cart.</p>
        <?php endif; ?>
        
        <div class="mt-4">
            <a href="products.php" class="btn btn-outline-secondary">Back to Products</a>
        </div>
    </div>
</div>

<!-- Reviews Section -->
<div class="row mt-5">
    <div class="col-12">
        <h3>Customer Reviews</h3>
        
        <!-- Review Form (if logged in) -->
        <?php if (isLoggedIn()): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5><?php echo $userReview ? 'Update Your Review' : 'Write a Review'; ?></h5>
            </div>
            <div class="card-body">
                <form method="POST" action="submit-review.php">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <div class="mb-3">
                        <label class="form-label">Rating *</label>
                        <div class="rating-input">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" name="rating" id="rating<?php echo $i; ?>" value="<?php echo $i; ?>" 
                                   <?php echo ($userReview && $userReview['rating'] == $i) ? 'checked' : ''; ?> required>
                            <label for="rating<?php echo $i; ?>" class="star-label">★</label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="comment" class="form-label">Your Review</label>
                        <textarea class="form-control" id="comment" name="comment" rows="4" 
                                  placeholder="Share your experience with this product..."><?php echo $userReview ? h($userReview['comment']) : ''; ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo $userReview ? 'Update Review' : 'Submit Review'; ?></button>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <a href="login.php">Login</a> to write a review.
        </div>
        <?php endif; ?>
        
        <!-- Reviews List -->
        <?php if (empty($reviews)): ?>
        <div class="alert alert-info">
            <p>No reviews yet. Be the first to review this product!</p>
        </div>
        <?php else: ?>
        <div class="reviews-list">
            <?php foreach ($reviews as $review): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <strong><?php echo h($review['full_name'] ?: $review['username']); ?></strong>
                            <span class="text-muted ms-2"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                        </div>
                        <div>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="<?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?>">★</span>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <?php if (!empty($review['comment'])): ?>
                    <p class="mb-0"><?php echo nl2br(h($review['comment'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.rating-input {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 5px;
}
.rating-input input[type="radio"] {
    display: none;
}
.rating-input label {
    font-size: 2rem;
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s;
}
.rating-input input[type="radio"]:checked ~ label,
.rating-input label:hover,
.rating-input label:hover ~ label {
    color: #ffc107;
}
.rating-input input[type="radio"]:checked ~ label {
    color: #ffc107;
}
</style>

<?php include 'includes/footer.php'; ?>

