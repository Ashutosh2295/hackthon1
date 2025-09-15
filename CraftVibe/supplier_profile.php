<?php
// supplier_profile.php
require_once 'auth_check.php';
require_role('supplier');

$con = mysqli_connect("localhost", "root", "", "tribal_arts_db") or die("Couldn't connect");

// Get supplier information
$user_id = $_SESSION['user_id'];
$supplier_query = mysqli_query($con, "SELECT * FROM suppliers WHERE user_id = $user_id");
$supplier = mysqli_fetch_assoc($supplier_query);

// Check if supplier exists
if (!$supplier) {
    // Create a basic supplier profile if it doesn't exist
    $user_query = mysqli_query($con, "SELECT * FROM users WHERE id = $user_id");
    $user = mysqli_fetch_assoc($user_query);
    
    $company_name = $user['name'] . "'s Business";
    $insert_query = "INSERT INTO suppliers (user_id, company_name, status) VALUES ($user_id, '$company_name', 'active')";
    
    if (mysqli_query($con, $insert_query)) {
        $supplier_query = mysqli_query($con, "SELECT * FROM suppliers WHERE user_id = $user_id");
        $supplier = mysqli_fetch_assoc($supplier_query);
    } else {
        die("Error creating supplier profile: " . mysqli_error($con));
    }
}

// Handle form submission
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name = mysqli_real_escape_string($con, $_POST['company_name']);
    $contact_number = mysqli_real_escape_string($con, $_POST['contact_number']);
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $business_registration = mysqli_real_escape_string($con, $_POST['business_registration']);
    $tax_id = mysqli_real_escape_string($con, $_POST['tax_id']);
    
    $update_query = "UPDATE suppliers SET 
                    company_name = '$company_name', 
                    contact_number = '$contact_number', 
                    address = '$address', 
                    business_registration = '$business_registration', 
                    tax_id = '$tax_id' 
                    WHERE user_id = $user_id";
    
    if (mysqli_query($con, $update_query)) {
        $success = "Profile updated successfully!";
        // Refresh supplier data
        $supplier_query = mysqli_query($con, "SELECT * FROM suppliers WHERE user_id = $user_id");
        $supplier = mysqli_fetch_assoc($supplier_query);
    } else {
        $error = "Error updating profile: " . mysqli_error($con);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Supplier Panel</title>
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

        /* Form Styles */
        .card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: var(--primary);
            outline: none;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary);
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
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
            .form-row {
                grid-template-columns: 1fr;
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
                <a href="supplier_products.php" class="menu-item">
                    <i class="fas fa-box"></i> <span>My Products</span>
                </a>
                <a href="supplier_orders.php" class="menu-item">
                    <i class="fas fa-shopping-cart"></i> <span>Orders</span>
                </a>
                <a href="supplier_profile.php" class="menu-item active">
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
                <h1>My Profile</h1>
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

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Company Information</h2>
                </div>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="company_name">Company Name *</label>
                        <input type="text" id="company_name" name="company_name" value="<?php echo htmlspecialchars($supplier['company_name']); ?>" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="contact_number">Contact Number</label>
                            <input type="text" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($supplier['contact_number']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="tax_id">Tax ID</label>
                            <input type="text" id="tax_id" name="tax_id" value="<?php echo htmlspecialchars($supplier['tax_id']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="business_registration">Business Registration Number</label>
                        <input type="text" id="business_registration" name="business_registration" value="<?php echo htmlspecialchars($supplier['business_registration']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Business Address</label>
                        <textarea id="address" name="address"><?php echo htmlspecialchars($supplier['address']); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </form>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Account Status</h2>
                </div>
                
                <div class="form-group">
                    <label>Supplier Status</label>
                    <div>
                        <span class="badge <?php 
                            switch($supplier['status']) {
                                case 'active': echo 'badge-success'; break;
                                case 'pending': echo 'badge-warning'; break;
                                case 'suspended': echo 'badge-danger'; break;
                                default: echo 'badge-info';
                            }
                        ?>">
                            <?php echo ucfirst($supplier['status']); ?>
                        </span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Registration Date</label>
                    <div>
                        <?php echo date('F j, Y', strtotime($supplier['created_at'])); ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Last Updated</label>
                    <div>
                        <?php echo date('F j, Y', strtotime($supplier['updated_at'])); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            let valid = true;
            const requiredFields = this.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    valid = false;
                    field.style.borderColor = 'red';
                } else {
                    field.style.borderColor = '#ddd';
                }
            });
            
            if (!valid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
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
?>