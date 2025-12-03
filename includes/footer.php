    </main>

    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?php echo SITE_NAME; ?></h5>
                    <p>Your trusted source for quality computer products and accessories.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo SITE_URL; ?>/index.php" class="text-light">Home</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/products.php" class="text-light">Products</a></li>
                        <?php if (isLoggedIn()): ?>
                            <li><a href="<?php echo SITE_URL; ?>/order-history.php" class="text-light">My Orders</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <hr class="bg-light">
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Dark / light mode toggle logic -->
    <script src="<?php echo SITE_URL; ?>/js/darkmodetoggle.js"></script>
    <!-- Your other custom JS (if any) -->
    <script src="<?php echo SITE_URL; ?>/js/main.js"></script>
</body>
</html>
