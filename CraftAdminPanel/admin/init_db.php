<?php
require_once 'config/config.php';

// Allow access if no admin users exist (bootstrap) or already logged in
$database = new Database();
$conn = $database->getConnection();

try {
    $stmt = $conn->query("SELECT COUNT(*) as count FROM admin");
    $admin_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // If admins exist, require login
    if ($admin_count > 0) {
        requireLogin();
    }
} catch (PDOException $e) {
    // Table doesn't exist yet, allow access for bootstrap
}

if ($_POST && isset($_POST['initialize'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        setMessage('Security token mismatch. Please try again.', 'error');
    } else {
    try {
        // Determine database type
        $is_postgres = strpos($conn->getAttribute(PDO::ATTR_DRIVER_NAME), 'pgsql') !== false;
        
        // Create admin table
        if ($is_postgres) {
            $sql = "CREATE TABLE IF NOT EXISTS admin (
                id SERIAL PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        } else {
            $sql = "CREATE TABLE IF NOT EXISTS admin (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        }
        $conn->exec($sql);
        
        // Create suppliers table
        if ($is_postgres) {
            $sql = "CREATE TABLE IF NOT EXISTS suppliers (
                id SERIAL PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                tribe VARCHAR(100),
                specialty TEXT,
                email VARCHAR(100),
                phone VARCHAR(20),
                address TEXT,
                image VARCHAR(255),
                bio TEXT,
                status VARCHAR(20) DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        } else {
            $sql = "CREATE TABLE IF NOT EXISTS suppliers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                tribe VARCHAR(100),
                specialty TEXT,
                email VARCHAR(100),
                phone VARCHAR(20),
                address TEXT,
                image VARCHAR(255),
                bio TEXT,
                status VARCHAR(20) DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        }
        $conn->exec($sql);
        
        // Create categories table
        if ($is_postgres) {
            $sql = "CREATE TABLE IF NOT EXISTS categories (
                id SERIAL PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                image VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        } else {
            $sql = "CREATE TABLE IF NOT EXISTS categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                image VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        }
        $conn->exec($sql);
        
        // Create products table
        if ($is_postgres) {
            $sql = "CREATE TABLE IF NOT EXISTS products (
                id SERIAL PRIMARY KEY,
                name VARCHAR(200) NOT NULL,
                description TEXT,
                price DECIMAL(10,2) NOT NULL,
                category_id INT REFERENCES categories(id),
                supplier_id INT REFERENCES suppliers(id),
                image VARCHAR(255),
                stock_quantity INT DEFAULT 0,
                status VARCHAR(20) DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        } else {
            $sql = "CREATE TABLE IF NOT EXISTS products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(200) NOT NULL,
                description TEXT,
                price DECIMAL(10,2) NOT NULL,
                category_id INT,
                supplier_id INT,
                image VARCHAR(255),
                stock_quantity INT DEFAULT 0,
                status VARCHAR(20) DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (category_id) REFERENCES categories(id),
                FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
            )";
        }
        $conn->exec($sql);
        
        // Create users table
        if ($is_postgres) {
            $sql = "CREATE TABLE IF NOT EXISTS users (
                id SERIAL PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255),
                phone VARCHAR(20),
                address TEXT,
                status VARCHAR(20) DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        } else {
            $sql = "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255),
                phone VARCHAR(20),
                address TEXT,
                status VARCHAR(20) DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        }
        $conn->exec($sql);
        
        // Insert default admin user
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO admin (username, password) VALUES (?, ?) ON CONFLICT (username) DO NOTHING");
        if (!$is_postgres) {
            $stmt = $conn->prepare("INSERT IGNORE INTO admin (username, password) VALUES (?, ?)");
        }
        $stmt->execute(['admin', $admin_password]);
        
        // Insert sample categories
        $categories = [
            ['Pottery & Ceramics', 'Traditional clay pottery and ceramic art pieces'],
            ['Textiles & Weavings', 'Handwoven fabrics, rugs, and textile art'],
            ['Jewelry & Accessories', 'Traditional ornaments and decorative accessories'], 
            ['Wood Crafts', 'Carved wooden sculptures and functional items'],
            ['Metalwork', 'Forged and crafted metal art pieces'],
            ['Stone Carvings', 'Sculpted stone and rock art']
        ];
        
        foreach ($categories as $category) {
            $stmt = $conn->prepare("INSERT INTO categories (name, description) SELECT ?, ? WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = ?)");
            $stmt->execute([$category[0], $category[1], $category[0]]);
        }
        
        // Insert sample suppliers
        $suppliers = [
            ['Maya Patel', 'Cherokee', 'Traditional pottery and ceramic art', 'maya@example.com', '+1-555-0101'],
            ['Joseph Running Bear', 'Lakota', 'Beadwork and leather crafts', 'joseph@example.com', '+1-555-0102'],
            ['Sarah Windwalker', 'Navajo', 'Textile weaving and rugs', 'sarah@example.com', '+1-555-0103'],
            ['David Featherstone', 'Hopi', 'Silver jewelry and metalwork', 'david@example.com', '+1-555-0104'],
            ['Elena Craftwater', 'Pueblo', 'Stone carving and sculptures', 'elena@example.com', '+1-555-0105']
        ];
        
        foreach ($suppliers as $supplier) {
            $stmt = $conn->prepare("INSERT INTO suppliers (name, tribe, specialty, email, phone) SELECT ?, ?, ?, ?, ? WHERE NOT EXISTS (SELECT 1 FROM suppliers WHERE email = ?)");
            $stmt->execute([$supplier[0], $supplier[1], $supplier[2], $supplier[3], $supplier[4], $supplier[3]]);
        }
        
        setMessage('Database initialized successfully with sample data!', 'success');
        header('Location: dashboard.php');
        exit();
        
    } catch (PDOException $e) {
        setMessage('Error initializing database: ' . $e->getMessage(), 'error');
    }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Initialize Database - <?php echo SITE_NAME; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'includes/topbar.php'; ?>
            
            <div class="content-area">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-database"></i> Initialize Database</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Warning:</strong> This will create the required database tables and add sample data.
                        </div>
                        
                        <p>This process will create the following tables:</p>
                        <ul>
                            <li><strong>Admin:</strong> Administrator accounts</li>
                            <li><strong>Suppliers:</strong> Artisan and supplier information</li>
                            <li><strong>Categories:</strong> Product categories</li>
                            <li><strong>Products:</strong> Product inventory</li>
                            <li><strong>Users:</strong> Customer accounts</li>
                        </ul>
                        
                        <p>Sample data will be added including:</p>
                        <ul>
                            <li>Default admin account (username: admin, password: admin123)</li>
                            <li>Product categories for tribal arts</li>
                            <li>Sample supplier/artisan profiles</li>
                        </ul>
                        
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <button type="submit" name="initialize" value="1" class="btn btn-primary">
                                <i class="fas fa-database"></i> Initialize Database
                            </button>
                            <a href="dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>