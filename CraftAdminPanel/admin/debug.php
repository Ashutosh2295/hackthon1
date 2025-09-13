<?php
/**
 * Debug page to check database and session status
 */
require_once 'config/config.php';

echo "<h1>Debug Info for Tribal Arts Admin Panel</h1>";

// Check session
echo "<h2>1. Session Status</h2>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Admin ID:</strong> " . ($_SESSION['admin_id'] ?? 'NOT SET') . "</p>";
echo "<p><strong>Admin Username:</strong> " . ($_SESSION['admin_username'] ?? 'NOT SET') . "</p>";
echo "<p><strong>Login Time:</strong> " . ($_SESSION['login_time'] ?? 'NOT SET') . "</p>";
echo "<p><strong>Is Logged In:</strong> " . (isLoggedIn() ? 'YES' : 'NO') . "</p>";

// Check database
echo "<h2>2. Database Status</h2>";
$database = new Database();
$conn = $database->getConnection();

if ($conn) {
    echo "<p style='color: green;'>✓ Database connected successfully</p>";
    
    try {
        // Check suppliers
        $stmt = $conn->query("SELECT COUNT(*) as count FROM suppliers");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p><strong>Total Suppliers:</strong> " . $result['count'] . "</p>";
        
        if ($result['count'] > 0) {
            echo "<h3>Recent Suppliers:</h3>";
            $stmt = $conn->query("SELECT name, email, tribe, specialty, created_at FROM suppliers ORDER BY created_at DESC LIMIT 10");
            $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>Name</th><th>Email</th><th>Tribe</th><th>Specialty</th><th>Created</th></tr>";
            foreach ($suppliers as $supplier) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($supplier['name']) . "</td>";
                echo "<td>" . htmlspecialchars($supplier['email']) . "</td>";
                echo "<td>" . htmlspecialchars($supplier['tribe'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($supplier['specialty'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($supplier['created_at'] ?? 'N/A') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Database query error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Database connection failed</p>";
}

// Check admin table
echo "<h2>3. Admin Account Status</h2>";
try {
    $stmt = $conn->query("SELECT username, created_at FROM admin");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($admins) {
        echo "<p style='color: green;'>✓ Admin accounts found:</p>";
        foreach ($admins as $admin) {
            echo "<p>- Username: <strong>" . htmlspecialchars($admin['username']) . "</strong> (Created: " . $admin['created_at'] . ")</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠ No admin accounts found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Admin query error: " . $e->getMessage() . "</p>";
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Debug - Tribal Arts Admin</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 0 auto; padding: 20px; }
        h1, h2, h3 { color: #8b4513; }
        table { width: 100%; }
        th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .btn { display: inline-block; padding: 10px 20px; background: #8b4513; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background: #654321; }
    </style>
</head>
<body>
    <div style="background: #e8f5e8; border: 1px solid #c3e6cb; padding: 15px; margin: 20px 0; border-radius: 5px;">
        <h3>Quick Fix Steps:</h3>
        <ol>
            <li><strong>Login Again:</strong> Go to <a href="login.php">login.php</a> and use: admin / admin123</li>
            <li><strong>View Suppliers:</strong> After login, go to <a href="suppliers.php">suppliers.php</a></li>
            <li><strong>Your data is safe!</strong> All suppliers you added are in the database (see above)</li>
        </ol>
    </div>
    
    <div style="text-align: center; margin-top: 30px;">
        <a href="login.php" class="btn">Go to Login</a>
        <a href="suppliers.php" class="btn">Try Suppliers Page</a>
        <a href="dashboard.php" class="btn">Dashboard</a>
    </div>
</body>
</html>