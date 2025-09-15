<?php
// auth_check.php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Set user role if not already set (for backward compatibility)
if (!isset($_SESSION['user_role'])) {
    // Default to customer role if not set
    $_SESSION['user_role'] = 'customer';
}

// Function to check if user has required role
function require_role($role) {
    if ($_SESSION['user_role'] !== $role) {
        header("Location: index.php");
        exit();
    }
}

// Function to log activities
function log_activity($activity_type, $description = '') {
    $con = mysqli_connect("localhost", "root", "", "tribal_arts_db") or die("Couldn't connect");
    $user_id = $_SESSION['user_id'];
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $query = "INSERT INTO activities (user_id, activity_type, description, ip_address, user_agent) 
              VALUES ($user_id, '$activity_type', '$description', '$ip_address', '$user_agent')";
    mysqli_query($con, $query);
    mysqli_close($con);
}
?>