<?php
// admin_suppliers.php
require_once 'auth_check.php';
require_role('admin');

$con = mysqli_connect("localhost", "root", "", "tribal_arts_db") or die("Couldn't connect");

// Handle supplier actions
if (isset($_GET['action'])) {
    $supplier_id = intval($_GET['id']);
    
    switch ($_GET['action']) {
        case 'approve':
            $query = "UPDATE suppliers SET status = 'active' WHERE id = $supplier_id";
            if (mysqli_query($con, $query)) {
                log_activity('approve_supplier', "Approved supplier ID: $supplier_id");
                $success = "Supplier approved successfully.";
            } else {
                $error = "Error approving supplier: " . mysqli_error($con);
            }
            break;
            
        case 'reject':
            $query = "UPDATE suppliers SET status = 'suspended' WHERE id = $supplier_id";
            if (mysqli_query($con, $query)) {
                log_activity('reject_supplier', "Rejected supplier ID: $supplier_id");
                $success = "Supplier rejected successfully.";
            } else {
                $error = "Error rejecting supplier: " . mysqli_error($con);
            }
            break;
            
        case 'delete':
            $query = "DELETE FROM suppliers WHERE id = $supplier_id";
            if (mysqli_query($con, $query)) {
                log_activity('delete_supplier', "Deleted supplier ID: $supplier_id");
                $success = "Supplier deleted successfully.";
            } else {
                $error = "Error deleting supplier: " . mysqli_error($con);
            }
            break;
    }
}

// Get all suppliers with user information
$suppliers = mysqli_query($con, "
    SELECT s.*, u.name, u.email, u.role 
    FROM suppliers s 
    JOIN users u ON s.user_id = u.id 
    ORDER BY s.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Suppliers - Admin Panel</title>
    <link href="https://fonts.googleapis.comcss2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                <h1>Manage Suppliers</h1>
                <div class="user-info">
                    <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></div>
                    <div>
                        <div><?php echo $_SESSION['user_name']; ?></div>
                        <small>Administrator</small>
                    </div>
                </div>
            </div>

            <!-- Suppliers Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Suppliers</h3>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Contact</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($supplier = mysqli_fetch_assoc($suppliers)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($supplier['company_name']); ?></td>
                            <td>
                                <div><?php echo htmlspecialchars($supplier['name']); ?></div>
                                <small><?php echo $supplier['contact_number'] ?: 'N/A'; ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($supplier['email']); ?></td>
                            <td>
                                <span class="badge 
                                    <?php 
                                    switch($supplier['status']) {
                                        case 'active': echo 'badge-success'; break;
                                        case 'pending': echo 'badge-warning'; break;
                                        case 'suspended': echo 'badge-danger'; break;
                                        default: echo 'badge-info';
                                    }
                                    ?>
                                "><?php echo ucfirst($supplier['status']); ?></span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($supplier['created_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <?php if ($supplier['status'] === 'pending'): ?>
                                    <a href="admin_suppliers.php?action=approve&id=<?php echo $supplier['id']; ?>" class="btn btn-success btn-sm">Approve</a>
                                    <a href="admin_suppliers.php?action=reject&id=<?php echo $supplier['id']; ?>" class="btn btn-danger btn-sm">Reject</a>
                                    <?php endif; ?>
                                    <!-- <a href="admin_supplier_view.php?id=<?php echo $supplier['id']; ?>" class="btn btn-primary btn-sm">View</a> -->
                                     <a href="#" class="btn btn-primary btn-sm">View</a>
                                    <a href="admin_suppliers.php?action=delete&id=<?php echo $supplier['id']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Are you sure you want to delete this supplier?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
<?php
// Close database connection
if (isset($con)) {
    mysqli_close($con);
}