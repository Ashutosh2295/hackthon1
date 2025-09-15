<?php
// admin_products.php
require_once 'auth_check.php';
require_role('admin');

$con = mysqli_connect("localhost", "root", "", "tribal_arts_db") or die("Couldn't connect");

// Handle product actions
if (isset($_GET['action'])) {
    $product_id = intval($_GET['id']);
    
    switch ($_GET['action']) {
        case 'delete':
            $query = "DELETE FROM products WHERE id = $product_id";
            if (mysqli_query($con, $query)) {
                log_activity('delete_product', "Deleted product ID: $product_id");
                $success = "Product deleted successfully.";
            } else {
                $error = "Error deleting product: " . mysqli_error($con);
            }
            break;
            
        case 'toggle_status':
            $current_status = mysqli_fetch_assoc(mysqli_query($con, "SELECT status FROM products WHERE id = $product_id"))['status'];
            $new_status = $current_status === 'active' ? 'inactive' : 'active';
            $query = "UPDATE products SET status = '$new_status' WHERE id = $product_id";
            if (mysqli_query($con, $query)) {
                log_activity('toggle_product_status', "Changed product ID $product_id status to $new_status");
                $success = "Product status updated successfully.";
            } else {
                $error = "Error updating product status: " . mysqli_error($con);
            }
            break;
    }
}

// Get all products with supplier information
$products = mysqli_query($con, "
    SELECT p.*, s.company_name, u.name as supplier_name 
    FROM products p 
    LEFT JOIN suppliers s ON p.supplier_id = s.id 
    LEFT JOIN users u ON s.user_id = u.id 
    ORDER BY p.created_at DESC
");

// Get suppliers for filter
$suppliers = mysqli_query($con, "SELECT s.id, s.company_name FROM suppliers s");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Reuse admin panel styles from admin_users.php */
        
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --info: #4895ef;
            --warning: #f72585;
            --danger: #e63946;
            --light: #f8f9fa;
            --dark: #212529;
            --bg-color: #f4f7fe;
            --sidebar-width: 250px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: #333;
            min-height: 100vh;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--primary);
            color: white;
            height: 100vh;
            position: fixed;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h2 {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
        }

        .menu-item:hover, .menu-item.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .menu-item i {
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        /* Filters */
        .filters {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .filter-group select, .filter-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        /* Tables */
        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th, .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .table tr:hover {
            background-color: #f8f9fa;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-success { background: #e8f5e9; color: #4caf50; }
        .badge-warning { background: #fff3e0; color: #ff9800; }
        .badge-danger { background: #ffebee; color: #f44336; }
        .badge-info { background: #e3f2fd; color: #2196f3; }
        .badge-primary { background: #e3f2fd; color: var(--primary); }

        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
        }

        .btn-primary { background: var(--primary); color: white; }
        .btn-success { background: #4caf50; color: white; }
        .btn-danger { background: #f44336; color: white; }
        .btn-warning { background: #ff9800; color: white; }

        .btn:hover {
            opacity: 0.9;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
            max-width: 80%;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .modal-title {
            font-size: 1.2rem;
            font-weight: 600;
        }

        .close {
            font-size: 1.5rem;
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        /* Alerts */
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #e8f5e9;
            color: #4caf50;
        }

        .alert-danger {
            background-color: #ffebee;
            color: #f44336;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
            }
            .sidebar-header h2 span, .menu-item span {
                display: none;
            }
            .main-content {
                margin-left: 70px;
            }
        }

        @media (max-width: 768px) {
            .filters {
                flex-direction: column;
            }
        }
    
        /* Add specific styles for products if needed */
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar (same as admin_users.php) -->
         <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-crown"></i> <span>Admin Panel</span></h2>
            </div>
            <div class="sidebar-menu">
                <a href="admin.php" class="menu-item">
                    <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                </a>
                <a href="admin_products.php" class="menu-item">
                    <i class="fas fa-box"></i> <span>Products</span>
                </a>
                <a href="admin_orders.php" class="menu-item">
                    <i class="fas fa-shopping-cart"></i> <span>Orders</span>
                </a>
                <a href="admin_users.php" class="menu-item active">
                    <i class="fas fa-users"></i> <span>Users</span>
                </a>
                <a href="admin_suppliers.php" class="menu-item">
                    <i class="fas fa-truck"></i> <span>Suppliers</span>
                </a>
                <a href="index.php" class="menu-item">
                    <i class="fas fa-home"></i> <span>Back to Site</span>
                </a>
                <a href="logout.php" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
                </a>
            </div>
        </div>
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Manage Products</h1>
                <div class="user-info">
                    <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></div>
                    <div>
                        <div><?php echo $_SESSION['user_name']; ?></div>
                        <small>Administrator</small>
                    </div>
                </div>
            </div>

            <!-- Products Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Products</h3>
                    <a href="add_product.php" class="btn btn-primary">Add New Product</a>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Supplier</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($product = mysqli_fetch_assoc($products)): ?>
                        <tr>
                            <td>
                                <img src="<?php echo !empty($product['image']) ? htmlspecialchars($product['image']) : 'https://via.placeholder.com/50x50?text=No+Image'; ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                            </td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td>₹<?php echo number_format($product['price'], 2); ?></td>
                            <td><?php echo $product['stock_quantity']; ?></td>
                            <td><?php echo $product['company_name'] ?: 'N/A'; ?></td>
                            <td>
                                <span class="badge <?php echo $product['status'] === 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                    <?php echo ucfirst($product['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="admin_product_edit.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <a href="admin_products.php?action=toggle_status&id=<?php echo $product['id']; ?>" class="btn btn-warning btn-sm">
                                        <?php echo $product['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                    </a>
                                    <a href="admin_products.php?action=delete&id=<?php echo $product['id']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleFilter = document.getElementById('role');
            const statusFilter = document.getElementById('status');
            const searchFilter = document.getElementById('search');
            const tableRows = document.querySelectorAll('.table tbody tr');
            
            function filterUsers() {
                const roleValue = roleFilter.value;
                const statusValue = statusFilter.value;
                const searchValue = searchFilter.value.toLowerCase();
                
                tableRows.forEach(row => {
                    const role = row.cells[2].textContent.trim().toLowerCase();
                    const name = row.cells[0].textContent.toLowerCase();
                    const email = row.cells[1].textContent.toLowerCase();
                    
                    const roleMatch = !roleValue || role === roleValue;
                    const statusMatch = !statusValue; // Add status field to users table for this to work
                    const searchMatch = !searchValue || name.includes(searchValue) || email.includes(searchValue);
                    
                    row.style.display = roleMatch && statusMatch && searchMatch ? '' : 'none';
                });
            }
            
            roleFilter.addEventListener('change', filterUsers);
            statusFilter.addEventListener('change', filterUsers);
            searchFilter.addEventListener('input', filterUsers);
        });// Filter functionality (similar to admin_users.php)
    </script>
</body>
</html>
<?php
// Close database connection
if (isset($con)) {
    mysqli_close($con);
}