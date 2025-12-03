<?php
require_once 'includes/config.php';

requireLogin();

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('checkout.php');
}

$full_name = trim($_POST['full_name'] ?? '');
$shipping_address = trim($_POST['shipping_address'] ?? '');
$phone = trim($_POST['phone'] ?? '');

if (empty($full_name) || empty($shipping_address) || empty($phone)) {
    $_SESSION['error'] = 'Please fill in all required fields.';
    redirect('checkout.php');
}

// Get cart items
$stmt = $pdo->prepare("SELECT c.quantity, p.id, p.name, p.price, p.stock 
                       FROM cart c 
                       INNER JOIN products p ON c.product_id = p.id 
                       WHERE c.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    $_SESSION['error'] = 'Your cart is empty.';
    redirect('cart.php');
}

// Validate stock and calculate total
$total = 0;
$errors = [];

foreach ($cartItems as $item) {
    if ($item['quantity'] > $item['stock']) {
        $errors[] = $item['name'] . ' - Only ' . $item['stock'] . ' available in stock.';
    }
    $total += $item['price'] * $item['quantity'];
}

if (!empty($errors)) {
    $_SESSION['error'] = implode('<br>', $errors);
    redirect('cart.php');
}

// Start transaction
$pdo->beginTransaction();

try {
    // Create order
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, phone) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $total, $shipping_address, $phone]);
    $order_id = $pdo->lastInsertId();
    
    // Create order items and update stock (INVENTORY AUTO-UPDATE)
    foreach ($cartItems as $item) {
        // Insert order item
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
        
        // AUTO-UPDATE INVENTORY: Decrease stock automatically
        $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$item['quantity'], $item['id']]);
        
        // Log inventory update (optional - for tracking)
        // Stock is automatically decreased when order is placed
    }
    
    // Clear cart
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    
    // Commit transaction
    $pdo->commit();
    
    $_SESSION['success'] = 'Order placed successfully! Order ID: #' . $order_id;
    redirect('order-history.php');
    
} catch (Exception $e) {
    // Rollback on error
    $pdo->rollBack();
    $_SESSION['error'] = 'Order failed: ' . $e->getMessage();
    redirect('checkout.php');
}

