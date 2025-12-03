<?php
require_once '../includes/config.php';

requireAdmin();

$pageTitle = 'View Orders';

$pdo = getDB();

// =========================
// Handle status update
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id'] ?? 0);
    $status   = $_POST['status'] ?? '';

    $allowed_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

    if ($order_id > 0 && in_array($status, $allowed_statuses, true)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);

        $_SESSION['success'] = 'Order status updated successfully.';
        redirect('view-orders.php');
    }
}

// =========================
// Status summary (for chips)
// =========================
$statusCounts = [
    'pending'    => 0,
    'processing' => 0,
    'shipped'    => 0,
    'delivered'  => 0,
    'cancelled'  => 0,
];

$stmt = $pdo->query("
    SELECT status, COUNT(*) AS cnt
    FROM orders
    GROUP BY status
");
$totalOrders = 0;
while ($row = $stmt->fetch()) {
    $status = $row['status'];
    $cnt    = (int)$row['cnt'];
    $totalOrders += $cnt;

    if (array_key_exists($status, $statusCounts)) {
        $statusCounts[$status] = $cnt;
    }
}

// =========================
// Get all orders
// =========================
$stmt = $pdo->query("
    SELECT o.*, u.username, u.full_name, u.email
    FROM orders o
    INNER JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="mb-0">All Orders</h2>
        <small class="text-muted">
            Manage every order and update its status in real time.
        </small>
    </div>
    <span class="badge bg-secondary px-3 py-2">
        Total Orders: <?php echo $totalOrders; ?>
    </span>
</div>

<!-- Status overview chips -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body d-flex flex-wrap gap-2 align-items-center">
        <span class="fw-semibold me-2">Status Overview:</span>

        <span class="badge bg-warning-subtle text-dark border border-warning-subtle">
            Pending: <strong><?php echo $statusCounts['pending']; ?></strong>
        </span>
        <span class="badge bg-info-subtle text-dark border border-info-subtle">
            Processing: <strong><?php echo $statusCounts['processing']; ?></strong>
        </span>
        <span class="badge bg-primary-subtle text-dark border border-primary-subtle">
            Shipped: <strong><?php echo $statusCounts['shipped']; ?></strong>
        </span>
        <span class="badge bg-success-subtle text-dark border border-success-subtle">
            Delivered: <strong><?php echo $statusCounts['delivered']; ?></strong>
        </span>
        <span class="badge bg-danger-subtle text-dark border border-danger-subtle">
            Cancelled: <strong><?php echo $statusCounts['cancelled']; ?></strong>
        </span>

        <span class="ms-auto small text-muted">
            Tip: Changing the dropdown will auto-save the new status.
        </span>
    </div>
</div>

<?php if (empty($orders)): ?>
    <div class="alert alert-info">
        <p>No orders found.</p>
    </div>
<?php else: ?>

    <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Shipping Address</th>
                <th class="text-center">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php
            // collect modals here and render after table
            $orderModals = '';

            foreach ($orders as $order):
                $shortAddress = mb_strimwidth($order['shipping_address'], 0, 40, '...');
                ?>
                <tr>
                    <td>#<?php echo $order['id']; ?></td>
                    <td>
                        <?php echo h($order['full_name']); ?><br>
                        <small class="text-muted"><?php echo h($order['email']); ?></small>
                    </td>
                    <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                    <td><strong><?php echo formatPrice($order['total_amount']); ?></strong></td>
                    <td style="min-width: 160px;">
                        <form method="POST" action="" class="d-flex align-items-center gap-1">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="pending"    <?php echo $order['status'] === 'pending'    ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped"    <?php echo $order['status'] === 'shipped'    ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered"  <?php echo $order['status'] === 'delivered'  ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled"  <?php echo $order['status'] === 'cancelled'  ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <input type="hidden" name="update_status" value="1">
                        </form>
                    </td>
                    <td><?php echo nl2br(h($shortAddress)); ?></td>
                    <td class="text-center">
                        <button type="button"
                                class="btn btn-sm btn-info"
                                data-bs-toggle="modal"
                                data-bs-target="#orderModal<?php echo $order['id']; ?>">
                            View Details
                        </button>
                    </td>
                </tr>

                <?php
                // ===== Build the modal for this order into a buffer =====
                ob_start();

                $statusClass = match ($order['status']) {
                    'pending'    => 'warning',
                    'processing' => 'info',
                    'shipped'    => 'primary',
                    'delivered'  => 'success',
                    'cancelled'  => 'danger',
                    default      => 'secondary'
                };

                // fetch order items
                $stmtItems = $pdo->prepare("
                    SELECT oi.*, p.name
                    FROM order_items oi
                    INNER JOIN products p ON oi.product_id = p.id
                    WHERE oi.order_id = ?
                ");
                $stmtItems->execute([$order['id']]);
                $orderItems = $stmtItems->fetchAll();
                ?>
                <div class="modal fade" id="orderModal<?php echo $order['id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Order #<?php echo $order['id']; ?> Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>
                                    <strong>Customer:</strong>
                                    <?php echo h($order['full_name']); ?>
                                    (<?php echo h($order['email']); ?>)
                                </p>
                                <p>
                                    <strong>Order Date:</strong>
                                    <?php echo date('F d, Y H:i', strtotime($order['created_at'])); ?>
                                </p>
                                <p>
                                    <strong>Status:</strong>
                                    <span class="badge bg-<?php echo $statusClass; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </p>
                                <p>
                                    <strong>Shipping Address:</strong><br>
                                    <?php echo nl2br(h($order['shipping_address'])); ?>
                                </p>
                                <p>
                                    <strong>Phone:</strong>
                                    <?php echo h($order['phone']); ?>
                                </p>

                                <hr>

                                <h6>Order Items</h6>
                                <table class="table table-sm">
                                    <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Subtotal</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($orderItems as $item): ?>
                                        <tr>
                                            <td><?php echo h($item['name']); ?></td>
                                            <td><?php echo (int)$item['quantity']; ?></td>
                                            <td><?php echo formatPrice($item['price']); ?></td>
                                            <td><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Total:</th>
                                        <th><?php echo formatPrice($order['total_amount']); ?></th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                $orderModals .= ob_get_clean();
            endforeach;
            ?>
            </tbody>
        </table>
    </div>

    <!-- All modals rendered here, outside the table -->
    <?php echo $orderModals; ?>

<?php endif; ?>

<div class="mt-3">
    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</div>

<?php include '../includes/footer.php'; ?>
