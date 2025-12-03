<?php
/**
 * Stripe Payment Configuration - CLEAN VERSION
 */

// ===============================
// AUTOLOAD
// ===============================
$autoloadPath = __DIR__ . '/../vendor/autoload.php';

if (!file_exists($autoloadPath)) {
    die("Stripe PHP library not installed. Run: composer require stripe/stripe-php");
}

require_once $autoloadPath;

// ===============================
// STRIPE KEYS (TEST MODE)
// ===============================
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_xxxxxxxxxxxxx');
define('STRIPE_SECRET_KEY', 'sk_test_xxxxxxxxxxxxx');

// ===============================
// INITIALIZE STRIPE
// ===============================
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
\Stripe\Stripe::setApiVersion('2023-10-16');

// ===============================
// GENERAL SETTINGS
// ===============================
define('STRIPE_CURRENCY', 'cad');
