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
                $price = floatval($_POST['price']);
                $category_id = intval($_POST['category_id']) ?: null;
                $supplier_id = intval($_POST['supplier_id']) ?: null;
                $stock_quantity = intval($_POST['stock_quantity']);
                $image = null;
                
                // Handle image upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $upload_result = uploadFile($_FILES['image'], 'products');
                    if ($upload_result['success']) {
                        $image = $upload_result['filename'];
                    } else {
                        setMessage($upload_result['message'], 'error');
                        break;
                    }
                }
                
                if (empty($name) || $price <= 0) {
                    setMessage('Product name and valid price are required.', 'error');
                } else {
                    try {
                        $stmt = $conn->prepare("INSERT INTO products (name, description, price, category_id, supplier_id, stock_quantity, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$name, $description, $price, $category_id, $supplier_id, $stock_quantity, $image]);
                        setMessage('Product added successfully!', 'success');
                    } catch (PDOException $e) {
                        setMessage('Error adding product: ' . $e->getMessage(), 'error');
                    }
                }
                break;
                
            case 'edit':
                $id = intval($_POST['id']);
                $name = sanitize($_POST['name']);
                $description = sanitize($_POST['description']);
                $price = floatval($_POST['price']);
                $category_id = intval($_POST['category_id']) ?: null;
                $supplier_id = intval($_POST['supplier_id']) ?: null;
                $stock_quantity = intval($_POST['stock_quantity']);
                $status = sanitize($_POST['status']);
                
                try {
                    $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, category_id=?, supplier_id=?, stock_quantity=?, status=? WHERE id=?");
                    $stmt->execute([$name, $description, $price, $category_id, $supplier_id, $stock_quantity, $status, $id]);
                    setMessage('Product updated successfully!', 'success');
                } catch (PDOException $e) {
                    setMessage('Error updating product: ' . $e->getMessage(), 'error');
                }
                break;
                
            case 'delete':
                $id = intval($_POST['id']);
                try {
                    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
                    $stmt->execute([$id]);
                    setMessage('Product deleted successfully!', 'success');
                } catch (PDOException $e) {
                    setMessage('Error deleting product: ' . $e->getMessage(), 'error');
                }
                break;
        }
    }
}

// Get categories and suppliers for dropdowns
try {
    $categories_stmt = $conn->query("SELECT id, name FROM categories ORDER BY name");
    $categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $suppliers_stmt = $conn->query("SELECT id, name FROM suppliers WHERE status = 'active' ORDER BY name");
    $suppliers = $suppliers_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
    $suppliers = [];
}

// Get products with pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

try {
    // Count total products
    $stmt = $conn->query("SELECT COUNT(*) as count FROM products");
    $total_products = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    $total_pages = ceil($total_products / $limit);
    
    // Get products with category and supplier info
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name, s.name as supplier_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN suppliers s ON p.supplier_id = s.id 
        ORDER BY p.created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$limit, $offset]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $products = [];
    $total_pages = 0;
}

// Get single product for editing
$edit_product = null;
if (isset($_GET['edit'])) {
    try {
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([intval($_GET['edit'])]);
        $edit_product = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Product not found
    }
}

$message = getMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - <?php echo SITE_NAME; ?></title>
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
                
                <!-- Add/Edit Product Form -->
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-<?php echo $edit_product ? 'edit' : 'plus'; ?>"></i> 
                           <?php echo $edit_product ? 'Edit' : 'Add New'; ?> Product</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="<?php echo $edit_product ? 'edit' : 'add'; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <?php if ($edit_product): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_product['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label class="form-label" for="name">
                                    <i class="fas fa-tag"></i> Product Name *
                                </label>
                                <input type="text" id="name" name="name" class="form-control" 
                                       value="<?php echo $edit_product['name'] ?? ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="description">
                                    <i class="fas fa-align-left"></i> Description
                                </label>
                                <textarea id="description" name="description" class="form-control" rows="3" 
                                          placeholder="Product description and details"><?php echo $edit_product['description'] ?? ''; ?></textarea>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                                <div class="form-group">
                                    <label class="form-label" for="price">
                                        <i class="fas fa-dollar-sign"></i> Price *
                                    </label>
                                    <input type="number" id="price" name="price" class="form-control" step="0.01" min="0"
                                           value="<?php echo $edit_product['price'] ?? ''; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="stock_quantity">
                                        <i class="fas fa-boxes"></i> Stock Quantity
                                    </label>
                                    <input type="number" id="stock_quantity" name="stock_quantity" class="form-control" min="0"
                                           value="<?php echo $edit_product['stock_quantity'] ?? '0'; ?>">
                                </div>
                                
                                <?php if ($edit_product): ?>
                                    <div class="form-group">
                                        <label class="form-label" for="status">
                                            <i class="fas fa-toggle-on"></i> Status
                                        </label>
                                        <select id="status" name="status" class="form-control">
                                            <option value="active" <?php echo ($edit_product['status'] ?? 'active') == 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo ($edit_product['status'] ?? '') == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                                <div class="form-group">
                                    <label class="form-label" for="category_id">
                                        <i class="fas fa-tags"></i> Category
                                    </label>
                                    <select id="category_id" name="category_id" class="form-control">
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" 
                                                    <?php echo ($edit_product['category_id'] ?? '') == $category['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="supplier_id">
                                        <i class="fas fa-user"></i> Supplier/Artisan
                                    </label>
                                    <select id="supplier_id" name="supplier_id" class="form-control">
                                        <option value="">Select Supplier</option>
                                        <?php foreach ($suppliers as $supplier): ?>
                                            <option value="<?php echo $supplier['id']; ?>" 
                                                    <?php echo ($edit_product['supplier_id'] ?? '') == $supplier['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($supplier['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="image">
                                    <i class="fas fa-image"></i> Product Image
                                </label>
                                <input type="file" id="image" name="image" class="form-control" accept="image/*">
                                <small style="color: var(--gray-medium);">Max size: 5MB. Allowed: JPG, PNG, GIF, WebP</small>
                                <?php if ($edit_product && $edit_product['image']): ?>
                                    <div style="margin-top: 10px;">
                                        <img src="uploads/products/<?php echo htmlspecialchars($edit_product['image']); ?>" 
                                             alt="Current image" class="image-preview">
                                        <p><small>Current image</small></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="action-buttons">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> <?php echo $edit_product ? 'Update' : 'Add'; ?> Product
                                </button>
                                <?php if ($edit_product): ?>
                                    <a href="products.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Products List -->
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-list"></i> All Products (<?php echo $total_products; ?>)</h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($products)): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle"></i>
                                No products found. <a href="init_db.php">Initialize the database</a> to create categories and suppliers first, then add your first product above.
                            </div>
                        <?php else: ?>
                            <div class="table-container">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Category</th>
                                            <th>Supplier</th>
                                            <th>Stock</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($products as $product): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                                    <?php if ($product['description']): ?>
                                                        <br><small style="color: var(--gray-medium);"><?php echo htmlspecialchars(substr($product['description'], 0, 60)) . '...'; ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><strong><?php echo formatCurrency($product['price']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($product['supplier_name'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $product['stock_quantity'] > 0 ? 'success' : 'warning'; ?>">
                                                        <?php echo $product['stock_quantity']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?php echo ($product['status'] ?? 'active') == 'active' ? 'success' : 'warning'; ?>">
                                                        <?php echo ucfirst($product['status'] ?? 'active'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="?edit=<?php echo $product['id']; ?>" class="btn btn-sm btn-secondary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form method="POST" style="display: inline;" 
                                                              onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
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