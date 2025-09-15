<?php
session_start();
$con = mysqli_connect("localhost", "root", "", "tribal_arts_db") or die("Couldn't connect");


$_SESSION['valid'] = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Product</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f4f4;
      padding: 40px;
    }
    form {
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      max-width: 500px;
      margin: auto;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
    }
    input, textarea, select {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border-radius: 6px;
      border: 1px solid #ccc;
    }
    button {
      margin-top: 20px;
      padding: 10px 20px;
      background: #0078d4;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
    button:hover {
      background: #005fa3;
    }
  </style>
</head>
<body>

<form action="insert_product.php" method="POST" enctype="multipart/form-data">
  <label>Product Name</label>
  <input type="text" name="name" required>

  <label>Description</label>
  <textarea name="description" rows="4" required></textarea>

  <label>Price</label>
  <input type="number" name="price" step="0.01" required>

  <input type="hidden" name="category_id" value="1">
  <input type="hidden" name="supplier_id" value="1">

  <label>Stock Quantity</label>
  <input type="number" name="stock_quantity" required>

  <label>Status</label>
  <select name="status" required>
    <option value="active">Active</option>
    <option value="in stock">In Stock</option>
    <option value="out of stock">Out of Stock</option>
  </select>

  <label>Product Image</label>
  <input type="file" name="image" accept="image/*" required>

  <button type="submit">Add Product</button>
</form>

</body>
</html>