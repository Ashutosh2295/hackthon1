<?php
/**
 * Create Orders Table for XAMPP MySQL Database
 */

// Database configuration for XAMPP
$host = "localhost";
$username = "root";  
$password = "";      
$dbname = "tribal_arts_db";

echo "<h1>Creating Orders Table</h1>";

try {
    // Connect to the database
    echo "<p>1. Connecting to MySQL database '$dbname'...</p>";
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✓ Connected to database '$dbname'</p>";
    
    // Create orders table
    echo "<p>2. Creating orders table...</p>";
    $sql = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        status VARCHAR(50) DEFAULT 'pending',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_status (status),
        INDEX idx_created_at (created_at)
    )";
    
    $conn->exec($sql);
    echo "<p style='color: green;'>✓ Orders table created successfully</p>";
    
    // Add a sample order
    echo "<p>3. Adding sample order...</p>";
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, notes) VALUES (?, ?, ?, ?)");
    $stmt->execute([null, 125.50, 'pending', 'Sample guest order - Pottery and textile items']);
    echo "<p style='color: green;'>✓ Sample order added</p>";
    
    // Check total orders
    $stmt = $conn->query("SELECT COUNT(*) as count FROM orders");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h3>Orders Table Created Successfully!</h3>";
    echo "<p><strong>Database:</strong> $dbname</p>";
    echo "<p><strong>Table:</strong> orders</p>";
    echo "<p><strong>Sample Orders:</strong> " . $result['count'] . "</p>";
    echo "<p><strong>Features:</strong></p>";
    echo "<ul>";
    echo "<li>Order tracking with unique IDs</li>";
    echo "<li>Customer linking (optional)</li>";
    echo "<li>Status management (pending, processing, shipped, delivered, cancelled)</li>";
    echo "<li>Order amounts and notes</li>";
    echo "<li>Creation and update timestamps</li>";
    echo "</ul>";
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h3>Error Creating Orders Table!</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p><strong>Common solutions:</strong></p>";
    echo "<ul>";
    echo "<li>Make sure XAMPP is running (Apache & MySQL)</li>";
    echo "<li>Check that the tribal_arts_db database exists</li>";
    echo "<li>Verify MySQL is accessible</li>";
    echo "</ul>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Orders Table - Tribal Arts Admin</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1 { color: #8b4513; }
        .btn { display: inline-block; padding: 10px 20px; background: #8b4513; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background: #654321; }
    </style>
</head>
<body>
    <div style="text-align: center; margin-top: 30px;">
        <a href="orders.php" class="btn">Go to Orders Page</a>
        <a href="dashboard.php" class="btn">Dashboard</a>
        <a href="login.php" class="btn">Login</a>
    </div>
</body>
</html>