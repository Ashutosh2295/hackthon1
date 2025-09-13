<div class="top-bar">
    <div class="page-title">
        <?php
        $page = basename($_SERVER['PHP_SELF'], '.php');
        $titles = [
            'dashboard' => 'Dashboard',
            'suppliers' => 'Suppliers/Artisans',
            'users' => 'Customers',
            'products' => 'Products',
            'categories' => 'Categories',
            'orders' => 'Orders',
            'settings' => 'Settings'
        ];
        
        echo $titles[$page] ?? ucfirst($page);
        ?>
    </div>
    
    <div class="admin-info">
        <span>Welcome, <?php echo $_SESSION['admin_username'] ?? 'Guest'; ?></span>
        <div class="admin-avatar">
            <?php echo strtoupper(substr($_SESSION['admin_username'] ?? 'G', 0, 1)); ?>
        </div>
    </div>
</div>