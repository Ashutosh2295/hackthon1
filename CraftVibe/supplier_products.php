<?php
// supplier_products.php
require_once 'auth_check.php';
require_role('supplier');

$con = mysqli_connect("localhost", "root", "", "tribal_arts_db") or die("Couldn't connect");

// Get supplier information
$user_id = $_SESSION['user_id'];
$supplier_query = mysqli_query($con, "SELECT * FROM suppliers WHERE user_id = $user_id");
$supplier = mysqli_fetch_assoc($supplier_query);

// Check if supplier exists
if (!$supplier) {
    header("Location: supplier_profile_complete.php");
    exit();
}

// Handle product actions
$success = '';
$error = '';
if (isset($_GET['action'])) {
    $product_id = intval($_GET['id']);
    
    switch ($_GET['action']) {
        case 'delete':
            $query = "DELETE FROM products WHERE id = $product_id AND supplier_id = {$supplier['id']}";
            if (mysqli_query($con, $query)) {
                log_activity('delete_product', "Deleted product ID: $product_id");
                $success = "Product deleted successfully.";
            } else {
                $error = "Error deleting product: " . mysqli_error($con);
            }
            break;
            
        case 'toggle_status':
            $current_status = mysqli_fetch_assoc(mysqli_query($con, "SELECT status FROM products WHERE id = $product_id AND supplier_id = {$supplier['id']}"))['status'];
            $new_status = $current_status === 'active' ? 'inactive' : 'active';
            $query = "UPDATE products SET status = '$new_status' WHERE id = $product_id AND supplier_id = {$supplier['id']}";
            if (mysqli_query($con, $query)) {
                log_activity('toggle_product_status', "Changed product ID $product_id status to $new_status");
                $success = "Product status updated successfully.";
            } else {
                $error = "Error updating product status: " . mysqli_error($con);
            }
            break;
    }
}

// Get supplier's products
$products = mysqli_query($con, "
    SELECT * FROM products 
    WHERE supplier_id = {$supplier['id']}
    ORDER BY created_at DESC
");

// Handle search and filter
$search = '';
$category = '';
$status = '';

if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($con, $_GET['search']);
}

if (isset($_GET['category'])) {
    $category = mysqli_real_escape_string($con, $_GET['category']);
}

if (isset($_GET['status'])) {
    $status = mysqli_real_escape_string($con, $_GET['status']);
}

// Build query with filters
$query = "SELECT * FROM products WHERE supplier_id = {$supplier['id']}";

if (!empty($search)) {
    $query .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
}

if (!empty($category) && $category !== 'all') {
    $query .= " AND category = '$category'";
}

if (!empty($status) && $status !== 'all') {
    $query .= " AND status = '$status'";
}

$query .= " ORDER BY created_at DESC";

$products = mysqli_query($con, $query);

// Get unique categories for filter
$categories_query = mysqli_query($con, "SELECT DISTINCT category FROM products WHERE supplier_id = {$supplier['id']} AND category IS NOT NULL");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Products - Supplier Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
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

        .supplier-container {
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

        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #e8f5e9;
            color: #4caf50;
            border: 1px solid #c8e6c9;
        }

        .alert-danger {
            background-color: #ffebee;
            color: #f44336;
            border: 1px solid #ffcdd2;
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
    </style>
</head>
<body>
    <div class="supplier-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-truck"></i> <span>Supplier Panel</span></h2>
            </div>
            <div class="sidebar-menu">
                <a href="supplier.php" class="menu-item">
                    <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                </a>
                <a href="supplier_product.php" class="menu-item active">
                    <i class="fas fa-box"></i> <span>My Products</span>
                </a>
                <a href="supplier_orders.php" class="menu-item">
                    <i class="fas fa-shopping-cart"></i> <span>Orders</span>
                </a>
                <a href="supplier_profile.php" class="menu-item">
                    <i class="fas fa-user"></i> <span>Profile</span>
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
                <h1>My Products</h1>
                <div class="user-info">
                    <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></div>
                    <div>
                        <div><?php echo $_SESSION['user_name']; ?></div>
                        <small><?php echo $supplier['company_name']; ?></small>
                    </div>
                </div>
            </div>

            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="filters">
                <div class="filter-group">
                    <label for="search">Search Products</label>
                    <input type="text" id="search" name="search" placeholder="Search by name or description" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="filter-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="all">All Categories</option>
                        <?php while($cat = mysqli_fetch_assoc($categories_query)): ?>
                        <option value="<?php echo $cat['category']; ?>" <?php echo $category === $cat['category'] ? 'selected' : ''; ?>>
                            <?php echo ucfirst($cat['category']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="all">All Statuses</option>
                        <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        <option value="out_of_stock" <?php echo $status === 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>&nbsp;</label>
                    <button type="button" id="applyFilters" class="btn btn-primary">Apply Filters</button>
                </div>
            </div>

            <!-- Products Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">My Products</h3>
                    <a href="supplier_product_add.php" class="btn btn-primary">Add New Product</a>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($products && mysqli_num_rows($products) > 0): ?>
                            <?php while($product = mysqli_fetch_assoc($products)): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo !empty($product['image']) ? htmlspecialchars($product['image']) : 'https://via.placeholder.com/50x50?text=No+Image'; ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                                </td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td>₹<?php echo number_format($product['price'], 2); ?></td>
                                <td><?php echo $product['stock_quantity']; ?></td>
                                <td><?php echo $product['category'] ? ucfirst($product['category']) : 'N/A'; ?></td>
                                <td>
                                    <span class="badge <?php echo $product['status'] === 'active' ? 'badge-success' : ($product['status'] === 'out_of_stock' ? 'badge-danger' : 'badge-warning'); ?>">
                                        <?php echo ucfirst($product['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="supplier_product_edit.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                        <a href="supplier_products.php?action=toggle_status&id=<?php echo $product['id']; ?>" class="btn btn-warning btn-sm">
                                            <?php echo $product['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                        </a>
                                        <a href="supplier_products.php?action=delete&id=<?php echo $product['id']; ?>" 
                                           class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No products found. <a href="supplier_product_add.php">Add your first product</a></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Filter functionality
        document.getElementById('applyFilters').addEventListener('click', function() {
            const search = document.getElementById('search').value;
            const category = document.getElementById('category').value;
            const status = document.getElementById('status').value;
            
            let url = 'supplier_products.php?';
            
            if (search) url += `search=${encodeURIComponent(search)}&`;
            if (category !== 'all') url += `category=${encodeURIComponent(category)}&`;
            if (status !== 'all') url += `status=${encodeURIComponent(status)}&`;
            
            // Remove trailing & if present
            if (url.endsWith('&')) url = url.slice(0, -1);
            
            window.location.href = url;
        });

        // Enter key to search
        document.getElementById('search').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('applyFilters').click();
            }
        });
    </script>
</body>
</html>
<?php
// Close database connection
if (isset($con)) {
    mysqli_close($con);
}