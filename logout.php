<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

logoutUser();
$_SESSION['success'] = 'You have been logged out successfully.';
redirect('index.php');

