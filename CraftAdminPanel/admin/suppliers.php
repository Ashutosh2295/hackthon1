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
                $tribe = sanitize($_POST['tribe']);
                $specialty = sanitize($_POST['specialty']);
                $email = sanitize($_POST['email']);
                $phone = sanitize($_POST['phone']);
                $address = sanitize($_POST['address']);
                $bio = sanitize($_POST['bio']);
                
                if (empty($name) || empty($email)) {
                    setMessage('Name and email are required.', 'error');
                } else {
                    try {
                        $stmt = $conn->prepare("INSERT INTO suppliers (name, tribe, specialty, email, phone, address, bio) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$name, $tribe, $specialty, $email, $phone, $address, $bio]);
                        setMessage('Supplier added successfully!', 'success');
                    } catch (PDOException $e) {
                        setMessage('Error adding supplier: ' . $e->getMessage(), 'error');
                    }
                }
                break;
                
            case 'edit':
                $id = intval($_POST['id']);
                $name = sanitize($_POST['name']);
                $tribe = sanitize($_POST['tribe']);
                $specialty = sanitize($_POST['specialty']);
                $email = sanitize($_POST['email']);
                $phone = sanitize($_POST['phone']);
                $address = sanitize($_POST['address']);
                $bio = sanitize($_POST['bio']);
                $status = sanitize($_POST['status']);
                
                try {
                    $stmt = $conn->prepare("UPDATE suppliers SET name=?, tribe=?, specialty=?, email=?, phone=?, address=?, bio=?, status=? WHERE id=?");
                    $stmt->execute([$name, $tribe, $specialty, $email, $phone, $address, $bio, $status, $id]);
                    setMessage('Supplier updated successfully!', 'success');
                } catch (PDOException $e) {
                    setMessage('Error updating supplier: ' . $e->getMessage(), 'error');
                }
                break;
                
            case 'delete':
                $id = intval($_POST['id']);
                try {
                    $stmt = $conn->prepare("DELETE FROM suppliers WHERE id = ?");
                    $stmt->execute([$id]);
                    setMessage('Supplier deleted successfully!', 'success');
                } catch (PDOException $e) {
                    setMessage('Error deleting supplier: ' . $e->getMessage(), 'error');
                }
                break;
        }
    }
}

// Get suppliers with pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

try {
    // Count total suppliers
    $stmt = $conn->query("SELECT COUNT(*) as count FROM suppliers");
    $total_suppliers = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    $total_pages = ceil($total_suppliers / $limit);
    
    // Get suppliers for current page
    $stmt = $conn->prepare("SELECT * FROM suppliers ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$limit, $offset]);
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $suppliers = [];
    $total_pages = 0;
}

// Get single supplier for editing
$edit_supplier = null;
if (isset($_GET['edit'])) {
    try {
        $stmt = $conn->prepare("SELECT * FROM suppliers WHERE id = ?");
        $stmt->execute([intval($_GET['edit'])]);
        $edit_supplier = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Supplier not found
    }
}

$message = getMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppliers/Artisans - <?php echo SITE_NAME; ?></title>
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
                
                <!-- Add/Edit Supplier Form -->
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-<?php echo $edit_supplier ? 'edit' : 'plus'; ?>"></i> 
                           <?php echo $edit_supplier ? 'Edit' : 'Add New'; ?> Supplier/Artisan</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="<?php echo $edit_supplier ? 'edit' : 'add'; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <?php if ($edit_supplier): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_supplier['id']; ?>">
                            <?php endif; ?>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                                <div class="form-group">
                                    <label class="form-label" for="name">
                                        <i class="fas fa-user"></i> Name *
                                    </label>
                                    <input type="text" id="name" name="name" class="form-control" 
                                           value="<?php echo $edit_supplier['name'] ?? ''; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="tribe">
                                        <i class="fas fa-flag"></i> Tribe
                                    </label>
                                    <input type="text" id="tribe" name="tribe" class="form-control" 
                                           value="<?php echo $edit_supplier['tribe'] ?? ''; ?>" 
                                           placeholder="e.g., Cherokee, Navajo, Lakota">
                                </div>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                                <div class="form-group">
                                    <label class="form-label" for="email">
                                        <i class="fas fa-envelope"></i> Email *
                                    </label>
                                    <input type="email" id="email" name="email" class="form-control" 
                                           value="<?php echo $edit_supplier['email'] ?? ''; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="phone">
                                        <i class="fas fa-phone"></i> Phone
                                    </label>
                                    <input type="tel" id="phone" name="phone" class="form-control" 
                                           value="<?php echo $edit_supplier['phone'] ?? ''; ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="specialty">
                                    <i class="fas fa-palette"></i> Specialty
                                </label>
                                <input type="text" id="specialty" name="specialty" class="form-control" 
                                       value="<?php echo $edit_supplier['specialty'] ?? ''; ?>" 
                                       placeholder="e.g., Traditional pottery, Beadwork, Textile weaving">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="address">
                                    <i class="fas fa-map-marker-alt"></i> Address
                                </label>
                                <textarea id="address" name="address" class="form-control" rows="2" 
                                          placeholder="Full address"><?php echo $edit_supplier['address'] ?? ''; ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="bio">
                                    <i class="fas fa-info-circle"></i> Bio
                                </label>
                                <textarea id="bio" name="bio" class="form-control" rows="3" 
                                          placeholder="Brief biography and background"><?php echo $edit_supplier['bio'] ?? ''; ?></textarea>
                            </div>
                            
                            <?php if ($edit_supplier): ?>
                                <div class="form-group">
                                    <label class="form-label" for="status">
                                        <i class="fas fa-toggle-on"></i> Status
                                    </label>
                                    <select id="status" name="status" class="form-control">
                                        <option value="active" <?php echo ($edit_supplier['status'] ?? 'active') == 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo ($edit_supplier['status'] ?? '') == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                            <?php endif; ?>
                            
                            <div class="action-buttons">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> <?php echo $edit_supplier ? 'Update' : 'Add'; ?> Supplier
                                </button>
                                <?php if ($edit_supplier): ?>
                                    <a href="suppliers.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Suppliers List -->
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-list"></i> All Suppliers/Artisans (<?php echo $total_suppliers; ?>)</h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($suppliers)): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle"></i>
                                No suppliers found. <a href="init_db.php">Initialize the database</a> to add sample data, or add your first supplier above.
                            </div>
                        <?php else: ?>
                            <div class="table-container">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Tribe</th>
                                            <th>Specialty</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($suppliers as $supplier): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($supplier['name']); ?></strong>
                                                    <?php if ($supplier['bio']): ?>
                                                        <br><small style="color: var(--gray-medium);"><?php echo htmlspecialchars(substr($supplier['bio'], 0, 60)) . '...'; ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($supplier['tribe'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($supplier['specialty'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($supplier['email']); ?></td>
                                                <td><?php echo htmlspecialchars($supplier['phone'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo ($supplier['status'] ?? 'active') == 'active' ? 'success' : 'warning'; ?>">
                                                        <?php echo ucfirst($supplier['status'] ?? 'active'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="?edit=<?php echo $supplier['id']; ?>" class="btn btn-sm btn-secondary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form method="POST" style="display: inline;" 
                                                              onsubmit="return confirm('Are you sure you want to delete this supplier?');">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                            <input type="hidden" name="id" value="<?php echo $supplier['id']; ?>">
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

<style>
.badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-success {
    background: var(--success-color);
    color: white;
}

.badge-warning {
    background: var(--warning-color);
    color: var(--text-dark);
}
</style>