<?php
/**
 * IMMEDIATE ADMIN PASSWORD FIX
 * Run this file in your browser to fix admin login
 * URL: http://localhost/OnlineStore/fix_admin_now.php
 */

// Database configuration
$host = 'localhost';
$dbname = 'online_store';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<!DOCTYPE html><html><head><title>Admin Password Fix</title>";
    echo "<style>body{font-family:Arial;max-width:800px;margin:50px auto;padding:20px;}";
    echo ".success{background:#d4edda;border:1px solid #c3e6cb;color:#155724;padding:15px;border-radius:5px;margin:10px 0;}";
    echo ".error{background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;padding:15px;border-radius:5px;margin:10px 0;}";
    echo ".info{background:#d1ecf1;border:1px solid #bee5eb;color:#0c5460;padding:15px;border-radius:5px;margin:10px 0;}";
    echo "pre{background:#f4f4f4;padding:10px;border-radius:5px;overflow-x:auto;}";
    echo "a{color:#007bff;text-decoration:none;} a:hover{text-decoration:underline;}</style></head><body>";
    
    echo "<h1>🔧 Admin Password Fix Tool</h1>";
    
    // Generate correct hash for 'admin123'
    $newPassword = 'admin123';
    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    
    echo "<div class='info'>";
    echo "<strong>Step 1:</strong> Generated new password hash for 'admin123'<br>";
    echo "<strong>Hash:</strong> <pre style='display:inline;'>" . htmlspecialchars($newHash) . "</pre>";
    echo "</div>";
    
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT id, username, email, role, password FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        echo "<div class='error'>";
        echo "<strong>❌ Admin user not found!</strong><br>";
        echo "Creating admin user now...<br>";
        
        // Create admin user
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)");
        $result = $stmt->execute(['admin', 'admin@store.com', $newHash, 'Administrator', 'admin']);
        
        if ($result) {
            echo "<strong>✅ Admin user created successfully!</strong>";
        } else {
            echo "<strong>❌ Failed to create admin user.</strong>";
        }
        echo "</div>";
    } else {
        echo "<div class='info'>";
        echo "<strong>Step 2:</strong> Found admin user in database<br>";
        echo "<strong>Current Info:</strong><br>";
        echo "- ID: " . $admin['id'] . "<br>";
        echo "- Username: " . htmlspecialchars($admin['username']) . "<br>";
        echo "- Email: " . htmlspecialchars($admin['email']) . "<br>";
        echo "- Role: " . htmlspecialchars($admin['role']) . "<br>";
        echo "</div>";
        
        // Update password
        echo "<div class='info'>";
        echo "<strong>Step 3:</strong> Updating admin password...<br>";
        echo "</div>";
        
        $stmt = $pdo->prepare("UPDATE users SET password = ?, role = 'admin' WHERE username = 'admin'");
        $result = $stmt->execute([$newHash]);
        
        if ($result) {
            echo "<div class='success'>";
            echo "<strong>✅ SUCCESS! Admin password has been updated!</strong><br><br>";
            echo "<strong>Login Credentials:</strong><br>";
            echo "Username: <strong>admin</strong><br>";
            echo "Password: <strong>admin123</strong><br><br>";
            
            // Verify password
            if (password_verify($newPassword, $newHash)) {
                echo "✅ Password verification: <strong>PASSED</strong><br>";
            } else {
                echo "❌ Password verification: <strong>FAILED</strong><br>";
            }
            
            echo "<br><strong>⚠️ IMPORTANT:</strong><br>";
            echo "1. Delete this file (fix_admin_now.php) for security<br>";
            echo "2. Try logging in now at: <a href='admin/login.php' target='_blank'>Admin Login</a><br>";
            echo "3. Change your password after first login!<br>";
            echo "</div>";
        } else {
            echo "<div class='error'>";
            echo "<strong>❌ Failed to update password.</strong><br>";
            echo "Please check database permissions.";
            echo "</div>";
        }
    }
    
    // Test login function
    echo "<div class='info'>";
    echo "<strong>Step 4:</strong> Testing password verification...<br>";
    $testStmt = $pdo->prepare("SELECT password FROM users WHERE username = 'admin'");
    $testStmt->execute();
    $testUser = $testStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($testUser && password_verify('admin123', $testUser['password'])) {
        echo "✅ <strong>Password test: SUCCESS!</strong> You can now login with 'admin' / 'admin123'";
    } else {
        echo "❌ <strong>Password test: FAILED</strong> - Please try running this script again";
    }
    echo "</div>";
    
    echo "<hr>";
    echo "<h2>Next Steps:</h2>";
    echo "<ol>";
    echo "<li><a href='admin/login.php'>Try Admin Login Now</a></li>";
    echo "<li>If login works, <strong>DELETE this file (fix_admin_now.php)</strong> immediately!</li>";
    echo "<li>Change your admin password to something more secure</li>";
    echo "</ol>";
    
    echo "<hr>";
    echo "<p><a href='index.php'>← Back to Store</a> | <a href='admin/login.php'>Go to Admin Login →</a></p>";
    
    echo "</body></html>";
    
} catch (PDOException $e) {
    echo "<!DOCTYPE html><html><head><title>Error</title>";
    echo "<style>body{font-family:Arial;max-width:800px;margin:50px auto;padding:20px;}";
    echo ".error{background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;padding:15px;border-radius:5px;}</style></head><body>";
    echo "<div class='error'>";
    echo "<h2>❌ Database Connection Error</h2>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Possible Issues:</strong></p>";
    echo "<ul>";
    echo "<li>Database 'online_store' doesn't exist - Run database.sql first</li>";
    echo "<li>MySQL is not running - Start MySQL in XAMPP</li>";
    echo "<li>Wrong database credentials - Check includes/config.php</li>";
    echo "</ul>";
    echo "<p><strong>Fix:</strong> Make sure you've imported database.sql into phpMyAdmin first!</p>";
    echo "</div>";
    echo "</body></html>";
}

