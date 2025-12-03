<?php
require_once 'includes/config.php';

requireLogin();

$pageTitle = 'Shopping Cart';

$pdo = getDB();

// Handle quantity update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantities'] as $cart_id => $quantity) {
        $cart_id = intval($cart_id);
        $quantity = intval($quantity);
        
        if ($quantity <= 0) {
            // Remove item if quantity is 0 or less
            $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $stmt->execute([$cart_id, $_SESSION['user_id']]);
        } else {
            // Get product stock
            $stmt = $pdo->prepare("SELECT p.stock FROM products p 
                                   INNER JOIN cart c ON p.id = c.product_id 
                                   WHERE c.id = ? AND c.user_id = ?");
            $stmt->execute([$cart_id, $_SESSION['user_id']]);
            $result = $stmt->fetch();
            
            if ($result && $quantity <= $result['stock']) {
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$quantity, $cart_id, $_SESSION['user_id']]);
            } else {
                $_SESSION['error'] = 'Some items could not be updated due to insufficient stock.';
            }
        }
    }
    redirect('cart.php');
}

// Get cart items
$stmt = $pdo->prepare("SELECT c.id as cart_id, c.quantity, p.id, p.name, p.price, p.stock, p.image 
                       FROM cart c 
                       INNER JOIN products p ON c.product_id = p.id 
                       WHERE c.user_id = ? 
                       ORDER BY c.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$cartItems = $stmt->fetchAll();

$total = 0;

include 'includes/header.php';
?>

<h2>Shopping Cart</h2>

<?php if (empty($cartItems)): ?>
<div class="alert alert-info">
    <p>Your cart is empty.</p>
    <a href="products.php" class="btn btn-primary">Continue Shopping</a>
</div>
<?php else: ?>
<form method="POST" action="">
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Stock</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartItems as $item): 
                    $subtotal = $item['price'] * $item['quantity'];
                    $total += $subtotal;
                ?>
                <tr>
                    <td>
                        <strong><?php echo h($item['name']); ?></strong>
                    </td>
                    <td><?php echo formatPrice($item['price']); ?></td>
                    <td>
                        <input type="number" name="quantities[<?php echo $item['cart_id']; ?>]" 
                               value="<?php echo $item['quantity']; ?>" 
                               min="1" max="<?php echo $item['stock']; ?>" 
                               class="form-control" style="width: 80px;">
                    </td>
                    <td>
                        <span class="badge bg-<?php echo $item['stock'] > 0 ? 'success' : 'danger'; ?>">
                            <?php echo $item['stock']; ?>
                        </span>
                    </td>
                    <td><strong><?php echo formatPrice($subtotal); ?></strong></td>
                    <td>
                        <a href="remove-from-cart.php?id=<?php echo $item['cart_id']; ?>" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm('Remove this item from cart?')">Remove</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end"><strong>Total:</strong></td>
                    <td><strong class="text-primary"><?php echo formatPrice($total); ?></strong></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-6">
            <button type="submit" name="update_cart" class="btn btn-secondary">Update Cart</button>
            <a href="products.php" class="btn btn-outline-primary">Continue Shopping</a>
        </div>
        <div class="col-md-6 text-end">
            <a href="checkout.php" class="btn btn-primary btn-lg">Proceed to Checkout</a>
        </div>
    </div>
</form>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>

