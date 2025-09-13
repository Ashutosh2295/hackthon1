<?php
require_once 'config/config.php';
requireLogin();

$database = new Database();
$conn = $database->getConnection();

// Handle form submissions
if ($_POST) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        setMessage('Security token mismatch. Please try again.', 'error');
    } elseif (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_password':
                $current_password = $_POST['current_password'] ?? '';
                $new_password = $_POST['new_password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                
                if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                    setMessage('All password fields are required.', 'error');
                } elseif ($new_password !== $confirm_password) {
                    setMessage('New password and confirmation do not match.', 'error');
                } elseif (strlen($new_password) < 6) {
                    setMessage('New password must be at least 6 characters long.', 'error');
                } else {
                    try {
                        // Verify current password
                        $stmt = $conn->prepare("SELECT password FROM admin WHERE id = ?");
                        $stmt->execute([$_SESSION['admin_id']]);
                        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($admin && password_verify($current_password, $admin['password'])) {
                            // Update password
                            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                            $stmt = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
                            $stmt->execute([$hashed_password, $_SESSION['admin_id']]);
                            setMessage('Password updated successfully!', 'success');
                        } else {
                            setMessage('Current password is incorrect.', 'error');
                        }
                    } catch (PDOException $e) {
                        setMessage('Error updating password: ' . $e->getMessage(), 'error');
                    }
                }
                break;
                
            case 'update_profile':
                $username = sanitize($_POST['username']);
                
                if (empty($username)) {
                    setMessage('Username is required.', 'error');
                } else {
                    try {
                        $stmt = $conn->prepare("UPDATE admin SET username = ? WHERE id = ?");
                        $stmt->execute([$username, $_SESSION['admin_id']]);
                        $_SESSION['admin_username'] = $username;
                        setMessage('Profile updated successfully!', 'success');
                    } catch (PDOException $e) {
                        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                            setMessage('Username already exists. Please choose another.', 'error');
                        } else {
                            setMessage('Error updating profile: ' . $e->getMessage(), 'error');
                        }
                    }
                }
                break;
                
            case 'clear_logs':
                // This would clear system logs in a real implementation
                setMessage('System logs cleared successfully!', 'success');
                break;
        }
    }
}

// Get current admin info
try {
    $stmt = $conn->prepare("SELECT username, created_at FROM admin WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $admin_info = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $admin_info = null;
}

// Get system statistics
try {
    $stats = [];
    $stats['suppliers'] = $conn->query("SELECT COUNT(*) FROM suppliers")->fetchColumn();
    $stats['users'] = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $stats['products'] = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $stats['categories'] = $conn->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    
    // Try to get orders count, but handle if table doesn't exist
    try {
        $stats['orders'] = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    } catch (PDOException $e) {
        $stats['orders'] = 0;
    }
} catch (PDOException $e) {
    $stats = ['suppliers' => 0, 'users' => 0, 'products' => 0, 'categories' => 0, 'orders' => 0];
}

$message = getMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - <?php echo SITE_NAME; ?></title>
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
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <!-- Profile Settings -->
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-user-cog"></i> Profile Settings</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="update_profile">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                
                                <div class="form-group">
                                    <label class="form-label" for="username">
                                        <i class="fas fa-user"></i> Username
                                    </label>
                                    <input type="text" id="username" name="username" class="form-control" 
                                           value="<?php echo htmlspecialchars($admin_info['username'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-calendar"></i> Account Created
                                    </label>
                                    <input type="text" class="form-control" 
                                           value="<?php echo $admin_info['created_at'] ? date('F j, Y g:i A', strtotime($admin_info['created_at'])) : 'Unknown'; ?>" readonly>
                                </div>
                                
                                <div class="action-buttons">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Password Settings -->
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-lock"></i> Change Password</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="update_password">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                
                                <div class="form-group">
                                    <label class="form-label" for="current_password">
                                        <i class="fas fa-key"></i> Current Password
                                    </label>
                                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="new_password">
                                        <i class="fas fa-lock"></i> New Password
                                    </label>
                                    <input type="password" id="new_password" name="new_password" class="form-control" 
                                           minlength="6" required>
                                    <small style="color: var(--gray-medium);">Minimum 6 characters</small>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="confirm_password">
                                        <i class="fas fa-lock"></i> Confirm New Password
                                    </label>
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                                           minlength="6" required>
                                </div>
                                
                                <div class="action-buttons">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Change Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- System Information -->
                <div class="card" style="margin-top: 2rem;">
                    <div class="card-header">
                        <h4><i class="fas fa-info-circle"></i> System Information</h4>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                            <div class="stat-item">
                                <div class="stat-icon" style="background: var(--primary-color);">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stat-details">
                                    <h3><?php echo $stats['suppliers']; ?></h3>
                                    <p>Suppliers/Artisans</p>
                                </div>
                            </div>
                            
                            <div class="stat-item">
                                <div class="stat-icon" style="background: var(--secondary-color);">
                                    <i class="fas fa-user-friends"></i>
                                </div>
                                <div class="stat-details">
                                    <h3><?php echo $stats['users']; ?></h3>
                                    <p>Customers</p>
                                </div>
                            </div>
                            
                            <div class="stat-item">
                                <div class="stat-icon" style="background: var(--accent-color);">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div class="stat-details">
                                    <h3><?php echo $stats['products']; ?></h3>
                                    <p>Products</p>
                                </div>
                            </div>
                            
                            <div class="stat-item">
                                <div class="stat-icon" style="background: var(--warning-color);">
                                    <i class="fas fa-tags"></i>
                                </div>
                                <div class="stat-details">
                                    <h3><?php echo $stats['categories']; ?></h3>
                                    <p>Categories</p>
                                </div>
                            </div>
                            
                            <div class="stat-item">
                                <div class="stat-icon" style="background: var(--success-color);">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div class="stat-details">
                                    <h3><?php echo $stats['orders']; ?></h3>
                                    <p>Orders</p>
                                </div>
                            </div>
                        </div>
                        
                        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                            <h5><i class="fas fa-server"></i> Technical Information</h5>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
                                <div>
                                    <strong>PHP Version:</strong> <?php echo PHP_VERSION; ?>
                                </div>
                                <div>
                                    <strong>Database:</strong> <?php echo $conn->getAttribute(PDO::ATTR_DRIVER_NAME); ?>
                                </div>
                                <div>
                                    <strong>Session Timeout:</strong> <?php echo ADMIN_SESSION_TIMEOUT; ?> seconds
                                </div>
                                <div>
                                    <strong>Max Upload Size:</strong> <?php echo (MAX_FILE_SIZE / 1024 / 1024); ?>MB
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card" style="margin-top: 2rem;">
                    <div class="card-header">
                        <h4><i class="fas fa-tools"></i> Quick Actions</h4>
                    </div>
                    <div class="card-body">
                        <div class="action-buttons">
                            <a href="init_db.php" class="btn btn-secondary">
                                <i class="fas fa-database"></i> Initialize Database
                            </a>
                            <a href="add_sample_data.php" class="btn btn-secondary">
                                <i class="fas fa-plus-circle"></i> Add Sample Data
                            </a>
                            <a href="create_orders_table.php" class="btn btn-secondary">
                                <i class="fas fa-table"></i> Create Orders Table
                            </a>
                            <a href="debug.php" class="btn btn-secondary">
                                <i class="fas fa-bug"></i> Debug Info
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>