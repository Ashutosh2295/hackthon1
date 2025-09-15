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
}

// Handle Remove from Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_from_cart'])) {
    $productId = $_POST['product_id'];
    unset($_SESSION['cart'][$productId]);
}

// Update Quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $productId = $_POST['product_id'];
    $action = $_POST['action'];
    if (isset($_SESSION['cart'][$productId])) {
        if ($action === 'plus') {
            $_SESSION['cart'][$productId]['quantity']++;
        } elseif ($action === 'minus' && $_SESSION['cart'][$productId]['quantity'] > 1) {
            $_SESSION['cart'][$productId]['quantity']--;
        }
    }
}

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
<title>Modern Cart Popup</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
  body {
    font-family: Arial, sans-serif;
    background: #f4f4f4;
    margin: 0;
    padding: 40px;
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
  /* Modal background */
  .modal {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 2000;
    justify-content: center;
    align-items: center;
  }
  /* Modal content */
  .modal-content {
    background: white;
    border-radius: 12px;
    padding: 20px;
    width: 400px;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 6px 20px rgba(0,0,0,0.2);
    animation: fadeIn 0.3s ease;
  }
  @keyframes fadeIn {
    from {opacity:0; transform:translateY(-20px);}
    to {opacity:1; transform:translateY(0);}
  }
  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .modal-header h2 {
    margin: 0;
    font-size: 1.4em;
  }
  .close-btn {
    background: none;
    border: none;
    font-size: 22px;
    cursor: pointer;
  }
  .cart-item {
    display: flex;
    align-items: center;
    margin: 15px 0;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
  }
  .cart-item img {
    width: 60px; height: 60px;
    border-radius: 8px;
    object-fit: cover;
    margin-right: 12px;
  }
  .cart-details {
    flex: 1;
  }
  .cart-details h4 {
    margin: 0 0 4px;
    font-size: 1em;
  }
  .cart-details p {
    margin: 0;
    color: #a33;
    font-weight: bold;
  }
  .quantity-controls {
    display: flex;
    align-items: center;
    gap: 5px;
  }
  .quantity-controls button {
    border: 1px solid #ccc;
    background: #fff;
    padding: 4px 8px;
    cursor: pointer;
    border-radius: 4px;
  }
  .remove-btn {
    border: none;
    background: none;
    color: red;
    font-size: 18px;
    cursor: pointer;
  }
  .modal-footer {
    margin-top: 15px;
    text-align: center;
  }
  .checkout-btn {
    background: black;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 25px;
    cursor: pointer;
    font-size: 1em;
  }
</style>
</head>
<body>

<div class="cart-icon" onclick="openCart()">
  <button><i class="fa-solid fa-cart-shopping"></i> </button><span class="cart-count"><?= $cartCount ?></span>
</div>

<!-- Modal -->
<div class="modal" id="cartModal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Shopping Cart</h2>
      <button class="close-btn" onclick="closeCart()">×</button>
    </div>

    <?php if ($cartCount > 0): ?>
      <?php foreach ($_SESSION['cart'] as $id => $item): ?>
        <div class="cart-item">
          <img src="<?= htmlspecialchars($item['image']) ?>" alt="Product">
          <div class="cart-details">
            <h4><?= htmlspecialchars($item['name']) ?></h4>
            <p>₹<?= $item['price'] ?></p>
            <div class="quantity-controls">
              <form method="POST" style="display:inline;">
                <input type="hidden" name="product_id" value="<?= $id ?>">
                <input type="hidden" name="action" value="minus">
                <button type="submit" name="update_quantity">-</button>
              </form>
              <?= $item['quantity'] ?>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="product_id" value="<?= $id ?>">
                <input type="hidden" name="action" value="plus">
                <button type="submit" name="update_quantity">+</button>
              </form>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="product_id" value="<?= $id ?>">
                <button type="submit" name="remove_from_cart" class="remove-btn">×</button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>

      <div class="modal-footer">
        <h3>Total: ₹<?= $totalPrice ?></h3>
        <button class="checkout-btn">Checkout</button>
      </div>
    <?php else: ?>
      <p>Your cart is empty.</p>
    <?php endif; ?>
  </div>
</div>

<script>
  function openCart() {
    document.getElementById('cartModal').style.display = 'flex';
  }
  function closeCart() {
    document.getElementById('cartModal').style.display = 'none';
  }
</script>

</body>
</html>
