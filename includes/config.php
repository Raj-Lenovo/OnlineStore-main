<?php
// ===========================
// Database configuration
// ===========================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'online_store');

// ===========================
// Path & Site configuration
// ===========================

// Absolute path to the project root on disk
// (one level up from this config file: e.g. C:\xampp\htdocs\OnlineStore)
define('BASE_PATH', realpath(__DIR__ . '/..'));

// Detect protocol & host (works on localhost and real servers)
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Name of the project folder (e.g. "OnlineStore")
$projectFolder = basename(BASE_PATH);

// Base URL of the site, e.g. http://localhost/OnlineStore
// This is what getImageUrl() uses to build image URLs
define('SITE_URL', $scheme . '://' . $host . '/' . $projectFolder);

// Human-readable site name
define('SITE_NAME', 'Online Computer Store');

// ===========================
// Session
// ===========================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===========================
// Database connection helper
// ===========================
function getDB() {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // In production, log this instead of die()
            die("Database connection failed: " . $e->getMessage());
        }
    }

    return $pdo;
}

// ===========================
// Helper functions
// ===========================

// Simple redirect helper
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Require login for a page
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = "Please login to access this page.";
        redirect('login.php');
    }
}

// Require admin access for a page
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        $_SESSION['error'] = "Access denied. Admin privileges required.";

        // Check if we're in admin folder
        $currentDir = dirname($_SERVER['PHP_SELF']);
        if (strpos($currentDir, '/admin') !== false || strpos($currentDir, '\\admin') !== false) {
            redirect('../index.php');
        } else {
            redirect('index.php');
        }
    }
}

// Format price nicely
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

// Escape output (XSS protection)
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
