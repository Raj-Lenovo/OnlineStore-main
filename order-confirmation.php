<?php
require_once 'includes/config.php';
require_once 'includes/image-upload.php';
requireLogin();

$pageTitle = 'Order Confirmation';

$pdo = getDB();

$order_id = intval($_GET['order_id'] ?? 0);

if (!$order_id) {
    $_SESSION['error'] = 'Invalid order ID.';
    redirect('order-history.php');
}

// Get order details
$stmt = $pdo->prepare("
    SELECT * FROM orders 
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['error'] = 'Order not found.';
    redirect('order-history.php');
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.image
    FROM order_items oi
    INNER JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$orderItems = $stmt->fetchAll();

// Parse payment details if exists
$paymentDetails = json_decode($order['payment_details'] ?? '{}', true);

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-9">
        
        <!-- Success Header -->
        <div class="card border-success mb-4 shadow">
            <div class="card-body text-center py-5">
                <div class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" fill="currentColor" class="bi bi-check-circle-fill text-success" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                    </svg>
                </div>
                <h2 class="text-success mb-3">🎉 Order Placed Successfully!</h2>
                <p class="lead mb-2">Thank you for your order!</p>
                <p class="text-muted mb-4">
                    Order ID: <strong class="text-dark">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></strong>
                </p>
                
                <?php if ($order['payment_method'] === 'stripe' && $order['payment_status'] === 'paid'): ?>
                <div class="alert alert-success mx-auto" style="max-width: 500px;">
                    <strong>✅ Payment Confirmed</strong><br>
                    <small class="text-muted">
                        Transaction ID: <?php echo h($order['transaction_id']); ?><br>
                        Amount Paid: <?php echo formatPrice($order['total_amount']); ?>
                    </small>
                </div>
                <?php elseif ($order['payment_method'] === 'cod'): ?>
                <div class="alert alert-info mx-auto" style="max-width: 500px;">
                    <strong>💵 Cash on Delivery</strong><br>
                    <small>Please have <strong><?php echo formatPrice($order['total_amount']); ?></strong> ready when your order arrives.</small>
                </div>
                <?php endif; ?>
                
                <div class="mt-4">
                    <p class="mb-2"><strong>📧 Confirmation email sent to your registered email address</strong></p>
                    <p class="text-muted small">You can track your order status in your order history</p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Order Details -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">📋 Order Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Order Date:</strong><br>
                            <span class="text-muted"><?php echo date('F d, Y \a\t g:i A', strtotime($order['created_at'])); ?></span>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Order Status:</strong><br>
                            <?php
                            $statusClass = match($order['status']) {
                                'pending'    => 'warning',
                                'processing' => 'info',
                                'shipped'    => 'primary',
                                'delivered'  => 'success',
                                'cancelled'  => 'danger',
                                default      => 'secondary'
                            };
                            $statusIcon = match($order['status']) {
                                'pending'    => '⏳',
                                'processing' => '📦',
                                'shipped'    => '🚚',
                                'delivered'  => '✅',
                                'cancelled'  => '❌',
                                default      => '📋'
                            };
                            ?>
                            <span class="badge bg-<?php echo $statusClass; ?> fs-6">
                                <?php echo $statusIcon; ?> <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>

                        <div class="mb-3">
                            <strong>Payment Method:</strong><br>
                            <span class="text-muted">
                                <?php if ($order['payment_method'] === 'stripe'): ?>
                                    💳 Credit/Debit Card (Stripe)
                                <?php else: ?>
                                    💵 Cash on Delivery
                                <?php endif; ?>
                            </span>
                        </div>

                        <div class="mb-0">
                            <strong>Payment Status:</strong><br>
                            <?php
                            $paymentStatusClass = match($order['payment_status']) {
                                'paid'     => 'success',
                                'pending'  => 'warning',
                                'failed'   => 'danger',
                                'refunded' => 'info',
                                default    => 'secondary'
                            };
                            ?>
                            <span class="badge bg-<?php echo $paymentStatusClass; ?>">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping Information -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">🚚 Shipping Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Shipping Address:</strong><br>
                            <span class="text-muted"><?php echo nl2br(h($order['shipping_address'])); ?></span>
                        </div>

                        <div class="mb-0">
                            <strong>Phone Number:</strong><br>
                            <span class="text-muted"><?php echo h($order['phone']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">📦 Order Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $subtotal = 0;
                            foreach ($orderItems as $item): 
                                $itemTotal = $item['price'] * $item['quantity'];
                                $subtotal += $itemTotal;
                            ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($item['image'])): ?>
                                            <img src="<?php echo getImageUrl($item['image']); ?>" 
                                                 alt="<?php echo h($item['name']); ?>" 
                                                 class="rounded me-3" 
                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                            <?php endif; ?>
                                            <strong><?php echo h($item['name']); ?></strong>
                                        </div>
                                    </td>
                                    <td class="text-center"><?php echo (int)$item['quantity']; ?></td>
                                    <td class="text-end"><?php echo formatPrice($item['price']); ?></td>
                                    <td class="text-end"><strong><?php echo formatPrice($itemTotal); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                <td class="text-end"><?php echo formatPrice($subtotal); ?></td>
                            </tr>
                            <?php
                            $shipping = $subtotal > 100 ? 0 : 10;
                            $tax = $subtotal * 0.13;
                            ?>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Shipping:</strong></td>
                                <td class="text-end"><?php echo $shipping > 0 ? formatPrice($shipping) : '<span class="text-success fw-bold">FREE</span>'; ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Tax (13%):</strong></td>
                                <td class="text-end"><?php echo formatPrice($tax); ?></td>
                            </tr>
                            <tr class="table-primary">
                                <td colspan="3" class="text-end"><strong class="fs-5">Total:</strong></td>
                                <td class="text-end"><strong class="fs-5 text-primary"><?php echo formatPrice($order['total_amount']); ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex gap-3 justify-content-center mb-5">
            <a href="order-history.php" class="btn btn-primary btn-lg">
                📋 View All Orders
            </a>
            <a href="products.php" class="btn btn-outline-primary btn-lg">
                🛒 Continue Shopping
            </a>
            <button onclick="window.print()" class="btn btn-outline-secondary btn-lg">
                🖨️ Print Order
            </button>
        </div>

        <!-- What's Next -->
        <div class="card bg-light border-0">
            <div class="card-body">
                <h5 class="card-title">📌 What Happens Next?</h5>
                <ul class="mb-0">
                    <li>We'll process your order within 1-2 business days</li>
                    <li>You'll receive tracking information once your order ships</li>
                    <li>Expected delivery: 3-5 business days</li>
                    <li>Track your order status in <a href="order-history.php">Order History</a></li>
                    <?php if ($order['payment_method'] === 'cod'): ?>
                    <li><strong>Have exact cash ready for delivery</strong></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

    </div>
</div>

<style>
@media print {
    .navbar, footer, .btn, .card-header { display: none !important; }
    .card { border: 1px solid #dee2e6 !important; page-break-inside: avoid; }
}
</style>

<?php include 'includes/footer.php'; ?>