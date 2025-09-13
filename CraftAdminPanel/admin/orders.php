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
                $user_id = intval($_POST['user_id']) ?: null;
                $total_amount = floatval($_POST['total_amount']);
                $status = sanitize($_POST['status']);
                $notes = sanitize($_POST['notes']);
                
                if ($total_amount <= 0) {
                    setMessage('Total amount must be greater than 0.', 'error');
                } else {
                    try {
                        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, notes) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$user_id, $total_amount, $status, $notes]);
                        setMessage('Order added successfully!', 'success');
                    } catch (PDOException $e) {
                        setMessage('Error adding order: ' . $e->getMessage(), 'error');
                    }
                }
                break;
                
            case 'edit':
                $id = intval($_POST['id']);
                $user_id = intval($_POST['user_id']) ?: null;
                $total_amount = floatval($_POST['total_amount']);
                $status = sanitize($_POST['status']);
                $notes = sanitize($_POST['notes']);
                
                try {
                    $stmt = $conn->prepare("UPDATE orders SET user_id=?, total_amount=?, status=?, notes=?, updated_at=CURRENT_TIMESTAMP WHERE id=?");
                    $stmt->execute([$user_id, $total_amount, $status, $notes, $id]);
                    setMessage('Order updated successfully!', 'success');
                } catch (PDOException $e) {
                    setMessage('Error updating order: ' . $e->getMessage(), 'error');
                }
                break;
                
            case 'delete':
                $id = intval($_POST['id']);
                try {
                    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
                    $stmt->execute([$id]);
                    setMessage('Order deleted successfully!', 'success');
                } catch (PDOException $e) {
                    setMessage('Error deleting order: ' . $e->getMessage(), 'error');
                }
                break;
        }
    }
}

// Get orders with pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

try {
    // Count total orders
    $stmt = $conn->query("SELECT COUNT(*) as count FROM orders");
    $total_orders = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    $total_pages = ceil($total_orders / $limit);
    
    // Get orders for current page with user information
    $stmt = $conn->prepare("
        SELECT o.*, u.name as customer_name, u.email as customer_email 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$limit, $offset]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $orders = [];
    $total_pages = 0;
}

// Get users for dropdown
try {
    $stmt = $conn->query("SELECT id, name, email FROM users ORDER BY name");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users = [];
}

// Get single order for editing
$edit_order = null;
if (isset($_GET['edit'])) {
    try {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([intval($_GET['edit'])]);
        $edit_order = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Order not found
    }
}

$message = getMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - <?php echo SITE_NAME; ?></title>
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
                
                <!-- Add/Edit Order Form -->
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-<?php echo $edit_order ? 'edit' : 'plus'; ?>"></i> 
                           <?php echo $edit_order ? 'Edit' : 'Add New'; ?> Order</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="<?php echo $edit_order ? 'edit' : 'add'; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <?php if ($edit_order): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_order['id']; ?>">
                            <?php endif; ?>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                                <div class="form-group">
                                    <label class="form-label" for="user_id">
                                        <i class="fas fa-user"></i> Customer
                                    </label>
                                    <select id="user_id" name="user_id" class="form-control">
                                        <option value="">Select Customer (Optional)</option>
                                        <?php foreach ($users as $user): ?>
                                            <option value="<?php echo $user['id']; ?>" 
                                                    <?php echo ($edit_order && $edit_order['user_id'] == $user['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($user['name'] . ' (' . $user['email'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="total_amount">
                                        <i class="fas fa-dollar-sign"></i> Total Amount *
                                    </label>
                                    <input type="number" id="total_amount" name="total_amount" class="form-control" 
                                           value="<?php echo $edit_order['total_amount'] ?? ''; ?>" 
                                           step="0.01" min="0.01" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="status">
                                    <i class="fas fa-flag"></i> Status
                                </label>
                                <select id="status" name="status" class="form-control">
                                    <option value="pending" <?php echo ($edit_order && $edit_order['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="processing" <?php echo ($edit_order && $edit_order['status'] == 'processing') ? 'selected' : ''; ?>>Processing</option>
                                    <option value="shipped" <?php echo ($edit_order && $edit_order['status'] == 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="delivered" <?php echo ($edit_order && $edit_order['status'] == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="cancelled" <?php echo ($edit_order && $edit_order['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="notes">
                                    <i class="fas fa-sticky-note"></i> Notes
                                </label>
                                <textarea id="notes" name="notes" class="form-control" rows="3" 
                                          placeholder="Order notes, special instructions, etc."><?php echo $edit_order['notes'] ?? ''; ?></textarea>
                            </div>
                            
                            <div class="action-buttons">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> <?php echo $edit_order ? 'Update' : 'Add'; ?> Order
                                </button>
                                <?php if ($edit_order): ?>
                                    <a href="orders.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Orders List -->
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-shopping-cart"></i> All Orders (<?php echo $total_orders; ?>)</h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($orders)): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle"></i>
                                No orders found. Add your first order above.
                            </div>
                        <?php else: ?>
                            <div class="table-container">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Customer</th>
                                            <th>Total Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                            <tr>
                                                <td>
                                                    <strong>#<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></strong>
                                                </td>
                                                <td>
                                                    <?php if ($order['customer_name']): ?>
                                                        <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong><br>
                                                        <small><?php echo htmlspecialchars($order['customer_email']); ?></small>
                                                    <?php else: ?>
                                                        <em>Guest Order</em>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?php echo formatCurrency($order['total_amount']); ?></strong>
                                                </td>
                                                <td>
                                                    <?php
                                                    $status_colors = [
                                                        'pending' => 'orange',
                                                        'processing' => 'blue',
                                                        'shipped' => 'purple',
                                                        'delivered' => 'green',
                                                        'cancelled' => 'red'
                                                    ];
                                                    $color = $status_colors[$order['status']] ?? 'gray';
                                                    ?>
                                                    <span class="badge" style="background: var(--<?php echo $color; ?>);">
                                                        <?php echo ucfirst($order['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong><?php echo date('M j, Y', strtotime($order['created_at'])); ?></strong><br>
                                                    <small><?php echo date('g:i A', strtotime($order['created_at'])); ?></small>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="orders.php?edit=<?php echo $order['id']; ?>" 
                                                           class="btn btn-sm btn-primary" title="Edit Order">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form method="POST" style="display: inline;" 
                                                              onsubmit="return confirm('Are you sure you want to delete this order?');">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                            <input type="hidden" name="id" value="<?php echo $order['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete Order">
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
                                    <?php if ($page > 1): ?>
                                        <a href="orders.php?page=<?php echo $page - 1; ?>" class="btn btn-secondary">
                                            <i class="fas fa-chevron-left"></i> Previous
                                        </a>
                                    <?php endif; ?>
                                    
                                    <span class="page-info">
                                        Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                                    </span>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <a href="orders.php?page=<?php echo $page + 1; ?>" class="btn btn-secondary">
                                            Next <i class="fas fa-chevron-right"></i>
                                        </a>
                                    <?php endif; ?>
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