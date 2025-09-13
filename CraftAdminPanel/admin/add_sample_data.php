<?php
/**
 * Add Sample Data for Tribal Arts Admin Panel
 * Run this to populate your database with test suppliers, categories, and products
 */

require_once 'config/config.php';

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    die("Database connection failed!");
}

echo "<h1>Adding Sample Data to Tribal Arts Database</h1>";

try {
    // Add Categories
    echo "<p>Adding product categories...</p>";
    $categories = [
        ['Traditional Pottery', 'Handcrafted ceramic vessels and sculptures'],
        ['Textiles & Weavings', 'Traditional fabrics, rugs, and woven items'],
        ['Jewelry & Ornaments', 'Handmade jewelry and decorative pieces'],
        ['Wood Carvings', 'Carved wooden sculptures and functional items'],
        ['Paintings & Art', 'Traditional and contemporary tribal artwork']
    ];
    
    $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
    foreach ($categories as $category) {
        $stmt->execute($category);
    }
    echo "<p style='color: green;'>✓ Added " . count($categories) . " categories</p>";
    
    // Add Suppliers/Artisans
    echo "<p>Adding suppliers/artisans...</p>";
    $suppliers = [
        ['Maria Gonzales', 'maria@email.com', '555-0101', 'Hopi Tribe', 'Traditional pottery and ceramics', '123 Reservation Rd, Arizona'],
        ['Joseph Running Bear', 'joseph@email.com', '555-0102', 'Lakota Sioux', 'Beadwork and leather crafts', '456 Tribal Ave, South Dakota'],
        ['Sarah Featherstone', 'sarah@email.com', '555-0103', 'Cherokee Nation', 'Basket weaving and textiles', '789 Heritage St, Oklahoma'],
        ['Robert Stonecrow', 'robert@email.com', '555-0104', 'Navajo Nation', 'Silver jewelry and turquoise work', '321 Canyon Rd, New Mexico'],
        ['Elena Windwalker', 'elena@email.com', '555-0105', 'Pueblo', 'Traditional paintings and art', '654 Mesa Dr, New Mexico']
    ];
    
    $stmt = $conn->prepare("INSERT INTO suppliers (name, email, phone, tribe, specialty, address) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($suppliers as $supplier) {
        $stmt->execute($supplier);
    }
    echo "<p style='color: green;'>✓ Added " . count($suppliers) . " suppliers/artisans</p>";
    
    // Add Sample Products
    echo "<p>Adding sample products...</p>";
    $products = [
        ['Handcrafted Clay Pot', 'Beautiful traditional pottery piece with geometric designs', 89.99, 1, 1, 5],
        ['Beaded Leather Bracelet', 'Traditional beadwork on genuine leather', 34.50, 2, 2, 12],
        ['Woven Pine Needle Basket', 'Intricate basket weaving using traditional techniques', 125.00, 2, 3, 3],
        ['Turquoise Silver Necklace', 'Handmade silver necklace with genuine turquoise stones', 245.00, 3, 4, 2],
        ['Eagle Feather Painting', 'Traditional acrylic painting on canvas', 180.00, 5, 5, 1],
        ['Carved Wooden Bowl', 'Hand-carved serving bowl from reclaimed wood', 67.50, 4, 1, 8],
        ['Dreamcatcher Wall Art', 'Traditional dreamcatcher with natural materials', 45.00, 2, 2, 15],
        ['Ceramic Water Vessel', 'Functional pottery piece with tribal motifs', 95.00, 1, 1, 4]
    ];
    
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, category_id, supplier_id, stock_quantity) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($products as $product) {
        $stmt->execute($product);
    }
    echo "<p style='color: green;'>✓ Added " . count($products) . " products</p>";
    
    // Add Sample Users/Customers
    echo "<p>Adding sample customers...</p>";
    $users = [
        ['John Smith', 'john.smith@email.com', '555-1001', '123 Main St, Denver, CO'],
        ['Lisa Johnson', 'lisa.j@email.com', '555-1002', '456 Oak Ave, Seattle, WA'],
        ['Michael Brown', 'mike.brown@email.com', '555-1003', '789 Pine St, Portland, OR'],
        ['Sarah Davis', 'sarah.d@email.com', '555-1004', '321 Elm Rd, Austin, TX']
    ];
    
    $stmt = $conn->prepare("INSERT INTO users (name, email, phone, address) VALUES (?, ?, ?, ?)");
    foreach ($users as $user) {
        $stmt->execute($user);
    }
    echo "<p style='color: green;'>✓ Added " . count($users) . " customers</p>";
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h3>Sample Data Added Successfully!</h3>";
    echo "<p>Your database now contains:</p>";
    echo "<ul>";
    echo "<li>" . count($categories) . " Product Categories</li>";
    echo "<li>" . count($suppliers) . " Suppliers/Artisans</li>";
    echo "<li>" . count($products) . " Sample Products</li>";
    echo "<li>" . count($users) . " Sample Customers</li>";
    echo "</ul>";
    echo "<p><strong>You can now:</strong></p>";
    echo "<ul>";
    echo "<li>View and manage suppliers in the admin panel</li>";
    echo "<li>Add/edit products with categories and suppliers</li>";
    echo "<li>Manage customer accounts</li>";
    echo "<li>Test all CRUD operations</li>";
    echo "</ul>";
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h3>Error Adding Sample Data!</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Sample Data - Tribal Arts Admin</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1 { color: #8b4513; }
        .btn { display: inline-block; padding: 10px 20px; background: #8b4513; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background: #654321; }
    </style>
</head>
<body>
    <div style="text-align: center; margin-top: 30px;">
        <a href="suppliers.php" class="btn">View Suppliers</a>
        <a href="products.php" class="btn">View Products</a>
        <a href="categories.php" class="btn">View Categories</a>
        <a href="dashboard.php" class="btn">Dashboard</a>
    </div>
</body>
</html>