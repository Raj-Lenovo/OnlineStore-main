<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

requireLogin();

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $rating = intval($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    
    // Validation
    if ($product_id <= 0) {
        $_SESSION['error'] = 'Invalid product.';
        redirect('products.php');
    }
    
    if ($rating < 1 || $rating > 5) {
        $_SESSION['error'] = 'Please select a rating between 1 and 5 stars.';
        redirect('product-details.php?id=' . $product_id);
    }
    
    // Check if product exists
    $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    if (!$stmt->fetch()) {
        $_SESSION['error'] = 'Product not found.';
        redirect('products.php');
    }
    
    // Check if user already reviewed this product
    $stmt = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$_SESSION['user_id'], $product_id]);
    $existing = $stmt->fetch();
    
    try {
        if ($existing) {
            // Update existing review
            $stmt = $pdo->prepare("UPDATE reviews SET rating = ?, comment = ? WHERE id = ?");
            $stmt->execute([$rating, $comment, $existing['id']]);
            $_SESSION['success'] = 'Review updated successfully.';
        } else {
            // Insert new review
            $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
            $stmt->execute([$product_id, $_SESSION['user_id'], $rating, $comment]);
            $_SESSION['success'] = 'Review submitted successfully.';
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Failed to submit review: ' . $e->getMessage();
    }
    
    redirect('product-details.php?id=' . $product_id);
} else {
    redirect('products.php');
}

