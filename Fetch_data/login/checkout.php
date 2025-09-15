<?php
// checkout.php
session_start();
$con = mysqli_connect("localhost", "root", "", "tribal_arts_db") or die("Couldn't connect");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=checkout");
    exit();
}

// Get user details from database
$userId = $_SESSION['user_id'];
$userQuery = mysqli_query($con, "SELECT * FROM users WHERE id = $userId");
$user = mysqli_fetch_assoc($userQuery);

// Get cart items from session
$cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$totalPrice = 0;

foreach ($cartItems as $item) {
    $totalPrice += $item['price'] * $item['quantity'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process the order
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $city = mysqli_real_escape_string($con, $_POST['city']);
    $state = mysqli_real_escape_string($con, $_POST['state']);
    $zip = mysqli_real_escape_string($con, $_POST['zip']);
    $paymentMethod = mysqli_real_escape_string($con, $_POST['payment_method']);
    
    // Insert order into database
    $orderQuery = "INSERT INTO orders (user_id, total_amount, status, shipping_address, city, state, zip_code, payment_method) 
                   VALUES ($userId, $totalPrice, 'pending', '$address', '$city', '$state', '$zip', '$paymentMethod')";
    
    if (mysqli_query($con, $orderQuery)) {
        $orderId = mysqli_insert_id($con);
        
        // Insert order items
        foreach ($cartItems as $productId => $item) {
            $itemQuery = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                          VALUES ($orderId, $productId, {$item['quantity']}, {$item['price']})";
            mysqli_query($con, $itemQuery);
        }
        
        // Clear the cart
        unset($_SESSION['cart']);
        
        // Redirect to success page
        header("Location: order_success.php?id=$orderId");
        exit();
    } else {
        $error = "There was an error processing your order. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Artisan Ark</title>
    <link href="https://fonts.googleapis.com/css2?family=Georgia&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
 <style>
 
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

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 0.8rem;
            margin: 0 1rem;
        }

        .nav-menu a {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 500;
            transition: var(--transition);
            padding: 0.5rem 0.8rem;
            border-radius: var(--border-radius);
            white-space: nowrap;
            font-size: 0.95rem;
        }

        .nav-menu a:hover,
        .nav-menu a.active {
            background-color: var(--primary-color);
            color: var(--white);
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

        .cart-icon:hover { color: var(--secondary-color); }

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

        .hidden { display: none; }

        .hamburger {
            display: none;
            flex-direction: column;
            justify-content: space-between;
            width: 30px;
            height: 21px;
            cursor: pointer;
        }

        .hamburger span {
            height: 3px;
            width: 100%;
            background-color: var(--primary-color);
            border-radius: 3px;
            transition: var(--transition);
        }

        /* Responsive styles */
        @media screen and (max-width: 968px) {
            .nav-menu { gap: 0.5rem; }
            .nav-menu a { padding: 0.4rem 0.6rem; font-size: 0.9rem; }
            .logo h1 { font-size: 1.5rem; }
            .logo p { font-size: 0.65rem; }
        }

        @media screen and (max-width: 768px) {
            .hamburger { display: flex; }
            .nav-menu {
                position: fixed;
                left: -100%;
                top: 70px;
                flex-direction: column;
                background-color: var(--white);
                width: 100%;
                text-align: center;
                transition: 0.3s;
                box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
                padding: 1rem 0;
                gap: 0;
            }
            .nav-menu.active { left: 0; }
            .nav-menu li { margin: 0.5rem 0; }
            .nav-menu a { padding: 0.8rem 2rem; display: block; }
            .logo-text { display: none; }
        }

        @media screen and (max-width: 480px) {
            .nav-container { padding: 0 15px; }
            .logo img { height: 40px; }
            .cart-icon { font-size: 1.3rem; }
        }
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
           
            min-height: 100vh;
            padding: 20px;
        }

        .login-container {
            background: var(--white);
            padding: 40px;
            border-radius: 10px;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 400px;
        }

        .login-title {
            text-align: center;
            margin-bottom: 30px;
            color: var(--primary-color);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 1rem;
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
            width: 100%;
            text-align: center;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
        }

        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
        }

        .register-link a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
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
                <a href="index.php#products" class="btn btn-outline" style="width: auto;">
                    <i class="fas fa-arrow-left"></i> Continue Shopping
                </a>
                <div class="cart-icon" onclick="window.location.href='index.php#cart'">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count">
                        <?php 
                        $cartCount = 0;
                        if (isset($_SESSION['cart'])) {
                            foreach ($_SESSION['cart'] as $item) {
                                $cartCount += $item['quantity'];
                            }
                        }
                        echo $cartCount > 0 ? $cartCount : '';
                        ?>
                    </span>
                </div>
            </div>
        </div>
    </header>

    <!-- Checkout Content -->
    <div class="container checkout-container">
        <h1 class="checkout-title">Checkout</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="checkout-grid">
            <div class="checkout-form">
                <h2 class="form-title">Shipping Information</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Shipping Address</label>
                        <input type="text" id="address" name="address" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="state">State</label>
                            <input type="text" id="state" name="state" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="zip">ZIP Code</label>
                        <input type="text" id="zip" name="zip" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select id="payment_method" name="payment_method" required>
                            <option value="">Select Payment Method</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="debit_card">Debit Card</option>
                            <option value="paypal">PayPal</option>
                            <option value="cod">Cash on Delivery</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Place Order</button>
                </form>
            </div>
            
            <div class="order-summary">
                <h2 class="summary-title">Order Summary</h2>
                
                <div class="order-items">
                    <?php if (count($cartItems) > 0): ?>
                        <?php foreach ($cartItems as $id => $item): ?>
                            <div class="order-item">
                                <span><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?></span>
                                <span>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Your cart is empty.</p>
                    <?php endif; ?>
                </div>
                
                <div class="order-total">
                    <span>Total:</span>
                    <span>₹<?php echo number_format($totalPrice, 2); ?></span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Simple form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            let isValid = true;
            const inputs = this.querySelectorAll('input[required], select[required]');
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.style.borderColor = 'red';
                } else {
                    input.style.borderColor = '#ddd';
                }
            });
            
            if (!isValid) {
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