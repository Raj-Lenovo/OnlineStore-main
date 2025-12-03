<?php
require_once '../includes/config.php';

requireAdmin();

$pageTitle = 'Manage Products';

$pdo = getDB();

// ======================
// Filters (GET params)
// ======================
$search      = trim($_GET['q'] ?? '');
$categoryId  = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$stockFilter = $_GET['stock'] ?? 'all';

// ======================
// Get categories for filter
// ======================
$catStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
$categories = $catStmt->fetchAll();

// ======================
// Build product query with filters
// ======================
$sql = "SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id";

$where  = [];
$params = [];

// Search by name
if ($search !== '') {
    $where[]      = "p.name LIKE :q";
    $params[':q'] = '%' . $search . '%';
}

// Filter by category
if ($categoryId > 0) {
    $where[]        = "p.category_id = :cat";
    $params[':cat'] = $categoryId;
}

// Stock filter
switch ($stockFilter) {
    case 'in':
        $where[] = "p.stock > 0";
        break;
    case 'out':
        $where[] = "p.stock = 0";
        break;
    case 'low':
        $where[] = "p.stock >= 0 AND p.stock <= 5";
        break;
    case 'all':
    default:
        // no extra condition
        break;
}

if ($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Manage Products</h2>
    <span class="badge bg-secondary">
        <?php echo count($products); ?> product(s) found
    </span>
</div>

<!-- Top actions -->
<div class="mb-3">
    <a href="add-product.php" class="btn btn-primary">Add New Product</a>
    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</div>

<!-- Filters -->
<div class="card mb-4 product-search-card">
    <div class="card-header">
        <strong>Filter Products</strong>
    </div>
    <div class="card-body">
        <form class="row g-3 product-search-form" method="get" action="">
            <div class="col-md-4">
                <label for="search" class="form-label">Search by Name</label>
                <input
                    type="text"
                    name="q"
                    id="search"
                    class="form-control"
                    placeholder="e.g., Laptop, Mouse"
                    value="<?php echo h($search); ?>"
                >
            </div>

            <div class="col-md-3">
                <label for="category_id" class="form-label">Category</label>
                <select name="category_id" id="category_id" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option
                            value="<?php echo $cat['id']; ?>"
                            <?php echo $categoryId === (int)$cat['id'] ? 'selected' : ''; ?>
                        >
                            <?php echo h($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label for="stock" class="form-label">Stock Status</label>
                <select name="stock" id="stock" class="form-select">
                    <option value="all" <?php echo $stockFilter === 'all' ? 'selected' : ''; ?>>All</option>
                    <option value="in"  <?php echo $stockFilter === 'in'  ? 'selected' : ''; ?>>In Stock</option>
                    <option value="low" <?php echo $stockFilter === 'low' ? 'selected' : ''; ?>>Low Stock (0–5)</option>
                    <option value="out" <?php echo $stockFilter === 'out' ? 'selected' : ''; ?>>Out of Stock</option>
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <div class="d-grid gap-2 w-100">
                    <button type="submit" class="btn btn-outline-primary">Apply</button>
                    <a href="products.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if (empty($products)): ?>
    <div class="alert alert-info">
        <p>No products found. Try changing the filters or add a new product.</p>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header">
            <strong>Product List</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:60px;">ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th style="width:120px;">Price</th>
                            <th style="width:110px;">Stock</th>
                            <th style="width:190px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td>
                                <?php echo h($product['name']); ?>

                                <?php if (!empty($product['image'])): ?>
                                    <?php
                                    $rawImage = trim($product['image']);

                                    if (preg_match('#^https?://#i', $rawImage)) {
                                        // full URL stored
                                        $imgUrl = $rawImage;
                                    } else {
                                        // if it's just a filename, assume assets/images/products/
                                        if (strpos($rawImage, '/') === false) {
                                            $rawImage = 'assets/images/products/' . $rawImage;
                                        }
                                        // make absolute URL based on SITE_URL
                                        $imgUrl = rtrim(SITE_URL, '/') . '/' . ltrim($rawImage, '/');
                                    }
                                    ?>
                                    <br>
                                    <img
                                        src="<?php echo h($imgUrl); ?>"
                                        alt="<?php echo h($product['name']); ?>"
                                        style="max-width:60px; max-height:60px; border-radius:4px; margin-top:4px;"
                                    >
                                <?php endif; ?>
                            </td>
                            <td><?php echo h($product['category_name'] ?? 'Uncategorized'); ?></td>
                            <td><?php echo formatPrice($product['price']); ?></td>
                            <td>
                                <?php
                                $stock = (int)$product['stock'];
                                if ($stock <= 0) {
                                    $badgeClass = 'danger';
                                } elseif ($stock <= 5) {
                                    $badgeClass = 'warning';
                                } else {
                                    $badgeClass = 'success';
                                }
                                ?>
                                <span class="badge bg-<?php echo $badgeClass; ?>">
                                    <?php echo $stock; ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a
                                        href="../product-details.php?id=<?php echo $product['id']; ?>"
                                        class="btn btn-outline-secondary"
                                        target="_blank"
                                    >
                                        View
                                    </a>
                                    <a
                                        href="edit-product.php?id=<?php echo $product['id']; ?>"
                                        class="btn btn-warning"
                                    >
                                        Edit
                                    </a>
                                    <a
                                        href="delete-product.php?id=<?php echo $product['id']; ?>"
                                        class="btn btn-danger"
                                        onclick="return confirm('Are you sure you want to delete this product?')"
                                    >
                                        Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <small class="text-muted d-block mt-2">
                Tip: use the filters above to quickly find products by name, category, or stock level.
            </small>
        </div>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
