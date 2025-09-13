<?php
require_once 'config/config.php';
requireLogin();

$database = new Database();
$conn = $database->getConnection();

// Get statistics
$stats = [];
try {
    // Count suppliers/artisans
    $stmt = $conn->query("SELECT COUNT(*) as count FROM suppliers");
    $stats['suppliers'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    // Count users
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $stats['users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    // Count products
    $stmt = $conn->query("SELECT COUNT(*) as count FROM products");
    $stats['products'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    // Count categories
    $stmt = $conn->query("SELECT COUNT(*) as count FROM categories");
    $stats['categories'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
} catch (PDOException $e) {
    // Initialize with zeros if tables don't exist yet
    $stats = [
        'suppliers' => 0,
        'users' => 0, 
        'products' => 0,
        'categories' => 0
    ];
}

$message = getMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/admin.css" rel="stylesheet">
</head>
<body>
        
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'includes/topbar.php'; ?>
            
            <div class="content-area">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message['type'] == 'error' ? 'error' : 'success'; ?>">
                        <i class="fas fa-<?php echo $message['type'] == 'error' ? 'exclamation-circle' : 'check-circle'; ?>"></i>
                        <?php echo $message['message']; ?>
                    </div>
                <?php endif; ?>
                
                <div class="stats-grid">
                    <div class="stat-card primary">
                        <div class="stat-number"><?php echo $stats['suppliers']; ?></div>
                        <div class="stat-label">
                            <i class="fas fa-users"></i> Suppliers/Artisans
                        </div>
                    </div>
                    
                    <div class="stat-card secondary">
                        <div class="stat-number"><?php echo $stats['users']; ?></div>
                        <div class="stat-label">
                            <i class="fas fa-user-friends"></i> Customers
                        </div>
                    </div>
                    
                    <div class="stat-card accent">
                        <div class="stat-number"><?php echo $stats['products']; ?></div>
                        <div class="stat-label">
                            <i class="fas fa-box"></i> Products
                        </div>
                    </div>
                    
                    <div class="stat-card success">
                        <div class="stat-number"><?php echo $stats['categories']; ?></div>
                        <div class="stat-label">
                            <i class="fas fa-tags"></i> Categories
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-chart-line"></i> Quick Actions</h4>
                    </div>
                    <div class="card-body">
                        <div class="action-buttons">
                            <a href="suppliers.php" class="btn btn-primary">
                                <i class="fas fa-user-plus"></i> Add New Supplier
                            </a>
                            <a href="products.php" class="btn btn-secondary">
                                <i class="fas fa-plus"></i> Add New Product
                            </a>
                            <a href="users.php" class="btn btn-outline">
                                <i class="fas fa-users"></i> Manage Users
                            </a>
                            <a href="init_db.php" class="btn btn-success">
                                <i class="fas fa-database"></i> Initialize Database
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-info-circle"></i> System Status</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($stats['suppliers'] == 0 && $stats['products'] == 0): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Setup Required:</strong> Click "Initialize Database" to create the required tables and add sample data.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                <strong>System Ready:</strong> Database is initialized and ready for use.
                            </div>
                        <?php endif; ?>
                        
                        <p><strong>Database:</strong> Connected and operational</p>
                        <p><strong>Admin User:</strong> <?php echo $_SESSION['admin_username']; ?></p>
                        <p><strong>Login Time:</strong> <?php echo date('Y-m-d H:i:s', $_SESSION['login_time']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>