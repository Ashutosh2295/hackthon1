<?php
// get_product_details.php
session_start();
$con = mysqli_connect("localhost", "root", "", "tribal_arts_db") or die("Couldn't connect");

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid product ID");
}

$productId = intval($_GET['id']);

// Fetch product details from database
$query = "SELECT * FROM products WHERE id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Product not found");
}

$product = $result->fetch_assoc();

// Handle Add to Cart from product details page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = (int) $_POST['quantity'];
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = [
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $quantity,
            'image' => $product['image']
        ];
    }
    
    $_SESSION['success_message'] = "Product added to cart!";
    header("Location: get_product_details.php?id=" . $productId);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Artisan Ark</title>
    <link href="https://fonts.googleapis.com/css2?family=Georgia&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #8B4513;
            --secondary-color: #D2691E;
            --accent-color: #CD853F;
            --text-dark: #333;
            --text-light: #777;
            --white: #fff;
            --light-bg: #f9f5f0;
            --transition: all 0.3s ease;
            --border-radius: 4px;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--light-bg);
            color: var(--text-dark);
            line-height: 1.6;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        .navbar {
            padding: 0.8rem 0;
            background-color: var(--white);
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
            width: 100%;
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            width: 100%;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            flex-shrink: 0;
        }

        .logo img {
            height: 60px;
            width: auto;
            border-radius: 10%;
            object-fit: cover;
        }

        .logo-text {
            display: flex;
            flex-direction: column;
        }

        .logo h1 {
            color: var(--primary-color);
            font-size: 1.8rem;
            margin-bottom: 0.2rem;
            line-height: 1;
        }

        .logo p {
            color: var(--secondary-color);
            font-size: 0.7rem;
            margin: 0;
            font-style: italic;
            white-space: nowrap;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .cart-icon {
            position: relative;
            cursor: pointer;
            font-size: 1.5rem;
            color: var(--primary-color);
            transition: var(--transition);
        }

        .cart-icon:hover {
            color: var(--secondary-color);
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--accent-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .back-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            text-decoration: none;
            display: inline-block;
        }

        .back-btn:hover {
            background-color: var(--secondary-color);
        }

        /* Product Detail Styles */
        .product-detail-container {
            padding: 40px 0;
        }

        .product-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            background: var(--white);
            border-radius: 10px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .product-image {
            padding: 20px;
            text-align: center;
        }

        .product-image img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }

        .product-info {
            padding: 30px;
        }

        .product-title {
            font-size: 2rem;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .product-price {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .product-description {
            margin-bottom: 25px;
            line-height: 1.7;
        }

        .product-specs {
            margin-bottom: 25px;
        }

        .product-specs h3 {
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .specs-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .spec-item {
            display: flex;
            flex-direction: column;
        }

        .spec-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .spec-value {
            color: var(--text-light);
        }

        .product-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-selector label {
            font-weight: 600;
        }

        .quantity-selector input {
            width: 60px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .btn-outline:hover {
            background: var(--primary-color);
            color: white;
        }

        /* Success message */
        .success-message {
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            animation: fadeOut 5s forwards;
        }

        @keyframes fadeOut {
            0% { opacity: 1; }
            70% { opacity: 1; }
            100% { opacity: 0; display: none; }
        }

        /* Responsive Design */
        @media (max-width: 968px) {
            .product-detail {
                grid-template-columns: 1fr;
            }
            
            .product-image, .product-info {
                padding: 20px;
            }
        }

        @media (max-width: 480px) {
            .product-title {
                font-size: 1.5rem;
            }
            
            .product-price {
                font-size: 1.5rem;
            }
            
            .specs-grid {
                grid-template-columns: 1fr;
            }
            
            .product-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <img src="/IMG-20250914-WA0002.jpg" alt="Artisan Ark Logo">
                <div class="logo-text">
                    <h1>Artisan Ark</h1>
                    <p>Preserving Culture • Supporting Artists</p>
                </div>
            </a>

            <div class="nav-actions">
                <a href="index.php#products" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
                <div class="cart-icon" onclick="window.location.href='index.php#cart'">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count">
                        <?php 
                        $cartCount = 0;
                        if (isset($_SESSION['cart'])) {
                            $cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));
                        }
                        echo $cartCount > 0 ? $cartCount : '';
                        ?>
                    </span>
                </div>
            </div>
        </div>
    </header>

    <!-- Success message -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="container">
            <div class="success-message" id="successMessage">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Product Details -->
    <div class="container product-detail-container">
        <div class="product-detail">
            <div class="product-image">
                <img src="<?php echo !empty($product['image']) ? htmlspecialchars($product['image']) : 'https://via.placeholder.com/400x300?text=Product+Image'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
            
            <div class="product-info">
                <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                <div class="product-price">₹<?php echo number_format($product['price'], 2); ?></div>
                
                <div class="product-description">
                    <p><?php echo htmlspecialchars($product['description']); ?></p>
                </div>
                
                <div class="product-specs">
                    <h3>Product Specifications</h3>
                    <div class="specs-grid">
                        <div class="spec-item">
                            <span class="spec-label">Category</span>
                            <span class="spec-value"><?php echo !empty($product['category']) ? htmlspecialchars($product['category']) : 'Not specified'; ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Stock Status</span>
                            <span class="spec-value"><?php echo ($product['stock_quantity'] > 0) ? 'In Stock (' . $product['stock_quantity'] . ' available)' : 'Out of Stock'; ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Product ID</span>
                            <span class="spec-value">#<?php echo $product['id']; ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Added On</span>
                            <span class="spec-value"><?php echo date('F j, Y', strtotime($product['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
                
                <form method="POST" class="product-actions">
                    <div class="quantity-selector">
                        <label for="quantity">Quantity:</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                    </div>
                    
                    <button type="submit" name="add_to_cart" class="btn btn-primary">
                        <i class="fas fa-shopping-cart"></i> Add to Cart
                    </button>
                    
                    <a href="index.php#products" class="btn btn-outline">
                        Continue Shopping
                    </a>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Auto-hide success message after 5 seconds
        setTimeout(function() {
            const successMessage = document.getElementById('successMessage');
            if (successMessage) {
                successMessage.style.display = 'none';
            }
        }, 5000);
    </script>
</body>
</html>
<?php
// Close database connection
if (isset($con)) {
    $con->close();
}
?>
<!-- // <button class="btn-small btn-view" onclick="window.location.href='get_product_details.php?id=<?php// echo $product['id']; ?>'">View Details</button> -->