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
            case 'add':
                $name = sanitize($_POST['name']);
                $email = sanitize($_POST['email']);
                $phone = sanitize($_POST['phone']);
                $address = sanitize($_POST['address']);
                $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
                
                if (empty($name) || empty($email)) {
                    setMessage('Name and email are required.', 'error');
                } else {
                    try {
                        $stmt = $conn->prepare("INSERT INTO users (name, email, phone, address, password) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$name, $email, $phone, $address, $password]);
                        setMessage('User added successfully!', 'success');
                    } catch (PDOException $e) {
                        setMessage('Error adding user: ' . $e->getMessage(), 'error');
                    }
                }
                break;
                
            case 'edit':
                $id = intval($_POST['id']);
                $name = sanitize($_POST['name']);
                $email = sanitize($_POST['email']);
                $phone = sanitize($_POST['phone']);
                $address = sanitize($_POST['address']);
                $status = sanitize($_POST['status']);
                
                try {
                    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=?, address=?, status=? WHERE id=?");
                    $stmt->execute([$name, $email, $phone, $address, $status, $id]);
                    setMessage('User updated successfully!', 'success');
                } catch (PDOException $e) {
                    setMessage('Error updating user: ' . $e->getMessage(), 'error');
                }
                break;
                
            case 'delete':
                $id = intval($_POST['id']);
                try {
                    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$id]);
                    setMessage('User deleted successfully!', 'success');
                } catch (PDOException $e) {
                    setMessage('Error deleting user: ' . $e->getMessage(), 'error');
                }
                break;
        }
    }
}

// Get users with pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

try {
    // Count total users
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    $total_pages = ceil($total_users / $limit);
    
    // Get users for current page
    $stmt = $conn->prepare("SELECT * FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$limit, $offset]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users = [];
    $total_pages = 0;
}

// Get single user for editing
$edit_user = null;
if (isset($_GET['edit'])) {
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([intval($_GET['edit'])]);
        $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // User not found
    }
}

$message = getMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - <?php echo SITE_NAME; ?></title>
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
                
                <!-- Add/Edit User Form -->
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-<?php echo $edit_user ? 'edit' : 'user-plus'; ?>"></i> 
                           <?php echo $edit_user ? 'Edit' : 'Add New'; ?> Customer</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="<?php echo $edit_user ? 'edit' : 'add'; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <?php if ($edit_user): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_user['id']; ?>">
                            <?php endif; ?>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                                <div class="form-group">
                                    <label class="form-label" for="name">
                                        <i class="fas fa-user"></i> Full Name *
                                    </label>
                                    <input type="text" id="name" name="name" class="form-control" 
                                           value="<?php echo $edit_user['name'] ?? ''; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="email">
                                        <i class="fas fa-envelope"></i> Email *
                                    </label>
                                    <input type="email" id="email" name="email" class="form-control" 
                                           value="<?php echo $edit_user['email'] ?? ''; ?>" required>
                                </div>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                                <div class="form-group">
                                    <label class="form-label" for="phone">
                                        <i class="fas fa-phone"></i> Phone
                                    </label>
                                    <input type="tel" id="phone" name="phone" class="form-control" 
                                           value="<?php echo $edit_user['phone'] ?? ''; ?>">
                                </div>
                                
                                <?php if ($edit_user): ?>
                                    <div class="form-group">
                                        <label class="form-label" for="status">
                                            <i class="fas fa-toggle-on"></i> Status
                                        </label>
                                        <select id="status" name="status" class="form-control">
                                            <option value="active" <?php echo ($edit_user['status'] ?? 'active') == 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo ($edit_user['status'] ?? '') == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                <?php else: ?>
                                    <div class="form-group">
                                        <label class="form-label" for="password">
                                            <i class="fas fa-lock"></i> Password
                                        </label>
                                        <input type="password" id="password" name="password" class="form-control" 
                                               placeholder="Leave empty for no password">
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="address">
                                    <i class="fas fa-map-marker-alt"></i> Address
                                </label>
                                <textarea id="address" name="address" class="form-control" rows="3" 
                                          placeholder="Full shipping address"><?php echo $edit_user['address'] ?? ''; ?></textarea>
                            </div>
                            
                            <div class="action-buttons">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> <?php echo $edit_user ? 'Update' : 'Add'; ?> Customer
                                </button>
                                <?php if ($edit_user): ?>
                                    <a href="users.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Users List -->
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-list"></i> All Customers (<?php echo $total_users; ?>)</h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($users)): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle"></i>
                                No customers found. Add your first customer above or users will be created when they register on your website.
                            </div>
                        <?php else: ?>
                            <div class="table-container">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Status</th>
                                            <th>Joined</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                                    <?php if ($user['address']): ?>
                                                        <br><small style="color: var(--gray-medium);"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars(substr($user['address'], 0, 40)) . '...'; ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo ($user['status'] ?? 'active') == 'active' ? 'success' : 'warning'; ?>">
                                                        <?php echo ucfirst($user['status'] ?? 'active'); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo timeAgo($user['created_at']); ?></td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="?edit=<?php echo $user['id']; ?>" class="btn btn-sm btn-secondary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form method="POST" style="display: inline;" 
                                                              onsubmit="return confirm('Are you sure you want to delete this customer?');">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <div class="pagination">
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <a href="?page=<?php echo $i; ?>" 
                                           class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>