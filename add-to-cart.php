<?php
require_once 'includes/config.php';

requireLogin();

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);
    
    if ($product_id <= 0 || $quantity <= 0) {
        $_SESSION['error'] = 'Invalid product or quantity.';
        redirect('products.php');
    }
    
    // Check if product exists and has stock
    $stmt = $pdo->prepare("SELECT id, name, stock FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        $_SESSION['error'] = 'Product not found.';
        redirect('products.php');
    }
    
    if ($product['stock'] < $quantity) {
        $_SESSION['error'] = 'Insufficient stock. Only ' . $product['stock'] . ' available.';
        redirect('product-details.php?id=' . $product_id);
    }
    
    // Check if item already in cart
    $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$_SESSION['user_id'], $product_id]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $newQuantity = $existing['quantity'] + $quantity;
        if ($newQuantity > $product['stock']) {
            $_SESSION['error'] = 'Cannot add more. Only ' . $product['stock'] . ' available in stock.';
            redirect('product-details.php?id=' . $product_id);
        }
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->execute([$newQuantity, $existing['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $product_id, $quantity]);
    }
    
    $_SESSION['success'] = 'Product added to cart successfully.';
    redirect('cart.php');
} else {
    redirect('products.php');
}

