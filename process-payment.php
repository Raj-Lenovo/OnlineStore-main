<?php

use Stripe\PaymentIntent;
use Stripe\Exception\CardException;
use Stripe\Exception\RateLimitException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;
require_once 'includes/config.php';
requireLogin();

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('checkout.php');
}

// Get form data
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$shipping_address = trim($_POST['shipping_address'] ?? '');
$payment_method = $_POST['payment_method'] ?? 'stripe';
$payment_method_id = $_POST['payment_method_id'] ?? null;
$total_amount = floatval($_POST['total_amount'] ?? 0);

// Validate required fields
if (empty($full_name) || empty($email) || empty($shipping_address) || empty($phone)) {
    $_SESSION['error'] = 'Please fill in all required fields.';
    redirect('checkout.php');
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Invalid email address.';
    redirect('checkout.php');
}

// Get cart items
$stmt = $pdo->prepare("
    SELECT c.quantity, p.id, p.name, p.price, p.stock 
    FROM cart c 
    INNER JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    $_SESSION['error'] = 'Your cart is empty.';
    redirect('cart.php');
}

// Validate stock and calculate total
$subtotal = 0;
$errors = [];

foreach ($cartItems as $item) {
    if ($item['quantity'] > $item['stock']) {
        $errors[] = $item['name'] . ' - Only ' . $item['stock'] . ' available in stock.';
    }
    $subtotal += $item['price'] * $item['quantity'];
}

if (!empty($errors)) {
    $_SESSION['error'] = implode('<br>', $errors);
    redirect('cart.php');
}

// Calculate shipping and tax
$shipping = $subtotal > 100 ? 0 : 10;
$tax = $subtotal * 0.13;
$calculated_total = $subtotal + $shipping + $tax;

// Verify total matches (allow 1 cent difference for rounding)
if (abs($calculated_total - $total_amount) > 0.01) {
    $_SESSION['error'] = 'Total amount mismatch. Please try again.';
    redirect('checkout.php');
}

// Initialize payment variables
$payment_status = 'pending';
$transaction_id = null;
$payment_details = [];

// =====================================================
// PROCESS PAYMENT BASED ON METHOD
// =====================================================

if ($payment_method === 'stripe' && $payment_method_id) {
    // ============== STRIPE PAYMENT ==============
    require_once 'includes/stripe-config.php';
    
    try {
        // Create a PaymentIntent
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => round($total_amount * 100), // Convert to cents
            'currency' => STRIPE_CURRENCY,
            'payment_method' => $payment_method_id,
            'confirm' => true,
            'automatic_payment_methods' => [
                'enabled' => true,
                'allow_redirects' => 'never'
            ],
            'description' => 'Order from ' . SITE_NAME,
            'metadata' => [
                'user_id' => $_SESSION['user_id'],
                'customer_name' => $full_name,
                'customer_email' => $email,
                'customer_phone' => $phone
            ],
            'receipt_email' => $email
        ]);

        // Check payment status
        if ($paymentIntent->status === 'succeeded') {
            $payment_status = 'paid';
            $transaction_id = $paymentIntent->id;
            $payment_details = [
                'payment_intent' => $paymentIntent->id,
                'amount' => $paymentIntent->amount / 100,
                'currency' => $paymentIntent->currency,
                'status' => $paymentIntent->status,
                'created' => date('Y-m-d H:i:s', $paymentIntent->created)
            ];
        } elseif ($paymentIntent->status === 'requires_action') {
            // Payment requires additional action (3D Secure, etc.)
            $_SESSION['error'] = 'Payment requires additional verification. Please try again.';
            redirect('checkout.php');
        } else {
            $_SESSION['error'] = 'Payment failed. Status: ' . $paymentIntent->status;
            redirect('checkout.php');
        }
        
    } catch (\Stripe\Exception\CardException $e) {
        // Card was declined
        $_SESSION['error'] = 'Card declined: ' . $e->getError()->message;
        redirect('checkout.php');
    } catch (\Stripe\Exception\RateLimitException $e) {
        $_SESSION['error'] = 'Too many requests. Please try again later.';
        redirect('checkout.php');
    } catch (\Stripe\Exception\InvalidRequestException $e) {
        $_SESSION['error'] = 'Invalid payment request: ' . $e->getMessage();
        redirect('checkout.php');
    } catch (\Stripe\Exception\AuthenticationException $e) {
        $_SESSION['error'] = 'Stripe authentication error. Please contact support.';
        error_log('Stripe auth error: ' . $e->getMessage());
        redirect('checkout.php');
    } catch (\Stripe\Exception\ApiConnectionException $e) {
        $_SESSION['error'] = 'Network error. Please check your connection and try again.';
        redirect('checkout.php');
    } catch (\Stripe\Exception\ApiErrorException $e) {
        $_SESSION['error'] = 'Payment processing error: ' . $e->getMessage();
        redirect('checkout.php');
    } catch (Exception $e) {
        $_SESSION['error'] = 'Unexpected error: ' . $e->getMessage();
        error_log('Payment error: ' . $e->getMessage());
        redirect('checkout.php');
    }
    
} elseif ($payment_method === 'cod') {
    // ============== CASH ON DELIVERY ==============
    $payment_status = 'pending';
    $transaction_id = 'COD-' . time() . '-' . $_SESSION['user_id'];
    $payment_details = [
        'method' => 'Cash on Delivery',
        'note' => 'Payment will be collected upon delivery'
    ];
} else {
    $_SESSION['error'] = 'Invalid payment method.';
    redirect('checkout.php');
}

// =====================================================
// CREATE ORDER IN DATABASE
// =====================================================

// Start transaction
$pdo->beginTransaction();

try {
    // Create order
    $stmt = $pdo->prepare("
        INSERT INTO orders (
            user_id, 
            total_amount, 
            shipping_address, 
            phone, 
            payment_method,
            payment_status,
            transaction_id,
            payment_details,
            status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $_SESSION['user_id'],
        $total_amount,
        $shipping_address,
        $phone,
        $payment_method,
        $payment_status,
        $transaction_id,
        json_encode($payment_details),
        'processing' // Order status
    ]);
    
    $order_id = $pdo->lastInsertId();
    
    // Create order items and update inventory
    foreach ($cartItems as $item) {
        // Insert order item
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
        
        // Update stock
        $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$item['quantity'], $item['id']]);
    }
    
    // Clear cart
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    
    // Commit transaction
    $pdo->commit();
    
    // Set success message
    if ($payment_method === 'stripe') {
        $_SESSION['success'] = '✅ Payment successful! Your order has been placed.';
    } else {
        $_SESSION['success'] = '✅ Order placed successfully! Pay cash on delivery.';
    }
    
    // Redirect to confirmation page
    redirect('order-confirmation.php?order_id=' . $order_id);
    
} catch (Exception $e) {
    // Rollback on error
    $pdo->rollBack();
    
    // Log error
    error_log('Order creation failed: ' . $e->getMessage());
    
    // Note: If Stripe payment succeeded but order creation failed,
    // you should implement a refund mechanism here
    // For demo purposes, we'll just show an error
    
    $_SESSION['error'] = 'Order processing failed. Please contact support. Error: ' . $e->getMessage();
    redirect('checkout.php');
}