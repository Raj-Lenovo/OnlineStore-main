<?php
require_once 'includes/config.php';
require_once 'includes/image-upload.php';

$pageTitle = 'Products';

$pdo = getDB();

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$category_id = $_GET['category'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Build query
$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if (!empty($category_id)) {
    $where[] = "p.category_id = ?";
    $params[] = $category_id;
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Get total count
$countSql = "SELECT COUNT(*) FROM products p $whereClause";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalProducts = $countStmt->fetchColumn();
$totalPages = ceil($totalProducts / $perPage);

// Get sort parameter
$sort = $_GET['sort'] ?? 'newest';
$orderBy = "p.created_at DESC"; // default

switch ($sort) {
    case 'price_low':
        $orderBy = "p.price ASC";
        break;
    case 'price_high':
        $orderBy = "p.price DESC";
        break;
    case 'name':
        $orderBy = "p.name ASC";
        break;
    case 'newest':
    default:
        $orderBy = "p.created_at DESC";
        break;
}

// Get products
$sql = "SELECT p.*, c.name as category_name FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        $whereClause
        ORDER BY $orderBy
        LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for filter
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2>Products</h2>
    </div>
    <div class="col-12 mb-3">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="products.php" class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search Products</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Search by name or description..." value="<?php echo h($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo h($cat['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="sort" class="form-label">Sort By</label>
                        <select class="form-select" id="sort" name="sort" onchange="this.form.submit()">
                            <option value="newest" <?php echo ($_GET['sort'] ?? 'newest') == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="price_low" <?php echo ($_GET['sort'] ?? '') == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo ($_GET['sort'] ?? '') == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="name" <?php echo ($_GET['sort'] ?? '') == 'name' ? 'selected' : ''; ?>>Name A-Z</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100" type="submit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                            </svg> Search
                        </button>
                    </div>
                    <?php if ($search || $category_id): ?>
                    <div class="col-12">
                        <a href="products.php" class="btn btn-sm btn-outline-secondary">Clear Filters</a>
                        <?php if ($search): ?>
                        <span class="badge bg-info ms-2">Search: "<?php echo h($search); ?>"</span>
                        <?php endif; ?>
                        <?php if ($category_id): ?>
                        <span class="badge bg-primary ms-2">Category: <?php 
                            $catName = '';
                            foreach ($categories as $cat) {
                                if ($cat['id'] == $category_id) {
                                    $catName = $cat['name'];
                                    break;
                                }
                            }
                            echo h($catName);
                        ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <p class="text-muted mb-0">
                Showing <?php echo count($products); ?> of <?php echo $totalProducts; ?> products
                <?php if ($totalPages > 1): ?>
                (Page <?php echo $page; ?> of <?php echo $totalPages; ?>)
                <?php endif; ?>
            </p>
        </div>
    </div>
</div>

<div class="row">
<?php if (empty($products)): ?>
<div class="col-12">
    <div class="alert alert-info text-center">
        <h5>No products found</h5>
        <p>Try adjusting your search or filter criteria.</p>
        <a href="products.php" class="btn btn-primary">View All Products</a>
    </div>
</div>
<?php else: ?>
<?php foreach ($products as $product): ?>
<div class="col-lg-3 col-md-4 col-sm-6 mb-4">
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

<!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php 
                // Build pagination URL parameters
                $paginationParams = [];
                if ($search) $paginationParams[] = 'search=' . urlencode($search);
                if ($category_id) $paginationParams[] = 'category=' . $category_id;
                if ($sort && $sort != 'newest') $paginationParams[] = 'sort=' . urlencode($sort);
                $paginationQuery = !empty($paginationParams) ? '&' . implode('&', $paginationParams) : '';
                ?>
                <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $paginationQuery; ?>">Previous</a>
                </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $paginationQuery; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $paginationQuery; ?>">Next</a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>

<?php include 'includes/footer.php'; ?>

