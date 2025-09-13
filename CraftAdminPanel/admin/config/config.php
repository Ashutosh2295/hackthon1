<?php
session_start();

// Site configuration
define('SITE_NAME', 'Tribal Arts Heritage Admin');
define('BASE_URL', '/');

// Admin configuration
define('ADMIN_SESSION_TIMEOUT', 86400); // 24 hours (increased for testing)

// Upload settings
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Pagination
define('ITEMS_PER_PAGE', 10);

// Include database configuration
require_once 'database.php';

// Utility functions
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
    
    // Check session timeout (only if login_time is set)
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > ADMIN_SESSION_TIMEOUT) {
        session_destroy();
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
    
    // Update last activity time
    $_SESSION['login_time'] = time();
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' min ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    return floor($time/86400) . ' days ago';
}

// Error and success message functions
function setMessage($message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

function getMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'] ?? 'success';
        unset($_SESSION['message'], $_SESSION['message_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

// CSRF Protection
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// File upload helper
function uploadFile($file, $upload_dir, $allowed_types = null) {
    if (!$file || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'No file uploaded or upload error'];
    }
    
    $allowed_types = $allowed_types ?: ALLOWED_IMAGE_TYPES;
    $file_size = $file['size'];
    $file_tmp_name = $file['tmp_name'];
    $file_name = $file['name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Validate file type
    if (!in_array($file_ext, $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type. Allowed: ' . implode(', ', $allowed_types)];
    }
    
    // Validate file size
    if ($file_size > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File too large. Maximum size: ' . (MAX_FILE_SIZE/1024/1024) . 'MB'];
    }
    
    // Generate unique filename
    $new_filename = uniqid() . '.' . $file_ext;
    $upload_path = UPLOAD_PATH . $upload_dir . '/' . $new_filename;
    
    // Create directory if it doesn't exist
    if (!is_dir(dirname($upload_path))) {
        mkdir(dirname($upload_path), 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file_tmp_name, $upload_path)) {
        return ['success' => true, 'filename' => $new_filename, 'path' => $upload_path];
    } else {
        return ['success' => false, 'message' => 'Failed to move uploaded file'];
    }
}
?>