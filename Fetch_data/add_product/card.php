<?php
session_start();
$con = mysqli_connect("localhost", "root", "", "tribal_arts_db") or die("Couldn't connect");

if (!isset($_SESSION['valid'])) {
    echo "Run"; // You can redirect instead
}

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $productId = $_POST['product_id'];
    $price = $_POST['price'];
    $quantity = (int) $_POST['quantity'];
    $name = $_POST['name'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = [
            'name' => $name,
            'price' => $price,
            'quantity' => $quantity
        ];
    }

    echo "<p style='text-align:center; color:green;'>🛒 Product added to cart!</p>";
}

// Handle Remove from Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_from_cart'])) {
    $productId = $_POST['product_id'];
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
}

// Cart counts
$cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
$totalPrice = 0;
if ($cartCount > 0) {
    foreach ($_SESSION['cart'] as $item) {
        $totalPrice += $item['price'] * $item['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Product Cards</title>
  <style>
    body {
      background: #f4f4f4;
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 40px;
    }
    .card-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 20px;
    }
    .card {
      flex: 0 0 calc(33.333% - 20px);
      box-sizing: border-box;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.08);
      padding: 24px;
      text-align: center;
      height: auto;
    }
    .card img {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 16px;
    }
    .card h2 {
      margin: 0 0 8px;
      font-size: 1.4em;
      color: #333;
    }
    .card p {
      color: #666;
      font-size: 0.95em;
      margin: 4px 0;
    }
    .card .status {
      font-weight: bold;
      color: green;
    }
    .card form {
      margin-top: 12px;
    }
    .card input[type="number"] {
      width: 60px;
      padding: 6px;
      margin-right: 8px;
    }
    .card button {
      padding: 6px 12px;
      background: #0078d4;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
    .card button:hover {
      background: #005fa3;
    }
    .cart-icon {
      position: fixed;
      top: 20px;
      right: 30px;
      background: #0078d4;
      color: white;
      padding: 10px 14px;
      border-radius: 50%;
      font-size: 20px;
      cursor: pointer;
      z-index: 1000;
    }
    .cart-count {
      background: red;
      color: white;
      font-size: 12px;
      padding: 2px 6px;
      border-radius: 50%;
      vertical-align: top;
      margin-left: 4px;
    }
    .cart-popup {
      display: none;
      position: fixed;
      top: 70px;
      right: 30px;
      background: white;
      border: 1px solid #ccc;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
      padding: 20px;
      border-radius: 8px;
      z-index: 1000;
      width: 300px;
    }
    .cart-popup h3 {
      margin-top: 0;
      font-size: 1.2em;
      color: #333;
    }
    .cart-popup ul {
      padding-left: 20px;
    }
    .cart-popup li {
      margin-bottom: 10px;
      font-size: 0.95em;
    }
    .remove-btn {
      background: red;
      color: white;
      border: none;
      padding: 2px 6px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 12px;
      margin-left: 6px;
    }
    .checkout-btn {
      display: block;
      width: 100%;
      padding: 8px;
      background: green;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      margin-top: 10px;
    }
  </style>
</head>
<body>

<div class="cart-icon" onclick="toggleCartPopup()">
  🛒 <span class="cart-count"><?= $cartCount ?></span>
</div>
<div class="cart-popup" id="cartPopup">
  <h3>Your Cart</h3>
  <?php if ($cartCount > 0): ?>
    <ul>
  <?php foreach ($_SESSION['cart'] as $id => $item): ?>
    <li>
      <?= isset($item['name']) ? htmlspecialchars($item['name']) : 'Unnamed Product' ?>
      | Qty: <?= $item['quantity'] ?> | ₹<?= $item['price'] ?>
      <form method="POST" style="display:inline;">
        <input type="hidden" name="product_id" value="<?= $id ?>">
        <button type="submit" name="remove_from_cart" class="remove-btn">X</button>
      </form>
    </li>
  <?php endforeach; ?>
</ul>

    <p><strong>Total Items:</strong> <?= $cartCount ?></p>
    <p><strong>Total Price:</strong> ₹<?= $totalPrice ?></p>
    <button class="checkout-btn">Proceed to Checkout</button>
  <?php else: ?>
    <p>Your cart is empty.</p>
  <?php endif; ?>
</div>

<div class="card-container">
  <?php
  $query = "SELECT * FROM products";
  $result = $con->query($query);

  if ($result->num_rows > 0) {
      while ($product = $result->fetch_assoc()) {
          echo '<div class="card">';
          $imagePath = '' . htmlspecialchars($product["image"]);
          echo '<img src="' . $imagePath . '" alt="Product Image">';
          echo '<h2>' . htmlspecialchars($product["name"]) . '</h2>';
          echo '<p>' . htmlspecialchars($product["description"]) . '</p>';
          echo '<p>Price: ₹' . htmlspecialchars($product["price"]) . '</p>';
          echo '<p>Stock: ' . htmlspecialchars($product["stock_quantity"]) . '</p>';
          echo '<p class="status">Status: ' . htmlspecialchars($product["status"]) . '</p>';

          echo '<form method="POST">';
          echo '<input type="hidden" name="product_id" value="' . $product["id"] . '">';
          echo '<input type="hidden" name="name" value="' . htmlspecialchars($product["name"]) . '">';
          echo '<input type="hidden" name="price" value="' . $product["price"] . '">';
          echo '<input type="number" name="quantity" min="1" max="' . $product["stock_quantity"] . '" value="1" required>';
          echo '<button type="submit" name="add_to_cart">Add to Cart</button>';
          echo '</form>';

          echo '</div>';
      }
  } else {
      echo "<p>No products found.</p>";
  }

  $con->close();
  ?>
</div>

<script>
  function toggleCartPopup() {
    const popup = document.getElementById('cartPopup');
    popup.style.display = popup.style.display === 'block' ? 'none' : 'block';
  }
</script>

</body>
</html>
