<?php
require_once 'includes/config.php';

requireLogin();

$pdo = getDB();

$cart_id = intval($_GET['id'] ?? 0);

if ($cart_id <= 0) {
    $_SESSION['error'] = 'Invalid cart item ID.';
    redirect('cart.php');
}

// Verify cart item belongs to current user
$stmt = $pdo->prepare("SELECT id FROM cart WHERE id = ? AND user_id = ?");
$stmt->execute([$cart_id, $_SESSION['user_id']]);
$cartItem = $stmt->fetch();

if (!$cartItem) {
    $_SESSION['error'] = 'Cart item not found.';
    redirect('cart.php');
}

// Remove item from cart
$stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
$stmt->execute([$cart_id, $_SESSION['user_id']]);

$_SESSION['success'] = 'Item removed from cart.';
redirect('cart.php');

