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
                $description = sanitize($_POST['description']);
                
                if (empty($name)) {
                    setMessage('Category name is required.', 'error');
                } else {
                    try {
                        $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
                        $stmt->execute([$name, $description]);
                        setMessage('Category added successfully!', 'success');
                    } catch (PDOException $e) {
                        setMessage('Error adding category: ' . $e->getMessage(), 'error');
                    }
                }
                break;
                
            case 'edit':
                $id = intval($_POST['id']);
                $name = sanitize($_POST['name']);
                $description = sanitize($_POST['description']);
                
                try {
                    $stmt = $conn->prepare("UPDATE categories SET name=?, description=? WHERE id=?");
                    $stmt->execute([$name, $description, $id]);
                    setMessage('Category updated successfully!', 'success');
                } catch (PDOException $e) {
                    setMessage('Error updating category: ' . $e->getMessage(), 'error');
                }
                break;
                
            case 'delete':
                $id = intval($_POST['id']);
                try {
                    // Check if category has products
                    $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
                    $check_stmt->execute([$id]);
                    $product_count = $check_stmt->fetch(PDO::FETCH_ASSOC)['count'];
                    
                    if ($product_count > 0) {
                        setMessage("Cannot delete category. It has {$product_count} products associated with it.", 'error');
                    } else {
                        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
                        $stmt->execute([$id]);
                        setMessage('Category deleted successfully!', 'success');
                    }
                } catch (PDOException $e) {
                    setMessage('Error deleting category: ' . $e->getMessage(), 'error');
                }
                break;
        }
    }
}

// Get categories with product count
try {
    $stmt = $conn->query("
        SELECT c.*, COUNT(p.id) as product_count 
        FROM categories c 
        LEFT JOIN products p ON c.id = p.category_id 
        GROUP BY c.id, c.name, c.description, c.created_at 
        ORDER BY c.created_at DESC
    ");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}

// Get single category for editing
$edit_category = null;
if (isset($_GET['edit'])) {
    try {
        $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([intval($_GET['edit'])]);
        $edit_category = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Category not found
    }
}

$message = getMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - <?php echo SITE_NAME; ?></title>
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
                
                <!-- Add/Edit Category Form -->
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-<?php echo $edit_category ? 'edit' : 'plus'; ?>"></i> 
                           <?php echo $edit_category ? 'Edit' : 'Add New'; ?> Category</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="<?php echo $edit_category ? 'edit' : 'add'; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <?php if ($edit_category): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_category['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label class="form-label" for="name">
                                    <i class="fas fa-tag"></i> Category Name *
                                </label>
                                <input type="text" id="name" name="name" class="form-control" 
                                       value="<?php echo $edit_category['name'] ?? ''; ?>" required
                                       placeholder="e.g., Pottery & Ceramics, Textiles & Weavings">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="description">
                                    <i class="fas fa-align-left"></i> Description
                                </label>
                                <textarea id="description" name="description" class="form-control" rows="3" 
                                          placeholder="Brief description of this category"><?php echo $edit_category['description'] ?? ''; ?></textarea>
                            </div>
                            
                            <div class="action-buttons">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> <?php echo $edit_category ? 'Update' : 'Add'; ?> Category
                                </button>
                                <?php if ($edit_category): ?>
                                    <a href="categories.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Categories List -->
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-list"></i> All Categories (<?php echo count($categories); ?>)</h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($categories)): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle"></i>
                                No categories found. <a href="init_db.php">Initialize the database</a> to add sample categories, or add your first category above.
                            </div>
                        <?php else: ?>
                            <div class="table-container">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th>Description</th>
                                            <th>Products</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($categories as $category): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($category['name']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($category['description'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $category['product_count'] > 0 ? 'success' : 'secondary'; ?>">
                                                        <?php echo $category['product_count']; ?> products
                                                    </span>
                                                </td>
                                                <td><?php echo timeAgo($category['created_at']); ?></td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="?edit=<?php echo $category['id']; ?>" class="btn btn-sm btn-secondary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php if ($category['product_count'] == 0): ?>
                                                            <form method="POST" style="display: inline;" 
                                                                  onsubmit="return confirm('Are you sure you want to delete this category?');">
                                                                <input type="hidden" name="action" value="delete">
                                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                                <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                                                <button type="submit" class="btn btn-sm btn-danger">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        <?php else: ?>
                                                            <button class="btn btn-sm btn-danger" disabled 
                                                                    title="Cannot delete - category has <?php echo $category['product_count']; ?> products">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<style>
.badge-secondary {
    background: var(--gray-medium);
    color: white;
}
</style>