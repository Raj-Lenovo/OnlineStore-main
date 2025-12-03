<?php
/**
 * Test Access File
 * This file helps verify your XAMPP setup and URL structure
 */

echo "<h1>XAMPP Access Test</h1>";
echo "<p><strong>Current Directory:</strong> " . __DIR__ . "</p>";
echo "<p><strong>Current File:</strong> " . __FILE__ . "</p>";
echo "<p><strong>Server Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Script Name:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p><strong>Request URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";

echo "<hr>";
echo "<h2>File Existence Check</h2>";

$files_to_check = [
    'admin/login.php',
    'admin/dashboard.php',
    'includes/config.php',
    'index.php'
];

foreach ($files_to_check as $file) {
    $exists = file_exists($file);
    $status = $exists ? "✓ EXISTS" : "✗ NOT FOUND";
    $color = $exists ? "green" : "red";
    echo "<p style='color: $color;'><strong>$file:</strong> $status</p>";
}

echo "<hr>";
echo "<h2>Try These URLs:</h2>";
echo "<ul>";
echo "<li><a href='index.php'>Main Store (index.php)</a></li>";
echo "<li><a href='admin/login.php'>Admin Login (admin/login.php)</a></li>";
echo "<li><a href='admin/'>Admin Directory (admin/)</a></li>";
echo "</ul>";

echo "<hr>";
echo "<h2>PHP Info</h2>";
echo "<p><a href='?phpinfo=1'>Click here to view PHP Info</a></p>";

if (isset($_GET['phpinfo'])) {
    phpinfo();
}

