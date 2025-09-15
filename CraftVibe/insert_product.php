<?php
session_start();
$con = mysqli_connect("localhost", "root", "", "tribal_arts_db") or die("Couldn't connect");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $desc = $_POST['description'];
  $price = $_POST['price'];
  $category_id = 1;
  $supplier_id = 1;
  $stock = $_POST['stock_quantity'];
  $status = $_POST['status'];

  // Handle image upload
  $imageName = $_FILES['image']['name'];
  $imageTmp = $_FILES['image']['tmp_name'];
  $imagePath = 'uploads/' . basename($imageName);

  if (move_uploaded_file($imageTmp, $imagePath)) {
    $query = "INSERT INTO products (name, description, price, category_id, supplier_id, image, stock_quantity, status, created_at)
              VALUES ('$name', '$desc', '$price', '$category_id', '$supplier_id', '$imagePath', '$stock', '$status', NOW())";

    if (mysqli_query($con, $query)) {
      echo "✅ Product added successfully!";
    } else {
      echo "❌ Error: " . mysqli_error($con);
    }
  } else {
    echo "❌ Failed to upload image.";
  }
}
?>