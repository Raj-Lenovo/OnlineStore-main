<?php
/**
 * Admin Password Fix Script
 * Run this file once to fix the admin password
 * Then delete this file for security
 */

require_once 'includes/config.php';

// Generate correct password hash for 'admin123'
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "<h2>Admin Password Fix</h2>";
echo "<p>Generated hash for 'admin123':</p>";
echo "<pre>" . $hash . "</pre>";

try {
    $pdo = getDB();
    
    // Update admin password
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
    $result = $stmt->execute([$hash]);
    
    if ($result) {
        echo "<div style='color: green; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>✓ Success!</strong> Admin password has been updated.<br>";
        echo "Username: <strong>admin</strong><br>";
        echo "Password: <strong>admin123</strong><br>";
        echo "<br><strong>⚠️ IMPORTANT:</strong> Delete this file (fix_admin_password.php) after use for security!";
        echo "</div>";
        
        // Verify the password works
        if (password_verify('admin123', $hash)) {
            echo "<div style='color: green; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;'>";
            echo "✓ Password verification successful! You can now login.";
            echo "</div>";
        }
    } else {
        echo "<div style='color: red; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0;'>";
        echo "✗ Failed to update password. Please check database connection.";
        echo "</div>";
    }
    
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<div style='padding: 10px; background: #e7f3ff; border: 1px solid #b3d9ff; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>Admin User Info:</strong><br>";
        echo "ID: " . $admin['id'] . "<br>";
        echo "Username: " . $admin['username'] . "<br>";
        echo "Role: " . $admin['role'] . "<br>";
        echo "</div>";
    } else {
        echo "<div style='color: orange; padding: 10px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; margin: 10px 0;'>";
        echo "⚠️ Admin user not found. You may need to run the database.sql file first.";
        echo "</div>";
    }
    
} catch (PDOException $e) {
    echo "<div style='color: red; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0;'>";
    echo "✗ Database Error: " . $e->getMessage();
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='admin/login.php'>Go to Admin Login</a> | <a href='index.php'>Back to Store</a></p>";

