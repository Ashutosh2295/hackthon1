<?php
session_start();
$con = mysqli_connect("localhost", "root", "", "tribal_arts_db") or die("Couldn't connect");

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $productId = $_POST['product_id'];
    $price = $_POST['price'];
    $quantity = (int) $_POST['quantity'];
    $name = $_POST['name'];
    $image = $_POST['image'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = [
            'name' => $name,
            'price' => $price,
            'quantity' => $quantity,
            'image' => $image
        ];
    }

    // Set success message in session to display after redirect
    $_SESSION['success_message'] = "Product added to cart!";
    
    // Redirect to prevent form resubmission on refresh
    header("Location: ".$_SERVER['PHP_SELF']."#products");
    exit();
}

// Handle Update Cart Quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    $productId = $_POST['product_id'];
    $quantity = (int) $_POST['quantity'];
    
    if (isset($_SESSION['cart'][$productId])) {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$productId]);
        } else {
            $_SESSION['cart'][$productId]['quantity'] = $quantity;
        }
    }
    
    header("Location: ".$_SERVER['PHP_SELF']."#cart");
    exit();
}

// Handle Remove from Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_from_cart'])) {
    $productId = $_POST['product_id'];
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
    
    header("Location: ".$_SERVER['PHP_SELF']."#cart");
    exit();
}

// Cart counts
$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
$totalPrice = 0;
if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    foreach ($_SESSION['cart'] as $item) {
        $totalPrice += $item['price'] * $item['quantity'];
    }
}

// Fetch products from database
$query = "SELECT * FROM products WHERE status = 'active' OR status = 'in stock'";
$result = $con->query($query);
$products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Check for success message
$successMessage = "";
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artisan Ark - Preserving Culture, Supporting Artists</title>
    <link href="https://fonts.googleapis.com/css2?family=Georgia&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Your existing CSS styles remain unchanged */
        #para1{ color: black; }
        #para2>ul>li>a{ color: black; }
        .fa-globe:before{ color: black; }
        .fa-book-open:before { color: #a07455; }
        .fa-hand-holding-heart:before { color: #5edd22; }
        .fa-search:before{ color: black; }
        
        :root {
            --primary-color: #3c6e71;
            --secondary-color: #3c6e71;
            --accent-color: #e68057;
            --text-dark: #a25748;
            --text-light: #bf7587;
            --background: #f0ebd8;
            --white: #dfc8a0;
            --gray-light: #F0F0F0;
            --gray-medium: #CCCCCC;
            --border-color: #D7C9AA;
            --shadow: 0 4px 8px rgba(154, 100, 91, 0.15);
            --border-radius: 8px;
            --transition: all 0.3s ease;
        }

        /* Reset and Base Styles */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            line-height: 1.6;
            color: var(--text-dark);
            background-color: var(--background);
        }

        img { max-width: 100%; height: auto; display: block; }

        /* Typography */
        h1, h2, h3, h4, h5, h6 {
            font-weight: 600;
            margin-bottom: 1rem;
            color: black;
        }

        h1 { font-size: 2.5rem; }
        h2 { font-size: 2rem; }
        h3 { font-size: 1.5rem; }
        h4 { font-size: 1.25rem; }

        p { margin-bottom: 1rem; }

        /* Layout Components */
        .container {
            margin-top: 100px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .section { padding: 5rem 0; }

        .section-title {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            text-align: center;
            margin-bottom: 2rem;
        }

        .section-subtitle {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            text-align: center;
            color: black;
            max-width: 700px;
            margin: 0 auto 3rem;
        }

        /* Header & Navigation */
        .main-header {
            background: var(--white);
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
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
        }

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

        /* Content for demonstration */
        .content {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 20px;
            text-align: center;
        }
        
        .content h2 {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        /* Mobile Menu */
        .hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
            gap: 3px;
        }

        .hamburger span {
            width: 25px;
            height: 3px;
            background-color: var(--primary-color);
            transition: var(--transition);
        }

        /* Hero Section */
        .hero {
            background-image: url('logos/wallpaper.jpg');
            background-size: cover;
            background-position: center;
            color: var(--text-light);
            padding: 8rem 0;
            text-align: center;
        }

        .hero-content { max-width: 800px; margin: 0 auto; }

        .hero h1 {
            font-size: 3.5rem;
            color: var(--text-light);
            margin-bottom: 1rem;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: var(--text-light);
        }

        /* Button Styles */
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            font-family: 'Poppins', sans-serif;
        }

        .btn-primary { border: 2px solid black; }

        .btn-primary:hover {
            background: var(--accent-color);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(156, 59, 43, 0.3);
        }

        .btn-secondary {
            background: transparent;
            color: #bf7587;
            border: px solid #dfc8a0;
        }

        .btn-secondary:hover {
            background: white;
            color: var(--primary-color);
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            color: black;
            border: 2px solid black;
        }

        .btn-outline:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        /* Stats Section */
        .stats {
            color: black;
            padding: 3rem 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            text-align: center;
        }

        .stat-item { padding: 1rem; }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* Featured Artisans */
        .artisan-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .artisan-card {
            background: var(--white);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
        }

        .artisan-card:hover { transform: translateY(-10px); }

        .artisan-image {
            height: 250px;
            overflow: hidden;
        }

        .artisan-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .artisan-card:hover .artisan-image img { transform: scale(1.05); }

        .artisan-info { padding: 1.5rem; }

        .artisan-name {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }

        .artisan-tribe {
            color: var(--primary-color);
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .artisan-specialty {
            color: var(--text-dark);
            margin-bottom: 1rem;
        }

        .artisan-stats {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }

        /* Product Categories */
        .products { background: var(--gray-light); }
        
        .filter-tabs {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .filter-tab {
            padding: 10px 20px;
            border: 2px solid black;
            background: transparent;
            color: black;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .filter-tab.active,
        .filter-tab:hover { /* background: var(--primary-color); */ }

        .search-container {
            position: relative;
            max-width: 400px;
            margin: 0 auto 3rem;
        }

        .search-container input {
            width: 100%;
            padding: 15px 50px 15px 20px;
            border: 2px solid black;
            border-radius: 25px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .search-container input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .search-container i {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .product-card {
            background: var(--white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
        }

        .product-card:hover { transform: translateY(-5px); }

        .product-image {
            height: 200px;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .product-card:hover .product-image img { transform: scale(1.05); }

        .product-info { padding: 1.5rem; }

        .product-name {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }

        .product-artisan {
            color: var(--primary-color);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .product-description {
            color: var(--text-dark);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .product-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: black;
            margin-bottom: 1rem;
        }

        .product-actions {
            display: flex;
            gap: 1rem;
        }

        .btn-small {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .btn-add-cart {
            background-color: black;
            color: white;
        }

        .btn-add-cart:hover { background: var(--accent-color); }

        .btn-view {
            background: transparent;
            color: black;
            border: 2px solid black;
        }

        .btn-view:hover {
            background: var(--primary-color);
            color: white;
        }

        /* Cultural Stories */
        .stories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .story-card {
            background: var(--white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
        }

        .story-card:hover { transform: translateY(-5px); }

        .story-image {
            height: 150px;
            overflow: hidden;
        }

        .story-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .story-content { padding: 1.5rem; }

        .story-content h3 {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        .story-content p {
            color: var(--text-dark);
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        /* About Section */
        .about { background: var(--gray-light); }

        .about-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .about-text h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--text-dark);
        }

        .about-text p {
            font-size: 1.1rem;
            color: var(--text-dark);
            margin-bottom: 2rem;
            line-height: 1.7;
        }

        .mission-points {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .mission-point {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .mission-point i {
            font-size: 2rem;
            color: var(--primary-color);
            width: 60px;
            text-align: center;
        }

        .mission-point h4 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }

        .mission-point p {
            color: var(--text-dark);
            margin: 0;
        }

        .about-image {
            display: flex;
            justify-content: center;
        }

        .about-image img {
            width: 100%;
            max-width: 400px;
            border-radius: 20px;
            box-shadow: var(--shadow);
        }

        /* Newsletter */
        .newsletter {
            color: #bf7587;
            text-align: center;
            padding: 4rem 0;
        }

        .newsletter h2 {
            color: black;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .newsletter p {
            color: black;
            font-size: 1.1rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .newsletter-form {
            display: flex;
            max-width: 400px;
            margin: 0 auto;
            gap: 1rem;
        }

        .newsletter-form input {
            border: 2px solid black;
            flex: 1;
            padding: 15px 20px;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
        }

        .newsletter-form input:focus { outline: none; }

        /* Footer */
        .main-footer {
            background-color: #adb8ba;
            color: var(--text-light);
            padding: 3rem 0 1rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h3,
        .footer-section h4 {
            color: black;
            margin-bottom: 1rem;
        }

        .footer-section ul { list-style: none; }

        .footer-section ul li { margin-bottom: 0.5rem; }

        .footer-section ul li a {
            color: black;
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-section ul li a:hover { color: var(--secondary-color); }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: black;
            color: var(--text-light);
            opacity: 0.8;
        }
        
        /* BackGround colour */
        .artisans{ background-color: #adb8ba; }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            padding: 2rem 2rem 1rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            color: var(--text-dark);
        }

        .close {
            font-size: 2rem;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close:hover { color: var(--primary-color); }

        .modal-body { padding: 2rem; }

        /* Cart Styles */
        .cart-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }

        .cart-item:last-child { border-bottom: none; }

        .cart-item-image {
            width: 60px;
            height: 60px;
            background: var(--gray-light);
            border-radius: 8px;
            overflow: hidden;
        }

        .cart-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cart-item-info { flex: 1; }

        .cart-item-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .cart-item-price {
            color: var(--primary-color);
            font-weight: 600;
        }

        .cart-item-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .quantity-btn {
            width: 30px;
            height: 30px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .quantity-btn:hover { background: var(--gray-light); }

        .cart-summary {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #eee;
            text-align: center;
        }

        .cart-total {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        /* Success message */
        .success-message {
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 10px;
            position: fixed;
            top: 100px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 5px;
            z-index: 1000;
            animation: fadeOut 3s forwards;
        }

        @keyframes fadeOut {
            0% { opacity: 1; }
            70% { opacity: 1; }
            100% { opacity: 0; display: none; }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hamburger { display: flex; }

            .nav-menu {
                position: fixed;
                left: -100%;
                top: 70px;
                flex-direction: column;
                background-color: white;
                width: 100%;
                text-align: center;
                transition: 0.3s;
                box-shadow: 0 10px 27px rgba(0, 0, 0, 0.05);
                padding: 2rem 0;
                gap: 0;
            }

            .nav-menu.active { left: 0; }

            .nav-menu li { margin: 1rem 0; }

            .hero h1 { font-size: 2.5rem; }

            .about-content {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .newsletter-form { flex-direction: column; }

            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .filter-tabs {
                justify-content: flex-start;
                overflow-x: auto;
                padding-bottom: 1rem;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }

            .modal-content {
                margin: 10% auto;
                width: 95%;
            }
        }

        @media (max-width: 480px) {
            .hero h1 { font-size: 2rem; }
            .section-title { font-size: 2rem; }
            .products-grid { grid-template-columns: 1fr; }
            .artisan-grid { grid-template-columns: 1fr; }
            .stories-grid { grid-template-columns: 1fr; }
        }

        /* Animation Classes */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .slide-in-left {
            opacity: 0;
            transform: translateX(-50px);
            transition: all 0.6s ease;
        }

        .slide-in-left.visible {
            opacity: 1;
            transform: translateX(0);
        }

        .slide-in-right {
            opacity: 0;
            transform: translateX(50px);
            transition: all 0.6s ease;
        }

        .slide-in-right.visible {
            opacity: 1;
            transform: translateX(0);
        }
         .success-message {
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 10px;
            position: fixed;
            top: 100px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 5px;
            z-index: 1000;
            animation: fadeOut 3s forwards;
        }
        
        @keyframes fadeOut {
            0% { opacity: 1; }
            70% { opacity: 1; }
            100% { opacity: 0; display: none; }
        }
        
        /* Product details modal */
        .product-detail {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .product-detail-image {
            text-align: center;
        }
        
        .product-detail-image img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
        }
        
        .product-specs {
            margin-top: 20px;
        }
        
        .product-specs h4 {
            margin-bottom: 10px;
            color: var(--primary-color);
        }
        
        .product-specs p {
            margin-bottom: 8px;
        }
        
        /* Quantity controls in cart */
        .cart-quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quantity-btn {
            width: 30px;
            height: 30px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .quantity-btn:hover {
            background: var(--gray-light);
        }
        
        .quantity-input {
            width: 40px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }

    </style>
</head>
<body>
      <!-- Success message display -->
    <?php if (!empty($successMessage)): ?>
        <div class="success-message" id="successMessage">
            <?php echo $successMessage; ?>
        </div>
        <script>
            setTimeout(function() {
                document.getElementById('successMessage').style.display = 'none';
            }, 3000);
        </script>
    <?php endif; ?>

    <!-- Header & Navigation -->
    <header class="main-header">
        <nav class="navbar">
            <div class="nav-container">
                <a href="#home" class="logo">
                    <img src="/IMG-20250914-WA0002.jpg" alt="Artisan Ark Logo">
                    <div class="logo-text">
                        <h1>Artisan Ark</h1>
                        <p>Preserving Culture • Supporting Artists</p>
                    </div>
                </a>

                <ul class="nav-menu" id="navMenu">
                    <li><a href="#home" class="active">Home</a></li>
                    <li><a href="#artisans">Trending</a></li>
                    <li><a href="#products">Handcrafted Treasures</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>

                <!-- In your navigation section -->
<div class="nav-actions">
    <?php if (isset($_SESSION['user_id'])): ?>
        <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        <a href="logout.php" class="btn btn-outline">Logout</a>
    <?php else: ?>
        <a href="login.php" class="btn btn-outline">Login</a>
        <a href="register.php" class="btn btn-primary">Register</a>
    <?php endif; ?>
    
    <div class="cart-icon" onclick="openCartModal()">
        <i class="fas fa-shopping-cart"></i>
        <span class="cart-count <?php echo $cartCount > 0 ? '' : 'hidden'; ?>" id="cart-count">
            <?php echo $cartCount; ?>
        </span>
    </div>
    <div class="hamburger" id="hamburger" aria-label="Toggle menu">
        <span></span>
        <span></span>
        <span></span>
    </div>
</div>
            </div>
        </nav>
    </header>


    <!-- Hero Section -->
    <section id="home" class="hero">    
        <div class="container">
            <div class="hero-content">
                <div class="imgBx1">
                    <h1 style="visibility: hidden;">Lorem ipsum dolor sit amet consectetur, adipisicing elit. usamus. Placeat, dicta tempora. Lorem, ipsum dolor sit</h1>
                </div>                
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number" data-target="150">0</div>
                    <div class="stat-label">Artisans</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" data-target="500">0</div>
                    <div class="stat-label">Products</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" data-target="25">0</div>
                    <div class="stat-label">Tribes</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" data-target="1000">0</div>
                    <div class="stat-label">Happy Customers</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Artisans -->
    <section id="artisans" class="section artisans">
        <div class="container">
            <h2 class="section-title">Trending</h2>
            <p class="section-subtitle">Handmade Heritage, Delivered to You.</p>
            
            <div class="artisan-grid" id="artisanGrid">
                <!-- Artisan cards will be populated by JavaScript -->
            </div>
        </div>
    </section>

    <!-- Product Categories -->
      <!-- Product Categories -->
    <section id="products" class="section products">
        <div class="container">
            <h2 class="section-title">Handcrafted Treasures</h2>
            <p class="section-subtitle">Authentic tribal crafts from across India</p>
            
            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <button class="filter-tab active" data-category="all">All Products</button>
                <button class="filter-tab" data-category="pottery">Pottery</button>
                <button class="filter-tab" data-category="textiles">Textiles</button>
                <button class="filter-tab" data-category="woodwork">Woodwork</button>
                <button class="filter-tab" data-category="paintings">Paintings</button>
            </div>
            
            <!-- Search Bar -->
            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Search products...">
                <i class="fas fa-search"></i>
            </div>
            
            <!-- Products Grid -->
            <div class="products-grid" id="productsGrid">
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $product): ?>
                        <div class="product-card fade-in">
                            <div class="product-image">
                                <img src="<?php echo !empty($product['image']) ? htmlspecialchars($product['image']) : 'https://via.placeholder.com/300x200?text=Product+Image'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>
                            <div class="product-info">
                                <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                                <div class="product-price">₹<?php echo number_format($product['price'], 2); ?></div>
                                <div class="product-actions">
                                    <button class="btn-small btn-view" onclick="viewProduct(<?php echo $product['id']; ?>)">View Details</button>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <input type="hidden" name="name" value="<?php echo htmlspecialchars($product['name']); ?>">
                                        <input type="hidden" name="price" value="<?php echo $product['price']; ?>">
                                        <input type="hidden" name="image" value="<?php echo htmlspecialchars($product['image']); ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" name="add_to_cart" class="btn-small btn-add-cart">Add to Cart</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No products found in the database.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Cultural Stories -->
    <section id="stories" class="section stories">
        <div class="container">
            <h2 class="section-title">Cultural Stories</h2>
            <p class="section-subtitle">Discover the rich heritage behind each craft</p>
            
            <div class="stories-grid">
                <div class="story-card">
                    <div class="story-image">
                        <img src="https://media.istockphoto.com/id/944997508/vector/background-for-decorating-textiles.jpg?s=612x612&w=0&k=20&c=s2_Hv7rhurHq-50oP9ZkzZt9o6rBYA7aANkIE50As-E=" alt="Warli Art">
                    </div>
                    <div class="story-content">
                        <h3>Warli Art</h3>
                        <p>Ancient tribal paintings from Maharashtra depicting daily life and nature in simple geometric forms.</p>
                        <button class="btn btn-outline">Read More</button>
                    </div>
                </div>
                
                <div class="story-card">
                    <div class="story-image">
                        <img src="https://media.istockphoto.com/id/1144573957/photo/cool-music-graffiti-in-urban-style.jpg?s=612x612&w=0&k=20&c=2GKNhb5Rsmkhh2HI6fzx4h8v0nIbmh84uYvhCz0LwZ4=" alt="Gond Art">
                    </div>
                    <div class="story-content">
                        <h3>Gond Art</h3>
                        <p>Vibrant paintings from Madhya Pradesh featuring intricate patterns inspired by nature and folklore.</p>
                        <button class="btn btn-outline">Read More</button>
                    </div>
                </div>
                
                <div class="story-card">
                    <div class="story-image">
                        <img src="https://media.istockphoto.com/id/841755434/photo/detail-handmade-pashmina-shawl-with-delicate-embroidery-at-outdoor-crafts-market-in-kathmandu.jpg?s=612x612&w=0&k=20&c=6wnbexSI1y-UIaPE0-51VBmsvxZxPL2vRp94psLKY-4=" alt="Santhal Crafts">
                    </div>
                    <div class="story-content">
                        <h3>Tribal Shawl</h3>
                        <p>Handwoven shawl with traditional Kondh tribe patterns, dyed with natural colors.</p>
                        <button class="btn btn-outline">Read More</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="section about">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2>Our Mission</h2>
                    <p>We believe that tribal art and crafts are not just products, but living expressions of cultural heritage. Our platform connects traditional artisans with a global audience, ensuring their skills and stories are preserved for future generations.</p>
                    
                    <div class="mission-points">
                        <div class="mission-point">
                            <i class="fas fa-hand-holding-heart"></i>
                            <div>
                                <h4>Support Artisans</h4>
                                <p>Direct income to tribal communities</p>
                            </div>
                        </div>
                        <div class="mission-point">
                            <i class="fas fa-book-open"></i>
                            <div>
                                <h4>Preserve Culture</h4>
                                <p>Document and share traditional knowledge</p>
                            </div>
                        </div>
                        <div class="mission-point">
                            <i class="fas fa-globe"></i>
                            <div>
                                <h4>Global Reach</h4>
                                <p>Connect artisans with worldwide customers</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1527525443983-6e60c75fff46?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Tribal Artisans">
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="newsletter">
        <div class="container">
            <h2>Stay Connected</h2>
            <p>Subscribe to our newsletter for updates on new artisans, products, and cultural stories</p>
            <div class="newsletter-form">
                <input type="email" placeholder="Your email address">
                <button class="btn btn-primary">Subscribe</button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="main-footer" id="contact">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Tribal Arts Heritage</h3>
                    <p id="para1">Dedicated to preserving and promoting the artistic traditions of tribal communities worldwide.</p>
                </div>

                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul id="para2">
                        <li><a href="#artisans">Artisans</a></li>
                        <li><a href="#products">Artwork</a></li>
                        <li><a href="#stories">Stories</a></li>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4 id="para3">For Artists</h4>
                    <ul>
                        <li><a href="#">Join Platform</a></li>
                        <li><a href="#">Guidelines</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p style="color: black;">&copy; 2025 Artisan Arts Copyrights Rights Reserved</p>
            </div>
        </div>
    </footer>

    <!-- Shopping Cart Modal -->
  <!-- Shopping Cart Modal -->
    <div id="cartModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Shopping Cart</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div id="cartItems">
                    <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                        <?php foreach ($_SESSION['cart'] as $id => $item): ?>
                            <div class="cart-item">
                                <div class="cart-item-image">
                                    <img src="<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : 'https://via.placeholder.com/60x60?text=Image'; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="cart-item-info">
                                    <div class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div class="cart-item-price">₹<?php echo number_format($item['price'], 2); ?></div>
                                    <div class="cart-quantity-controls">
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                                            <input type="hidden" name="quantity" value="<?php echo $item['quantity'] - 1; ?>">
                                            <button type="submit" name="update_cart" class="quantity-btn">-</button>
                                        </form>
                                        <span class="quantity"><?php echo $item['quantity']; ?></span>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                                            <input type="hidden" name="quantity" value="<?php echo $item['quantity'] + 1; ?>">
                                            <button type="submit" name="update_cart" class="quantity-btn">+</button>
                                        </form>
                                    </div>
                                </div>
                                <div class="cart-item-actions">
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                                        <button type="submit" name="remove_from_cart" class="quantity-btn">×</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Your cart is empty.</p>
                    <?php endif; ?>
                </div>
                <div class="cart-summary">
                    <div class="cart-total">
                        <strong>Total: ₹<?php echo number_format($totalPrice, 2); ?></strong>
                    </div>
                    <button class="btn btn-primary" onclick="window.location.href='checkout.php'">Checkout</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Detail Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="productModalTitle">Product Details</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div id="productModalContent"></div>
            </div>
        </div>
    </div>



    <script>
                // Function to view product details
        function viewProduct(productId) {
            // In a real implementation, you would fetch product details from the server
            // For now, we'll show a placeholder modal
            document.getElementById('productModalTitle').textContent = "Product Details";
            document.getElementById('productModalContent').innerHTML = `
                <div class="product-detail">
                    <div class="product-detail-image">
                        <img src="https://via.placeholder.com/400x300?text=Product+Image" alt="Product Image">
                    </div>
                    <div class="product-detail-info">
                        <h3>Product Name</h3>
                        <div class="product-price">₹0.00</div>
                        <p class="product-description">Product description will be shown here.</p>
                        
                        <div class="product-specs">
                            <h4>Specifications:</h4>
                            <p><strong>Materials:</strong> Information not available</p>
                            <p><strong>Dimensions:</strong> Information not available</p>
                            <p><strong>Artisan:</strong> Information not available</p>
                        </div>
                        
                        <div class="product-actions">
                            <button class="btn btn-primary" onclick="addToCartDirect(${productId})">Add to Cart</button>
                            <button class="btn btn-outline" onclick="closeProductModal()">Close</button>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('productModal').style.display = 'block';
            
            // In a real implementation, you would fetch the actual product details:
            /*
            fetch('get_product_details.php?id=' + productId)
                .then(response => response.json())
                .then(product => {
                    // Populate the modal with product details
                    document.getElementById('productModalTitle').textContent = product.name;
                    // ... rest of the code to populate product details
                })
                .catch(error => console.error('Error:', error));
            */
        }

        // Function to add product to cart directly from the product modal
        function addToCartDirect(productId) {
            // This would need to be implemented with actual product data
            alert("This would add product " + productId + " to cart. Implementation needed.");
        }

        // Function to open product modal
        function openProductModal() {
            document.getElementById('productModal').style.display = 'block';
        }

        // Function to close product modal
        function closeProductModal() {
            document.getElementById('productModal').style.display = 'none';
        }

        // Function to open cart modal
        function openCartModal() {
            document.getElementById('cartModal').style.display = 'block';
        }

        // Function to close cart modal
        function closeCartModal() {
            document.getElementById('cartModal').style.display = 'none';
        }

        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            const cartModal = document.getElementById('cartModal');
            const productModal = document.getElementById('productModal');
            
            if (event.target === cartModal) {
                closeCartModal();
            }
            
            if (event.target === productModal) {
                closeProductModal();
            }
        });
          // Global variables
        let cart = [];
        let currentFilter = 'all';
        let searchTerm = '';

        // Sample data for artisans
        const artisansData = [
            {
                 id: 1,
                name: "Wooden Tribal Mask",
                category: "woodwork",
                price: 2200,
                description: "Hand-carved wooden mask representing tribal deities, crafted using traditional Santal techniques.",
                image: "https://media.istockphoto.com/id/185249362/photo/carved-wooden-mask.jpg?s=612x612&w=0&k=20&c=coVvFlvn2tInJMoscCDxzH_kUVrLugAmPdV-bU30_Fw=",
                rating: 4.6,
                reviews: 12,
                inStock: true,
                materials: "Teak wood, natural finish",
                dimensions: "8x6 inches"
            },
            
                {
  id: 2,
                name: "Bamboo Storage Basket",
                
                category: "woodwork",
                price: 800,
                description: "Handwoven bamboo basket perfect for storage, made using traditional Santhal techniques.",
                image: "https://media.istockphoto.com/id/2193058717/photo/wicker-basket-bowl-with-cover-for-serving-or-storage-on-wooden-desk.jpg?s=612x612&w=0&k=20&c=Jea_VWRRM7_HHoyLBhTktZ2NNLxUFOKL-ZI2mwZR-Gw=",
                rating: 4.7,
                reviews: 31,
                inStock: true,
                materials: "Natural bamboo",
                dimensions: "10x12x8 inches"


            },
            
{
  
  id: 3,
                name: "Gond Art Canvas",
                
                category: "paintings",
                price: 3200,
                description: "Vibrant Gond painting featuring intricate nature patterns and tribal folklore elements.",
                image: "https://media.istockphoto.com/id/1144573957/photo/cool-music-graffiti-in-urban-style.jpg?s=612x612&w=0&k=20&c=2GKNhb5Rsmkhh2HI6fzx4h8v0nIbmh84uYvhCz0LwZ4=",
                rating: 4.8,
                reviews: 18,
                inStock: true,
                materials: "Acrylic on canvas",
                dimensions: "14x18 inches"


}



            
        ];

        // Sample data for products
        const productsData = [
            {
                id: 1,
                name: "Warli Tribal Painting",
                category: "paintings",
                price: 2500,
                description: "Traditional Warli painting depicting daily village life with geometric patterns and natural themes.",
                image: "https://media.istockphoto.com/id/944997508/vector/background-for-decorating-textiles.jpg?s=612x612&w=0&k=20&c=s2_Hv7rhurHq-50oP9ZkzZt9o6rBYA7aANkIE50As-E=",
                rating: 4.9,
                reviews: 23,
                inStock: true,
                materials: "Natural pigments on canvas",
                dimensions: "12x16 inches"
            },
            {
                id: 2,
                name: "Gond Art Canvas",
                
                category: "paintings",
                price: 3200,
                description: "Vibrant Gond painting featuring intricate nature patterns and tribal folklore elements.",
                image: "https://media.istockphoto.com/id/1144573957/photo/cool-music-graffiti-in-urban-style.jpg?s=612x612&w=0&k=20&c=2GKNhb5Rsmkhh2HI6fzx4h8v0nIbmh84uYvhCz0LwZ4=",
                rating: 4.8,
                reviews: 18,
                inStock: true,
                materials: "Acrylic on canvas",
                dimensions: "14x18 inches"
            },
            {
                id: 3,
                name: "Bamboo Storage Basket",
                
                category: "woodwork",
                price: 800,
                description: "Handwoven bamboo basket perfect for storage, made using traditional Santhal techniques.",
                image: "https://media.istockphoto.com/id/2193058717/photo/wicker-basket-bowl-with-cover-for-serving-or-storage-on-wooden-desk.jpg?s=612x612&w=0&k=20&c=Jea_VWRRM7_HHoyLBhTktZ2NNLxUFOKL-ZI2mwZR-Gw=",
                rating: 4.7,
                reviews: 31,
                inStock: true,
                materials: "Natural bamboo",
                dimensions: "10x12x8 inches"
            },
            {
                id: 4,
                name: "Terracotta Water Pot",
               
                category: "pottery",
                price: 1200,
                description: "Traditional terracotta water pot with beautiful tribal motifs, perfect for home decoration.",
                image: "https://media.istockphoto.com/id/1197797103/photo/clay-water-pot-with-clay-lid.jpg?s=612x612&w=0&k=20&c=Rl5ak_uyo8krLJToRGe3BjKnke1BgWL2k7V2DS3gc2s=",
                rating: 4.9,
                reviews: 27,
                inStock: true,
                materials: "Clay, natural glaze",
                dimensions: "8x10 inches"
            },
            {
                id: 5,
                name: "Tribal Textile Shawl",
                
                category: "textiles",
                price: 1800,
                description: "Handwoven shawl with traditional Kondh tribe patterns, dyed with natural colors.",
                image: "https://media.istockphoto.com/id/841755434/photo/detail-handmade-pashmina-shawl-with-delicate-embroidery-at-outdoor-crafts-market-in-kathmandu.jpg?s=612x612&w=0&k=20&c=6wnbexSI1y-UIaPE0-51VBmsvxZxPL2vRp94psLKY-4=",
    
                rating: 4.8,
                reviews: 15,
                inStock: true,
                materials: "Cotton, natural dyes",
                dimensions: "60x40 inches"
            },
            {
                id: 6,
                name: "Wooden Tribal Mask",
                
                category: "woodwork",
                price: 2200,
                description: "Hand-carved wooden mask representing tribal deities, crafted using traditional Santal techniques.",
                image: "https://media.istockphoto.com/id/185249362/photo/carved-wooden-mask.jpg?s=612x612&w=0&k=20&c=coVvFlvn2tInJMoscCDxzH_kUVrLugAmPdV-bU30_Fw=",
                rating: 4.6,
                reviews: 12,
                inStock: true,
                materials: "Teak wood, natural finish",
                dimensions: "8x6 inches"
            }
        ];

        // Initialize the application
        document.addEventListener('DOMContentLoaded', function() {
            initializeApp();
        });

        function initializeApp() {
            setupEventListeners();
            animateStats();
            setupScrollAnimations();
            setupMobileMenu();
        }



        // Event Listeners
        function setupEventListeners() {
            // Navigation scroll effect
            window.addEventListener('scroll', handleScroll);
            
            // Filter tabs
            document.querySelectorAll('.filter-tab').forEach(tab => {
                tab.addEventListener('click', function() {
                    // This would need to be implemented with AJAX or page reload
                    // For now, just update the UI
                    document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                });
            });
            
            // Search functionality
            document.getElementById('searchInput').addEventListener('input', function() {
                // This would need to be implemented with AJAX or page reload
                console.log('Search term:', this.value);
            });
            
            // Cart modal
            document.querySelector('.cart-icon').addEventListener('click', openCartModal);
            document.querySelector('#cartModal .close').addEventListener('click', closeCartModal);
            
            // Newsletter form
            document.querySelector('.newsletter-form button').addEventListener('click', handleNewsletter);
        }

        // Navigation scroll effect
        function handleScroll() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 100) {
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
            }
        }

        // Mobile menu functionality
        function setupMobileMenu() {
            const navToggle = document.querySelector('.hamburger');
            const navMenu = document.querySelector('.nav-menu');
            
            if (navToggle) {
                navToggle.addEventListener('click', function() {
                    navToggle.classList.toggle('active');
                    navMenu.classList.toggle('active');
                });
            }
            
            // Close menu when clicking on a link
            document.querySelectorAll('.nav-menu a').forEach(link => {
                link.addEventListener('click', function() {
                    document.querySelector('.hamburger').classList.remove('active');
                    navMenu.classList.remove('active');
                });
            });
        }

        // View product details
        function viewProduct(productId) {
            // Redirect to product detail page
            window.location.href = 'product.php?id=' + productId;
        }

        // Cart modal functions
        function openCartModal() {
            const modal = document.getElementById('cartModal');
            modal.style.display = 'block';
        }

        function closeCartModal() {
            const modal = document.getElementById('cartModal');
            modal.style.display = 'none';
        }

        // Animate stats
        function animateStats() {
            const stats = document.querySelectorAll('.stat-number');
            
            const animateValue = (element, start, end, duration) => {
                let startTimestamp = null;
                const step = (timestamp) => {
                    if (!startTimestamp) startTimestamp = timestamp;
                    const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                    const value = Math.floor(progress * (end - start) + start);
                    element.textContent = value;
                    if (progress < 1) {
                        window.requestAnimationFrame(step);
                    }
                };
                window.requestAnimationFrame(step);
            };
            
            // Intersection Observer for stats animation
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const target = parseInt(entry.target.dataset.target);
                        animateValue(entry.target, 0, target, 2000);
                        observer.unobserve(entry.target);
                    }
                });
            });
            
            stats.forEach(stat => {
                observer.observe(stat);
            });
        }

        // Scroll animations
        function setupScrollAnimations() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            });
            
            document.querySelectorAll('.fade-in, .slide-in-left, .slide-in-right').forEach(el => {
                observer.observe(el);
            });
        }

        // Handle newsletter subscription
        function handleNewsletter() {
            const emailInput = document.querySelector('.newsletter-form input');
            const email = emailInput.value.trim();
            
            if (!email) {
                alert('Please enter your email address.');
                return;
            }
            
            if (!isValidEmail(email)) {
                alert('Please enter a valid email address.');
                return;
            }
            
            // In a real app, this would send the email to a server
            alert(`Thank you for subscribing with ${email}! You'll receive updates about our tribal arts.`);
            emailInput.value = '';
        }

        // Email validation
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            const cartModal = document.getElementById('cartModal');
            
            if (event.target === cartModal) {
                closeCartModal();
            }
        });

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>

    <?php
    // Close database connection
    if (isset($con)) {
        $con->close();
    }
    ?>
</body>
</html>