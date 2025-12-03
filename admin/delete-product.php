<?php
require_once '../includes/config.php';

requireAdmin();

$pdo = getDB();

$product_id = intval($_GET['id'] ?? 0);

if (!$product_id) {
    $_SESSION['error'] = 'Invalid product ID.';
    redirect('dashboard.php');
}

// Check if product exists
$stmt = $pdo->prepare("SELECT id, name FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['error'] = 'Product not found.';
    redirect('dashboard.php');
}

// Handle deletion confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        // Check if product is in any orders
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $orderCount = $stmt->fetchColumn();
        
        if ($orderCount > 0) {
            $_SESSION['error'] = 'Cannot delete product. It is associated with existing orders.';
        } else {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $_SESSION['success'] = 'Product deleted successfully.';
        }
        redirect('dashboard.php');
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Failed to delete product: ' . $e->getMessage();
        redirect('dashboard.php');
    }
}

$pageTitle = 'Delete Product';
include '../includes/header.php';
?>

<h2>Delete Product</h2>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <p>Are you sure you want to delete the following product?</p>
                <p><strong>Product:</strong> <?php echo h($product['name']); ?></p>
                <p class="text-danger">This action cannot be undone.</p>
                
                <form method="POST" action="">
                    <button type="submit" name="confirm_delete" class="btn btn-danger">Yes, Delete Product</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

