<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

requireLogin();

$pageTitle = 'Checkout';

$pdo = getDB();

// Get user info
$user = [
    'full_name' => '',
    'address'   => '',
    'phone'     => '',
    'email'     => ''
];

try {
    $stmtUser = $pdo->prepare(
        "SELECT full_name, address, phone, email 
         FROM users 
         WHERE id = ?"
    );
    $stmtUser->execute([$_SESSION['user_id']]);
    $dbUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if ($dbUser) {
        $user['full_name'] = $dbUser['full_name'] ?? '';
        $user['address']   = $dbUser['address']   ?? '';
        $user['phone']     = $dbUser['phone']     ?? '';
        $user['email']     = $dbUser['email']     ?? '';
    }
} catch (PDOException $e) {
    // Leave fields empty on error
}

// Get cart items
$stmt = $pdo->prepare(
    "SELECT c.id AS cart_id, c.quantity,
            p.id, p.name, p.price, p.stock 
     FROM cart c 
     INNER JOIN products p ON c.product_id = p.id 
     WHERE c.user_id = ?"
);
$stmt->execute([$_SESSION['user_id']]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    $_SESSION['error'] = 'Your cart is empty.';
    redirect('cart.php');
}

// Calculate totals
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
$shipping = $subtotal > 100 ? 0 : 10; // Free shipping over $100
$tax = $subtotal * 0.13; // 13% tax
$total = $subtotal + $shipping + $tax;

include 'includes/header.php';
?>

<h2>Checkout</h2>

<div class="row">
    <!-- Left: Shipping & Payment -->
    <div class="col-md-7">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Shipping Information</h5>
            </div>
            <div class="card-body">
                <form id="checkout-form" method="POST" action="process-payment.php">
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name *</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                               value="<?php echo h($user['full_name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo h($user['email']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number *</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?php echo h($user['phone']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="shipping_address" class="form-label">Shipping Address *</label>
                        <textarea class="form-control" id="shipping_address" name="shipping_address" 
                                  rows="3" required><?php echo h($user['address']); ?></textarea>
                        <small class="text-muted">Include street, city, province, and postal code</small>
                    </div>

                    <hr class="my-4">

                    <h5 class="mb-3">Payment Method</h5>
                    
                    <div class="mb-3">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" 
                                   id="stripe" value="stripe" checked>
                            <label class="form-check-label" for="stripe">
                                <strong>💳 Credit/Debit Card (Stripe)</strong>
                                <div class="text-muted small">Secure payment processing</div>
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" 
                                   id="cod" value="cod">
                            <label class="form-check-label" for="cod">
                                <strong>💵 Cash on Delivery</strong>
                                <div class="text-muted small">Pay when you receive your order</div>
                            </label>
                        </div>
                    </div>

                    <!-- Stripe Card Element -->
                    <div id="stripe-payment-section" class="mt-4">
                        <div class="card bg-light border">
                            <div class="card-body">
                                <label class="form-label fw-bold">Card Details</label>
                                <div id="card-element" class="form-control" style="height: 40px; padding-top: 10px;">
                                    <!-- Stripe.js injects card element here -->
                                </div>
                                <div id="card-errors" class="text-danger mt-2 small" role="alert"></div>
                                <div class="mt-3 p-2 bg-warning bg-opacity-10 border border-warning rounded">
                                    <small class="text-dark">
                                        <strong>🧪 TEST MODE:</strong> Use card <code>4242 4242 4242 4242</code>, 
                                        any future expiry (e.g., 12/34), and any 3-digit CVC (e.g., 123)
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="total_amount" value="<?php echo number_format($total, 2, '.', ''); ?>">
                    
                    <button type="submit" class="btn btn-primary btn-lg w-100 mt-4" id="submit-button">
                        <span id="button-text">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-lock-fill me-2" viewBox="0 0 16 16" style="vertical-align: text-bottom;">
                                <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
                            </svg>
                            Place Order - <?php echo formatPrice($total); ?>
                        </span>
                        <span id="button-spinner" class="spinner-border spinner-border-sm d-none" role="status">
                            <span class="visually-hidden">Processing...</span>
                        </span>
                    </button>
                    
                    <div class="text-center mt-3">
                        <small class="text-muted">🔒 Your payment information is secure</small>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right: Order Summary -->
    <div class="col-md-5">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Order Summary</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <?php foreach ($cartItems as $item): ?>
                        const STRIPE_KEY = 'pk_test_51SZbO13XWnonpH6QM0OuM43ZxCbf3mvXt6zjsNClSMaX0jqMSsxnUq1PebVOG96r46bQm74H2u7Izepzolga9myN00zC9D0G5H';
                    <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                        <div>
                            <strong><?php echo h($item['name']); ?></strong><br>
                            <small class="text-muted">Qty: <?php echo $item['quantity']; ?> × <?php echo formatPrice($item['price']); ?></small>
                        </div>
                        <div class="text-end">
                            <strong><?php echo formatPrice($item['price'] * $item['quantity']); ?></strong>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <span><?php echo formatPrice($subtotal); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Shipping:</span>
                    <span class="<?php echo $shipping == 0 ? 'text-success fw-bold' : ''; ?>">
                        <?php echo $shipping > 0 ? formatPrice($shipping) : 'FREE'; ?>
                    </span>
                </div>
                <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                    <span>Tax (13%):</span>
                    <span><?php echo formatPrice($tax); ?></span>
                </div>
                <div class="d-flex justify-content-between fs-5">
                    <strong>Total:</strong>
                    <strong class="text-primary"><?php echo formatPrice($total); ?></strong>
                </div>

                <?php if ($shipping == 0): ?>
                <div class="alert alert-success mt-3 mb-0 small">
                    🎉 You qualify for FREE shipping!
                </div>
                <?php else: ?>
                <div class="alert alert-info mt-3 mb-0 small">
                    💡 Spend <?php echo formatPrice(100 - $subtotal); ?> more for FREE shipping!
                </div>
                <?php endif; ?>
            </div>
        </div>

        <a href="cart.php" class="btn btn-outline-secondary w-100">
            ← Back to Cart
        </a>
    </div>
</div>

<!-- Load Stripe.js -->
<script src="https://js.stripe.com/v3/"></script>

<script>
// IMPORTANT: Replace this with your Stripe publishable TEST key
// Get it from: https://dashboard.stripe.com/test/apikeys
const STRIPE_KEY = 'pk_test_51QP6wJ00dLp6OAQ0J0CiLLVqRYMnCo6r8H3yjYCRYmFyJDkCVBZW6t8xwx5W8yQWGXHXD';

// Initialize Stripe
const stripe = Stripe(STRIPE_KEY);
const elements = stripe.elements();

// Create card element with styling
const cardElement = elements.create('card', {
    style: {
        base: {
            fontSize: '16px',
            color: '#32325d',
            fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
            '::placeholder': {
                color: '#aab7c4'
            }
        },
        invalid: {
            color: '#dc3545',
            iconColor: '#dc3545'
        }
    }
});

cardElement.mount('#card-element');

// Handle real-time validation errors
cardElement.on('change', function(event) {
    const displayError = document.getElementById('card-errors');
    if (event.error) {
        displayError.textContent = event.error.message;
    } else {
        displayError.textContent = '';
    }
});

// Show/hide Stripe section based on payment method
const stripeRadio = document.getElementById('stripe');
const codRadio = document.getElementById('cod');
const stripeSection = document.getElementById('stripe-payment-section');

function toggleStripeSection() {
    stripeSection.style.display = stripeRadio.checked ? 'block' : 'none';
}

stripeRadio.addEventListener('change', toggleStripeSection);
codRadio.addEventListener('change', toggleStripeSection);
toggleStripeSection(); // Initialize on load

// Handle form submission
const form = document.getElementById('checkout-form');
const submitButton = document.getElementById('submit-button');
const buttonText = document.getElementById('button-text');
const buttonSpinner = document.getElementById('button-spinner');

form.addEventListener('submit', async function(event) {
    event.preventDefault();

    // Disable submit button
    submitButton.disabled = true;
    buttonText.classList.add('d-none');
    buttonSpinner.classList.remove('d-none');

    // Check which payment method is selected
    if (codRadio.checked) {
        // Cash on delivery - submit form directly
        form.submit();
        return;
    }

    // Stripe payment - create payment method
    try {
        const {paymentMethod, error} = await stripe.createPaymentMethod({
            type: 'card',
            card: cardElement,
            billing_details: {
                name: document.getElementById('full_name').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value,
                address: {
                    line1: document.getElementById('shipping_address').value
                }
            }
        });

        if (error) {
            // Show error
            document.getElementById('card-errors').textContent = error.message;
            submitButton.disabled = false;
            buttonText.classList.remove('d-none');
            buttonSpinner.classList.add('d-none');
        } else {
            // Add payment method ID to form and submit
            const hiddenInput = document.createElement('input');
            hiddenInput.setAttribute('type', 'hidden');
            hiddenInput.setAttribute('name', 'payment_method_id');
            hiddenInput.setAttribute('value', paymentMethod.id);
            form.appendChild(hiddenInput);
            
            // Submit form
            form.submit();
        }
    } catch (err) {
        console.error('Stripe error:', err);
        document.getElementById('card-errors').textContent = 'Payment processing error. Please try again.';
        submitButton.disabled = false;
        buttonText.classList.remove('d-none');
        buttonSpinner.classList.add('d-none');
    }
});
</script>

<?php include 'includes/footer.php'; ?>