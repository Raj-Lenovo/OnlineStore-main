<?php
/**
 * Check for Duplicate Products
 * Run this to see if you have duplicate products in the database
 */

require_once 'includes/config.php';

$pdo = getDB();

echo "<!DOCTYPE html><html><head><title>Check Duplicates</title>";
echo "<style>body{font-family:Arial;max-width:1200px;margin:20px auto;padding:20px;}";
echo "table{border-collapse:collapse;width:100%;margin:20px 0;}";
echo "th,td{border:1px solid #ddd;padding:8px;text-align:left;}";
echo "th{background:#4CAF50;color:white;}";
echo ".duplicate{background:#ffebee;}";
echo ".info{background:#e3f2fd;padding:15px;border-radius:5px;margin:10px 0;}</style></head><body>";

echo "<h1>🔍 Duplicate Products Checker</h1>";

// Check for duplicate products by name
$stmt = $pdo->query("SELECT name, COUNT(*) as count FROM products GROUP BY name HAVING count > 1");
$duplicates = $stmt->fetchAll();

if (empty($duplicates)) {
    echo "<div class='info' style='background:#c8e6c9;'>";
    echo "<strong>✅ No duplicate product names found!</strong>";
    echo "</div>";
} else {
    echo "<div class='info' style='background:#ffcdd2;'>";
    echo "<strong>⚠️ Found " . count($duplicates) . " duplicate product name(s):</strong>";
    echo "</div>";
    
    echo "<table>";
    echo "<tr><th>Product Name</th><th>Count</th><th>Action</th></tr>";
    foreach ($duplicates as $dup) {
        echo "<tr class='duplicate'>";
        echo "<td>" . htmlspecialchars($dup['name']) . "</td>";
        echo "<td>" . $dup['count'] . " times</td>";
        echo "<td><a href='?fix=" . urlencode($dup['name']) . "'>View Duplicates</a></td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Show all products
echo "<h2>All Products in Database</h2>";
$stmt = $pdo->query("SELECT id, name, price, stock, created_at FROM products ORDER BY name, id");
$allProducts = $stmt->fetchAll();

echo "<table>";
echo "<tr><th>ID</th><th>Name</th><th>Price</th><th>Stock</th><th>Created</th></tr>";
foreach ($allProducts as $product) {
    $isDuplicate = false;
    foreach ($duplicates as $dup) {
        if ($dup['name'] === $product['name']) {
            $isDuplicate = true;
            break;
        }
    }
    $class = $isDuplicate ? "class='duplicate'" : "";
    echo "<tr $class>";
    echo "<td>" . $product['id'] . "</td>";
    echo "<td>" . htmlspecialchars($product['name']) . "</td>";
    echo "<td>$" . number_format($product['price'], 2) . "</td>";
    echo "<td>" . $product['stock'] . "</td>";
    echo "<td>" . $product['created_at'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Show duplicate details if requested
if (isset($_GET['fix'])) {
    $productName = $_GET['fix'];
    echo "<h2>Duplicate Details for: " . htmlspecialchars($productName) . "</h2>";
    
    $stmt = $pdo->prepare("SELECT * FROM products WHERE name = ? ORDER BY id");
    $stmt->execute([$productName]);
    $dups = $stmt->fetchAll();
    
    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th><th>Price</th><th>Stock</th><th>Category</th><th>Image</th><th>Action</th></tr>";
    foreach ($dups as $index => $dup) {
        echo "<tr class='duplicate'>";
        echo "<td>" . $dup['id'] . "</td>";
        echo "<td>" . htmlspecialchars($dup['name']) . "</td>";
        echo "<td>$" . number_format($dup['price'], 2) . "</td>";
        echo "<td>" . $dup['stock'] . "</td>";
        echo "<td>" . $dup['category_id'] . "</td>";
        echo "<td>" . htmlspecialchars($dup['image'] ?? 'N/A') . "</td>";
        if ($index > 0) {
            echo "<td><a href='?delete=" . $dup['id'] . "' onclick='return confirm(\"Delete product ID " . $dup['id'] . "?\")'>Delete</a></td>";
        } else {
            echo "<td><strong>KEEP (First one)</strong></td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    echo "<p><strong>Note:</strong> Keep the first product (lowest ID), delete the rest.</p>";
}

// Handle deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        echo "<div class='info' style='background:#c8e6c9;'>";
        echo "✅ Product ID $id deleted successfully! <a href='check_duplicates.php'>Refresh</a>";
        echo "</div>";
    } catch (PDOException $e) {
        echo "<div class='info' style='background:#ffcdd2;'>";
        echo "❌ Error: " . htmlspecialchars($e->getMessage());
        echo "</div>";
    }
}

echo "<hr>";
echo "<h2>Quick Fix: Remove All Duplicates</h2>";
echo "<p>This will keep the first product (lowest ID) and delete all duplicates.</p>";
echo "<a href='?remove_all_duplicates=1' onclick='return confirm(\"This will delete duplicate products. Continue?\")' style='background:#f44336;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;'>Remove All Duplicates</a>";

if (isset($_GET['remove_all_duplicates'])) {
    echo "<h3>Removing duplicates...</h3>";
    $removed = 0;
    
    foreach ($duplicates as $dup) {
        $stmt = $pdo->prepare("SELECT id FROM products WHERE name = ? ORDER BY id");
        $stmt->execute([$dup['name']]);
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Keep first, delete rest
        array_shift($ids);
        
        foreach ($ids as $id) {
            try {
                $delStmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
                $delStmt->execute([$id]);
                $removed++;
            } catch (PDOException $e) {
                echo "<p>Error deleting ID $id: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    }
    
    echo "<div class='info' style='background:#c8e6c9;'>";
    echo "✅ Removed $removed duplicate product(s)! <a href='check_duplicates.php'>Refresh to see results</a>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='index.php'>← Back to Store</a> | <a href='admin/dashboard.php'>Admin Panel</a></p>";
echo "</body></html>";

