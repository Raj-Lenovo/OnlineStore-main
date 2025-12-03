<?php
require_once 'includes/config.php';

$pageTitle = 'Search';

$search = trim($_GET['q'] ?? '');

if (empty($search)) {
    redirect('products.php');
}

redirect('products.php?search=' . urlencode($search));

