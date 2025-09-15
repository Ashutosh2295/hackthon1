<?php
// setup_database.php
$con = mysqli_connect("localhost", "root", "", "tribal_arts_db");

if (!$con) {
    die("Couldn't connect to database");
}

// Create users table if not exists
$users_table = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'supplier', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!mysqli_query($con, $users_table)) {
    die("Error creating users table: " . mysqli_error($con));
}

// Create suppliers table if not exists
$suppliers_table = "CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    contact_number VARCHAR(20),
    address TEXT,
    business_registration VARCHAR(100),
    tax_id VARCHAR(50),
    status ENUM('active', 'pending', 'suspended') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if (!mysqli_query($con, $suppliers_table)) {
    die("Error creating suppliers table: " . mysqli_error($con));
}

// Create products table if not exists
$products_table = "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INT,
    supplier_id INT,
    image VARCHAR(255),
    stock_quantity INT DEFAULT 0,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    category VARCHAR(100),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
)";

if (!mysqli_query($con, $products_table)) {
    die("Error creating products table: " . mysqli_error($con));
}

// Create orders table if not exists
$orders_table = "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL