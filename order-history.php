<?php
require_once 'includes/config.php';
requireLogin();

$pageTitle = 'Order History';

$pdo = getDB();

// Get user orders (newest first)
$stmt = $pdo->prepare("
    SELECT *
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

include 'includes/header.php';
?>

<h2>My Orders</h2>

<?php if (empty($orders)): ?>
    <div class="alert alert-info">
        <p>You have no orders yet.</p>
        <a href="products.php" class="btn btn-primary">Start Shopping</a>
    </div>
<?php else: ?>

    <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Shipping Address</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // We’ll build modals in a string and print them after the table
                $orderModals = '';

                foreach ($orders as $order):
                    // Short shipping address for table cell
                    $shortAddress = mb_strimwidth($order['shipping_address'], 0, 40, '...');
                ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                        <td><strong><?php echo formatPrice($order['total_amount']); ?></strong></td>
                        <td>
                            <?php
                            $statusClass = match($order['status']) {
                                'pending'    => 'warning',
                                'processing' => 'info',
                                'shipped'    => 'primary',
                                'delivered'  => 'success',
                                'cancelled'  => 'danger',
                                default      => 'secondary'
                            };
                            ?>
                            <span class="badge bg-<?php echo $statusClass; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td><?php echo h($shortAddress); ?></td>
                        <td class="text-center">
                            <button
                                type="button"
                                class="btn btn-sm btn-info"
                                data-bs-toggle="modal"
                                data-bs-target="#orderModal<?php echo $order['id']; ?>"
                            >
                                View Details
                            </button>
                        </td>
                    </tr>
                <?php
                    // Build the modal for this order
                    ob_start();
                ?>
                    <div class="modal fade" id="orderModal<?php echo $order['id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        Order #<?php echo $order['id']; ?> Details
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">

                                    <div class="mb-3">
                                        <strong>Order Date:</strong>
                                        <?php echo date('F d, Y H:i', strtotime($order['created_at'])); ?>
                                    </div>

                                    <div class="mb-3">
                                        <strong>Status:</strong>
                                        <span class="badge bg-<?php echo $statusClass; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </div>

                                    <div class="mb-3">
                                        <strong>Shipping Address:</strong><br>
                                        <?php echo nl2br(h($order['shipping_address'])); ?>
                                    </div>

                                    <div class="mb-3">
                                        <strong>Phone:</strong>
                                        <?php echo h($order['phone']); ?>
                                    </div>

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
                                            <?php
                                            $stmtItems = $pdo->prepare("
                                                SELECT oi.*, p.name
                                                FROM order_items oi
                                                INNER JOIN products p ON oi.product_id = p.id
                                                WHERE oi.order_id = ?
                                            ");
                                            $stmtItems->execute([$order['id']]);
                                            $orderItems = $stmtItems->fetchAll();

                                            foreach ($orderItems as $item):
                                            ?>
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
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                    // Append modal HTML to our buffer
                    $orderModals .= ob_get_clean();
                endforeach;
                ?>
            </tbody>
        </table>
    </div>

    <!-- Render all modals here, OUTSIDE the table -->
    <?php echo $orderModals; ?>

<?php endif; ?>

<?php include 'includes/footer.php'; ?>
