<?php
require_once '../includes/config.php';

requireAdmin();

$pageTitle = 'Admin Dashboard';

$pdo = getDB();

// =====================
// Stats
// =====================
$stats = [];

// Total products
$stmt = $pdo->query("SELECT COUNT(*) FROM products");
$stats['products'] = $stmt->fetchColumn();

// Total orders
$stmt = $pdo->query("SELECT COUNT(*) FROM orders");
$stats['orders'] = $stmt->fetchColumn();

// Total customers (role = user)
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
$stats['users'] = $stmt->fetchColumn();

// Total revenue (all non-cancelled)
$stmt = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status != 'cancelled'");
$stats['revenue_total'] = $stmt->fetchColumn() ?: 0;

// Revenue this month
$stmt = $pdo->query("
    SELECT SUM(total_amount)
    FROM orders
    WHERE status != 'cancelled'
      AND YEAR(created_at) = YEAR(CURDATE())
      AND MONTH(created_at) = MONTH(CURDATE())
");
$stats['revenue_month'] = $stmt->fetchColumn() ?: 0;

// Revenue today
$stmt = $pdo->query("
    SELECT SUM(total_amount)
    FROM orders
    WHERE status != 'cancelled'
      AND DATE(created_at) = CURDATE()
");
$stats['revenue_today'] = $stmt->fetchColumn() ?: 0;

// Orders today (count)
$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM orders
    WHERE DATE(created_at) = CURDATE()
");
$stats['orders_today'] = $stmt->fetchColumn() ?: 0;

// Orders by status
$orderStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
$statusCounts = [];
foreach ($orderStatuses as $status) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE status = ?");
    $stmt->execute([$status]);
    $statusCounts[$status] = $stmt->fetchColumn() ?: 0;
}

// =====================
// Recent orders
// =====================
$stmt = $pdo->query("
    SELECT o.*, u.username, u.full_name
    FROM orders o
    INNER JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 10
");
$recentOrders = $stmt->fetchAll();

// =====================
// Low stock products
// =====================
$stmt = $pdo->query("
    SELECT id, name, stock
    FROM products
    WHERE stock IS NOT NULL
    ORDER BY stock ASC
    LIMIT 5
");
$lowStockProducts = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0">Admin Dashboard</h2>
        <small class="text-muted">
            Overview of your store performance
        </small>
    </div>
    <span class="badge bg-success px-3 py-2">
        Live
    </span>
</div>

<!-- Top Stats -->
<div class="row admin-dashboard-stats g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted text-uppercase mb-1">Total Products</h6>
                    <h2 class="mb-0"><?php echo $stats['products']; ?></h2>
                    <small class="text-muted">Active items in catalog</small>
                </div>
                <div class="fs-1 text-primary opacity-25">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted text-uppercase mb-1">Total Orders</h6>
                    <h2 class="mb-0"><?php echo $stats['orders']; ?></h2>
                    <small class="text-muted">
                        <?php echo $stats['orders_today']; ?> orders today
                    </small>
                </div>
                <div class="fs-1 text-success opacity-25">
                    <i class="bi bi-cart-check"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted text-uppercase mb-1">Customers</h6>
                    <h2 class="mb-0"><?php echo $stats['users']; ?></h2>
                    <small class="text-muted">Registered users</small>
                </div>
                <div class="fs-1 text-info opacity-25">
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase mb-1">Revenue</h6>
                <h3 class="mb-1 text-success">
                    <?php echo formatPrice($stats['revenue_total']); ?>
                </h3>
                <div class="d-flex justify-content-between mb-1">
                    <div>
                        <small class="text-muted d-block">This Month</small>
                        <strong class="small">
                            <?php echo formatPrice($stats['revenue_month']); ?>
                        </strong>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Today</small>
                        <strong class="small">
                            <?php echo formatPrice($stats['revenue_today']); ?>
                        </strong>
                    </div>
                </div>
                <small class="text-muted">
                    Non-cancelled orders only
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Second Row: Quick Actions + Recent Orders -->
<div class="row g-3 mb-4">
    <!-- Quick Actions -->
    <div class="col-lg-5 col-md-12">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2 quick-actions">
                    <a href="add-product.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>
                        Add New Product
                    </a>
                    <a href="products.php" class="btn btn-outline-primary">
                        <i class="bi bi-box-seam me-2"></i>
                        Manage Products
                    </a>
                    <a href="view-orders.php" class="btn btn-outline-success">
                        <i class="bi bi-receipt-cutoff me-2"></i>
                        View All Orders
                    </a>
                    <a href="view-reviews.php" class="btn btn-outline-warning">
                        <i class="bi bi-star me-2"></i>
                        Manage Reviews
                    </a>
                    <!-- FIXED: use store index, same as navbar brand, same tab -->
                    <a href="<?php echo SITE_URL; ?>/index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-shop me-2"></i>
                        View Store
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="col-lg-7 col-md-12">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Orders</h5>
                <a href="view-orders.php" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($recentOrders)): ?>
                    <p class="text-muted mb-0">No orders yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo h($order['full_name']); ?></td>
                                    <td><?php echo formatPrice($order['total_amount']); ?></td>
                                    <td>
                                        <?php
                                        $badgeClass = match ($order['status']) {
                                            'pending'    => 'warning',
                                            'processing' => 'info',
                                            'shipped'    => 'primary',
                                            'delivered'  => 'success',
                                            'cancelled'  => 'danger',
                                            default      => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?php echo $badgeClass; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?>
                                        </small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Third Row: Order Status Overview + Low Stock -->
<div class="row g-3">
    <!-- Order Status Overview -->
    <div class="col-lg-6 col-md-12">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header">
                <h5 class="mb-0">Order Status Overview</h5>
            </div>
            <div class="card-body">
                <?php if ($stats['orders'] == 0): ?>
                    <p class="text-muted mb-0">No orders yet.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php
                        $statusLabels = [
                            'pending'    => 'Pending',
                            'processing' => 'Processing',
                            'shipped'    => 'Shipped',
                            'delivered'  => 'Delivered',
                            'cancelled'  => 'Cancelled',
                        ];
                        $statusBadge = [
                            'pending'    => 'warning',
                            'processing' => 'info',
                            'shipped'    => 'primary',
                            'delivered'  => 'success',
                            'cancelled'  => 'danger',
                        ];
                        foreach ($orderStatuses as $status):
                            $count = $statusCounts[$status];
                            $percent = $stats['orders'] > 0
                                ? round(($count / $stats['orders']) * 100)
                                : 0;
                        ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-<?php echo $statusBadge[$status]; ?> me-2">
                                        <?php echo $statusLabels[$status]; ?>
                                    </span>
                                    <small class="text-muted">
                                        <?php echo $percent; ?>% of all orders
                                    </small>
                                </div>
                                <span class="fw-semibold">
                                    <?php echo $count; ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Low Stock Products -->
    <div class="col-lg-6 col-md-12">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Low Stock Products</h5>
                <small class="text-muted">Top 5 lowest stock</small>
            </div>
            <div class="card-body">
                <?php if (empty($lowStockProducts)): ?>
                    <p class="text-muted mb-0">No products with low stock.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th class="text-end">Stock</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($lowStockProducts as $product): ?>
                                <tr>
                                    <td><?php echo h($product['name']); ?></td>
                                    <td class="text-end">
                                        <?php if ($product['stock'] <= 5): ?>
                                            <span class="badge bg-danger">
                                                <?php echo $product['stock']; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">
                                                <?php echo $product['stock']; ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <small class="text-muted d-block mt-2">
                        Consider updating stock for items in red.
                    </small>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
