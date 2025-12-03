<?php
// includes/header.php
// Assumes config.php (and auth helpers) are already included
// before this file is required.

if (!isset($pageTitle)) {
    $pageTitle = '';
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo $pageTitle ? h($pageTitle) . ' - ' : ''; ?>
        <?php echo SITE_NAME; ?>
    </title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <!-- Brand: always goes to store home -->
        <a class="navbar-brand" href="<?php echo SITE_URL; ?>/index.php">
            <?php echo SITE_NAME; ?>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- LEFT SIDE NAV -->
            <ul class="navbar-nav me-auto">
                <?php if (isAdmin()): ?>
                    <!-- ADMIN NAV (shown everywhere for admin users) -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/products.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/view-orders.php">Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/view-reviews.php">Reviews</a>
                    </li>
                <?php else: ?>
                    <!-- CUSTOMER NAV (for non-admin users) -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/products.php">Products</a>
                    </li>

                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/cart.php">
                                Cart
                                <?php
                                // Cart badge only for normal logged-in users
                                $pdo = getDB();
                                $cartCount = 0;
                                try {
                                    $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
                                    $stmt->execute([$_SESSION['user_id']]);
                                    $cartCount = $stmt->fetchColumn() ?: 0;
                                } catch (PDOException $e) {
                                    // ignore
                                }
                                if ($cartCount > 0): ?>
                                    <span class="badge bg-danger"><?php echo $cartCount; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/order-history.php">My Orders</a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>

            <!-- RIGHT SIDE NAV (user + dark-mode) -->
            <ul class="navbar-nav align-items-center">
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item dropdown me-2">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown"
                           role="button" data-bs-toggle="dropdown">
                            <?php echo h($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if (!isAdmin()): ?>
                                <!-- Only useful for normal users -->
                                <li>
                                    <a class="dropdown-item" href="<?php echo SITE_URL; ?>/order-history.php">
                                        My Orders
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php if (isAdmin()): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/dashboard.php">
                                        Admin Panel
                                    </a>
                                </li>
                            <?php endif; ?>

                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="<?php echo SITE_URL; ?>/logout.php">
                                    Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/login.php">Login</a>
                    </li>
                    <li class="nav-item me-2">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/register.php">Register</a>
                    </li>
                <?php endif; ?>

                <!-- Dark mode toggle button (your JS handles it) -->
                <li class="nav-item">
                    <button id="themeToggle" class="btn btn-outline-light btn-sm" type="button">
                        Dark mode
                    </button>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container my-4">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo h($_SESSION['success']); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo h($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
