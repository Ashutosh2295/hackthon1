<?php
/**
 * XAMPP Setup Script for Tribal Arts Admin Panel
 * Run this file once to set up the MySQL database and initial admin user
 */

// Database configuration for XAMPP
$host = "localhost";
$username = "root";  
$password = "";      // Default XAMPP MySQL password is empty
$dbname = "tribal_arts_db";

echo "<h1>Setting up Tribal Arts Admin Panel for XAMPP</h1>";

try {
    // Step 1: Connect to MySQL server (without specifying database)
    echo "<p>1. Connecting to MySQL server...</p>";
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✓ Connected to MySQL server</p>";
    
    // Step 2: Create database if it doesn't exist
    echo "<p>2. Creating database '$dbname'...</p>";
    $conn->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p style='color: green;'>✓ Database '$dbname' created</p>";
    
    // Step 3: Connect to the specific database
    echo "<p>3. Connecting to database '$dbname'...</p>";
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✓ Connected to database '$dbname'</p>";
    
    // Step 4: Create admin table
    echo "<p>4. Creating admin table...</p>";
    $sql = "CREATE TABLE IF NOT EXISTS admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "<p style='color: green;'>✓ Admin table created</p>";
    
    // Step 5: Create initial admin user
    echo "<p>5. Creating initial admin user...</p>";
    $admin_username = 'admin';
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    
    // Check if admin already exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM admin WHERE username = ?");
    $stmt->execute([$admin_username]);
    
    if ($stmt->fetchColumn() == 0) {
        $stmt = $conn->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
        $stmt->execute([$admin_username, $admin_password]);
        echo "<p style='color: green;'>✓ Initial admin user created</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Admin user already exists</p>";
    }
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h3>Setup Complete!</h3>";
    echo "<p><strong>Database:</strong> $dbname</p>";
    echo "<p><strong>Admin Username:</strong> admin</p>";
    echo "<p><strong>Admin Password:</strong> admin123</p>";
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ol>";
    echo "<li>Go to <a href='login.php' target='_blank'>login.php</a> to access the admin panel</li>";
    echo "<li>Login with the credentials above</li>";
    echo "<li>Go to the initialization page to create all tables and sample data</li>";
    echo "</ol>";
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h3>Setup Error!</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p><strong>Common solutions:</strong></p>";
    echo "<ul>";
    echo "<li>Make sure XAMPP is running (Apache & MySQL)</li>";
    echo "<li>Check that MySQL service is started in XAMPP Control Panel</li>";
    echo "<li>Verify MySQL username/password (default is root with empty password)</li>";
    echo "</ul>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>XAMPP Setup - Tribal Arts Admin</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1 { color: #8b4513; }
        .btn { display: inline-block; padding: 10px 20px; background: #8b4513; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        .btn:hover { background: #654321; }
    </style>
</head>
<body>
    <div style="text-align: center; margin-top: 30px;">
        <a href="login.php" class="btn">Go to Admin Login</a>
        <a href="init_db.php" class="btn">Initialize Database Tables</a>
    </div>
</body>
</html>